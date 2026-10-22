<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveUserRequest;
use App\Models\Campus;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'campus_id' => ['nullable', 'integer', 'exists:campuses,id'],
            'employment_type' => ['nullable', Rule::in(array_keys(User::employmentTypeLabels()))],
            'unit_section' => ['nullable', Rule::in(array_keys(User::unitSectionLabels()))],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ], [
            'search.max' => 'Pencarian tidak boleh lebih dari 100 karakter.',
            'campus_id.integer' => 'Pilihan kampus tidak valid.',
            'campus_id.exists' => 'Kampus yang dipilih tidak tersedia.',
            'employment_type.in' => 'Filter status kepegawaian tidak valid.',
            'unit_section.in' => 'Filter unit bagian tidak valid.',
            'status.in' => 'Filter status tidak valid.',
        ]);

        $users = $this->scopedUsers($request->user())
            ->with(['campus:id,name', 'unit:id,campus_id,name', 'unit.campus:id,name'])
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('username', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['campus_id'] ?? null, function (Builder $query, int $id) {
                $query->where(function (Builder $query) use ($id) {
                    $query->where(function (Builder $query) use ($id) {
                        $query->where('role', User::ROLE_CAMPUS_HEAD)->where('campus_id', $id);
                    })->orWhereHas('unit', fn (Builder $units) => $units->where('campus_id', $id));
                });
            })
            ->when($filters['employment_type'] ?? null, fn (Builder $query, string $type) => $query->where('employment_type', $type))
            ->when($filters['unit_section'] ?? null, fn (Builder $query, string $section) => $query->where('unit_section', $section))
            ->when(isset($filters['status']), fn (Builder $query) => $query->where('is_active', $filters['status'] === 'active'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'filters' => $filters,
            'campuses' => $this->availableCampuses($request->user()),
            'employmentTypeLabels' => User::employmentTypeLabels(),
            'unitSectionLabels' => User::unitSectionLabels(),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', User::class);

        return view('users.form', [
            'managedUser' => new User(['is_active' => true]),
            'campuses' => $this->availableCampuses($request->user()),
            'employmentTypeLabels' => User::employmentTypeLabels(),
            'unitSectionLabels' => $this->availableUnitSectionLabels($request->user()),
            'isSelf' => false,
            'isSystemAdminTarget' => false,
        ]);
    }

    public function store(SaveUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data = $this->normalizePlacement($data, $request->user());
        $data['is_active'] = true;

        User::create($data);

        return redirect()->route('users.index')->with('status', 'Akun pengguna berhasil ditambahkan.');
    }

    public function edit(Request $request, User $user): View
    {
        $user = $this->resolveScopedUser($request->user(), $user);
        Gate::authorize('view', $user);

        return view('users.form', [
            'managedUser' => $user,
            'campuses' => $this->availableCampuses($request->user()),
            'employmentTypeLabels' => User::employmentTypeLabels(),
            'unitSectionLabels' => $this->availableUnitSectionLabels($request->user(), $user),
            'isSelf' => $request->user()->is($user),
            'isSystemAdminTarget' => $user->isSystemAdmin(),
        ]);
    }

    public function update(SaveUserRequest $request, User $user): RedirectResponse
    {
        $user = $this->resolveScopedUser($request->user(), $user);
        Gate::authorize('update', $user);

        $data = $request->validated();

        if ($request->user()->is($user)) {
            unset($data['campus_id']);
        } else {
            $data = $this->normalizePlacement($data, $request->user(), $user);
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $blockedLastAdmin = false;

        DB::transaction(function () use ($user, $data, &$blockedLastAdmin) {
            if ($this->wouldRemoveLastActiveAdmin($user, $data['role'] ?? $user->role)) {
                $blockedLastAdmin = true;

                return;
            }

            $user->fill($data)->save();
        });

        if ($blockedLastAdmin) {
            return back()->withErrors([
                'role' => 'Peran Admin Sistem aktif terakhir tidak dapat diubah.',
            ])->withInput($request->except(['password', 'password_confirmation']));
        }

        return redirect()->route('users.index')->with('status', 'Data pengguna berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $user = $this->resolveScopedUser($request->user(), $user);
        Gate::authorize('toggleStatus', $user);

        $request->validate([
            'is_active' => ['required', Rule::in(['0', '1'])],
        ], [
            'is_active.required' => 'Status akun wajib dipilih.',
            'is_active.in' => 'Status akun tidak valid.',
        ]);

        $newStatus = $request->input('is_active') === '1';

        $blockedLastAdmin = false;

        DB::transaction(function () use ($user, $newStatus, &$blockedLastAdmin) {
            if (! $newStatus && $this->wouldRemoveLastActiveAdmin($user, $user->role, true)) {
                $blockedLastAdmin = true;

                return;
            }

            $user->forceFill(['is_active' => $newStatus])->save();
        });

        if ($blockedLastAdmin) {
            return back()->withErrors([
                'status' => 'Admin Sistem aktif terakhir tidak dapat dinonaktifkan.',
            ]);
        }

        $message = $newStatus
            ? 'Akun pengguna berhasil diaktifkan.'
            : 'Akun pengguna berhasil dinonaktifkan.';

        return redirect()->route('users.index')->with('status', $message);
    }

    private function scopedUsers(User $actor): Builder
    {
        $query = User::query();

        if ($actor->role === User::ROLE_CAMPUS_HEAD) {
            $campusId = $actor->assignedCampusId();

            if ($campusId === null) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('role', [User::ROLE_UNIT_HEAD, User::ROLE_UNIT_MEMBER])
                ->whereHas('unit', fn (Builder $units) => $units->where('campus_id', $campusId));
        }

        if ($actor->role === User::ROLE_UNIT_HEAD) {
            if ($actor->unit_id === null) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where('role', User::ROLE_UNIT_MEMBER)
                ->where('unit_id', $actor->unit_id);
        }

        return $query;
    }

    private function resolveScopedUser(User $actor, User $target): User
    {
        $scoped = $this->scopedUsers($actor)->whereKey($target->id)->first();

        if ($actor->is($target)) {
            return $target;
        }

        abort_if($scoped === null, 404);

        return $scoped;
    }

    private function availableCampuses(User $actor)
    {
        if ($actor->role === User::ROLE_SYSTEM_ADMIN) {
            return Campus::query()->orderBy('name')->get(['id', 'name']);
        }

        $campusId = $actor->assignedCampusId();

        return $campusId === null
            ? collect()
            : Campus::query()->whereKey($campusId)->get(['id', 'name']);
    }

    private function availableUnitSectionLabels(User $actor, ?User $target = null): array
    {
        if ($target?->isSystemAdmin()) {
            return [];
        }

        $labels = User::unitSectionLabels();

        if ($target && $actor->is($target)) {
            return isset($labels[$target->unit_section])
                ? [$target->unit_section => $labels[$target->unit_section]]
                : [];
        }

        if ($actor->role === User::ROLE_SYSTEM_ADMIN) {
            return $labels;
        }

        if ($actor->role === User::ROLE_CAMPUS_HEAD) {
            return array_intersect_key($labels, User::unitSectionUnitNames());
        }

        if ($actor->role === User::ROLE_UNIT_HEAD) {
            $section = array_search($actor->unit?->name, User::unitSectionUnitNames(), true);

            return $section === false ? [] : [$section => $labels[$section]];
        }

        return [];
    }

    private function normalizePlacement(array $data, User $actor, ?User $target = null): array
    {
        if ($target?->isSystemAdmin()) {
            $data['role'] = User::ROLE_SYSTEM_ADMIN;
            $data['campus_id'] = null;
            $data['unit_id'] = null;

            return $data;
        }

        $data['role'] = $data['unit_section'] === 'campus_head'
            ? User::ROLE_CAMPUS_HEAD
            : User::ROLE_UNIT_MEMBER;

        if ($actor->role === User::ROLE_CAMPUS_HEAD) {
            $data['campus_id'] = $actor->assignedCampusId();
        } elseif ($actor->role === User::ROLE_UNIT_HEAD) {
            $data['campus_id'] = $actor->assignedCampusId();
            $data['unit_id'] = $actor->unit_id;
        }

        if ($data['unit_section'] === 'campus_head') {
            $data['unit_id'] = null;
        } elseif ($actor->role !== User::ROLE_UNIT_HEAD) {
            $unitName = User::unitSectionUnitNames()[$data['unit_section']];
            $unit = Unit::query()
                ->where('campus_id', $data['campus_id'])
                ->where('name', $unitName)
                ->firstOrFail();
            $data['unit_id'] = $unit->id;
        }

        return $data;
    }

    private function wouldRemoveLastActiveAdmin(User $target, string $newRole, bool $deactivating = false): bool
    {
        if ($target->role !== User::ROLE_SYSTEM_ADMIN
            || ! $target->isActive()
            || ($newRole === User::ROLE_SYSTEM_ADMIN && ! $deactivating)) {
            return false;
        }

        $activeAdminIds = User::query()
            ->where('role', User::ROLE_SYSTEM_ADMIN)
            ->where('is_active', true)
            ->lockForUpdate()
            ->pluck('id');

        return $activeAdminIds->count() <= 1;
    }
}
