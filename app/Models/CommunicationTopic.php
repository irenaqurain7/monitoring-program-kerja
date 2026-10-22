<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunicationTopic extends Model
{
    use HasFactory;

    public const CATEGORY_ASPIRASI = 'aspirasi';
    public const CATEGORY_KENDALA = 'kendala_pekerjaan';
    public const CATEGORY_FASILITAS = 'fasilitas';
    public const CATEGORY_KOORDINASI = 'koordinasi';
    public const CATEGORY_LAINNYA = 'lainnya';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    public const PRIVACY_PUBLIC_UNIT = 'public_unit';
    public const PRIVACY_PRIVATE_CAMPUS_HEAD = 'private_campus_head';

    public const STATUS_NEW = 'new';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_PENDING_INFO = 'pending_info';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'ticket_number',
        'campus_id',
        'creator_id',
        'unit_id',
        'team_id',
        'category',
        'priority',
        'privacy',
        'assigned_to_user_id',
        'status',
        'title',
        'description',
    ];

    public static function categoryLabels(): array
    {
        return [
            self::CATEGORY_ASPIRASI => 'Aspirasi',
            self::CATEGORY_KENDALA => 'Kendala Pekerjaan',
            self::CATEGORY_FASILITAS => 'Fasilitas',
            self::CATEGORY_KOORDINASI => 'Koordinasi',
            self::CATEGORY_LAINNYA => 'Lainnya',
        ];
    }

    public static function priorityLabels(): array
    {
        return [
            self::PRIORITY_LOW => 'Rendah',
            self::PRIORITY_MEDIUM => 'Sedang',
            self::PRIORITY_HIGH => 'Tinggi',
            self::PRIORITY_URGENT => 'Mendesak',
        ];
    }

    public static function privacyLabels(): array
    {
        return [
            self::PRIVACY_PUBLIC_UNIT => 'Terbuka (Unit)',
            self::PRIVACY_PRIVATE_CAMPUS_HEAD => 'Privat (Kepala Kampus)',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_NEW => 'Baru',
            self::STATUS_UNDER_REVIEW => 'Dalam Tinjauan',
            self::STATUS_IN_PROGRESS => 'Diproses',
            self::STATUS_PENDING_INFO => 'Menunggu Informasi',
            self::STATUS_RESOLVED => 'Selesai',
            self::STATUS_CLOSED => 'Ditutup',
        ];
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CommunicationMessage::class)->orderBy('created_at');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(CommunicationStatusLog::class)->orderByDesc('created_at');
    }

    public function categoryLabel(): string
    {
        return self::categoryLabels()[$this->category] ?? $this->category;
    }

    public function priorityLabel(): string
    {
        return self::priorityLabels()[$this->priority] ?? $this->priority;
    }

    public function privacyLabel(): string
    {
        return self::privacyLabels()[$this->privacy] ?? $this->privacy;
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public static function generateTicketNumber(int $campusId): string
    {
        $year = date('Y');
        $count = self::query()->whereYear('created_at', $year)->count() + 1;
        return sprintf('TWC-%s-%04d', $year, $count);
    }
}
