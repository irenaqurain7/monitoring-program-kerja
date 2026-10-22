<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\CommunicationMessage;
use App\Models\CommunicationStatusLog;
use App\Models\CommunicationTopic;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommunicationTopicController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::in(array_keys(CommunicationTopic::categoryLabels()))],
            'status' => ['nullable', Rule::in(array_keys(CommunicationTopic::statusLabels()))],
            'priority' => ['nullable', Rule::in(array_keys(CommunicationTopic::priorityLabels()))],
        ]);

        $query = $this->visibleTopics($user)
            ->with(['creator:id,name', 'assignedTo:id,name', 'unit:id,name'])
            ->withCount('messages');

        $filtered = (clone $query)
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) =>
                $q->where(fn (Builder $sub) =>
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('ticket_number', 'like', "%{$search}%")
                ))
            ->when($filters['category'] ?? null, fn (Builder $q, string $cat) =>
                $q->where('category', $cat))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) =>
                $q->where('status', $status))
            ->when($filters['priority'] ?? null, fn (Builder $q, string $priority) =>
                $q->where('priority', $priority));

        $topics = $filtered
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $statusSummary = (clone $this->visibleTopics($user))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('communication.index', [
            'topics' => $topics,
            'filters' => $filters,
            'categories' => CommunicationTopic::categoryLabels(),
            'statuses' => CommunicationTopic::statusLabels(),
            'priorities' => CommunicationTopic::priorityLabels(),
            'statusSummary' => $statusSummary,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        return view('communication.create', [
            'categories' => CommunicationTopic::categoryLabels(),
            'priorities' => CommunicationTopic::priorityLabels(),
            'privacies' => CommunicationTopic::privacyLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(array_keys(CommunicationTopic::categoryLabels()))],
            'priority' => ['required', Rule::in(array_keys(CommunicationTopic::priorityLabels()))],
            'privacy' => ['required', Rule::in(array_keys(CommunicationTopic::privacyLabels()))],
            'description' => ['required', 'string', 'max:10000'],
        ], [
            'title.required' => 'Judul aspirasi / aduan wajib diisi.',
            'description.required' => 'Uraian pesan atau kendala wajib diisi.',
        ]);

        $campusId = $user->assignedCampusId();
        abort_if(! $campusId, 403, 'Akun tidak terasosiasi dengan kampus yang valid.');

        $topic = DB::transaction(function () use ($data, $user, $campusId) {
            $topic = CommunicationTopic::create([
                'ticket_number' => CommunicationTopic::generateTicketNumber($campusId),
                'campus_id' => $campusId,
                'creator_id' => $user->id,
                'unit_id' => $user->unit_id,
                'team_id' => null,
                'category' => $data['category'],
                'priority' => $data['priority'],
                'privacy' => $data['privacy'],
                'assigned_to_user_id' => null,
                'status' => CommunicationTopic::STATUS_NEW,
                'title' => $data['title'],
                'description' => $data['description'],
            ]);

            CommunicationStatusLog::create([
                'communication_topic_id' => $topic->id,
                'user_id' => $user->id,
                'from_status' => 'init',
                'to_status' => CommunicationTopic::STATUS_NEW,
                'action_note' => 'Aspirasi / pengaduan baru berhasil dibuat.',
            ]);

            return $topic;
        });

        return redirect()->route('communication.show', $topic)
            ->with('status', 'Aspirasi / pengaduan Anda berhasil dikirim dengan nomor referensi '.$topic->ticket_number.'.');
    }

    public function show(Request $request, CommunicationTopic $topic): View
    {
        $user = $request->user();
        $this->authorizeViewTopic($user, $topic);

        $topic->load([
            'creator:id,name,role,unit_section',
            'assignedTo:id,name',
            'unit:id,name',
            'messages.user:id,name,role',
            'statusLogs.user:id,name',
        ]);

        $campusUsers = [];
        if ($user->role === User::ROLE_CAMPUS_HEAD) {
            $campusUsers = User::query()
                ->where('campus_id', $user->assignedCampusId())
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return view('communication.show', [
            'topic' => $topic,
            'campusUsers' => $campusUsers,
            'statuses' => CommunicationTopic::statusLabels(),
        ]);
    }

    public function storeMessage(Request $request, CommunicationTopic $topic): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeViewTopic($user, $topic);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ], [
            'message.required' => 'Pesan tanggapan tidak boleh kosong.',
        ]);

        CommunicationMessage::create([
            'communication_topic_id' => $topic->id,
            'user_id' => $user->id,
            'message' => $data['message'],
        ]);

        return redirect()->route('communication.show', $topic)
            ->with('status', 'Tanggapan berhasil dikirim.');
    }

    public function updateStatus(Request $request, CommunicationTopic $topic): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeViewTopic($user, $topic);

        $isCampusHead = $user->role === User::ROLE_CAMPUS_HEAD;
        $isCreator = $topic->creator_id === $user->id;

        abort_if(! $isCampusHead && ! $isCreator, 403, 'Anda tidak memiliki hak untuk mengubah status.');

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(CommunicationTopic::statusLabels()))],
            'action_note' => ['nullable', 'string', 'max:1000'],
            'assigned_to_user_id' => ['nullable', 'exists:users,id'],
        ], [
            'status.required' => 'Status wajib dipilih.',
        ]);

        $fromStatus = $topic->status;
        $topic->status = $data['status'];

        if ($isCampusHead && array_key_exists('assigned_to_user_id', $data)) {
            $topic->assigned_to_user_id = $data['assigned_to_user_id'] ?: null;
        }

        $topic->save();

        CommunicationStatusLog::create([
            'communication_topic_id' => $topic->id,
            'user_id' => $user->id,
            'from_status' => $fromStatus,
            'to_status' => $topic->status,
            'action_note' => $data['action_note'] ?: 'Pembaruan status tindak lanjut.',
        ]);

        return redirect()->route('communication.show', $topic)
            ->with('status', 'Status tindak lanjut aduan berhasil diperbarui.');
    }

    private function visibleTopics(User $user): Builder
    {
        $query = CommunicationTopic::query();

        if ($user->role === User::ROLE_SYSTEM_ADMIN) {
            return $query;
        }

        $campusId = $user->assignedCampusId();
        $query->where('campus_id', $campusId);

        if ($user->role === User::ROLE_CAMPUS_HEAD) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->where('creator_id', $user->id)
              ->orWhere(function (Builder $sub) use ($user) {
                  $sub->where('privacy', CommunicationTopic::PRIVACY_PUBLIC_UNIT)
                      ->where('unit_id', $user->unit_id);
              });
        });
    }

    private function authorizeViewTopic(User $user, CommunicationTopic $topic): void
    {
        if ($user->role === User::ROLE_SYSTEM_ADMIN) {
            return;
        }

        if ($user->assignedCampusId() !== (int) $topic->campus_id) {
            abort(403, 'Akses ditolak untuk kampus yang berbeda.');
        }

        if ($user->role === User::ROLE_CAMPUS_HEAD) {
            return;
        }

        if ($topic->creator_id === $user->id) {
            return;
        }

        if ($topic->privacy === CommunicationTopic::PRIVACY_PUBLIC_UNIT && (int) $topic->unit_id === (int) $user->unit_id) {
            return;
        }

        abort(403, 'Aspirasi / pengaduan ini bersifat privat.');
    }
}
