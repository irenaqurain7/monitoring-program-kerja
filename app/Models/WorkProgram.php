<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkProgram extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_POSTPONED = 'postponed';

    protected $fillable = [
        'name',
        'description',
        'objective',
        'success_indicator',
        'target',
        'target_unit',
        'year',
        'start_date',
        'due_date',
        'campus_id',
        'unit_id',
        'team_id',
        'responsible_user_id',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'target' => 'decimal:2',
        'year' => 'integer',
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'Rancangan',
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_POSTPONED => 'Ditunda',
        ];
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function progressUpdates(): HasMany
    {
        return $this->hasMany(WorkProgramProgressUpdate::class)->orderByDesc('created_at')->orderByDesc('id');
    }

    public function latestProgress(): HasOne
    {
        return $this->hasOne(WorkProgramProgressUpdate::class)->latestOfMany();
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? 'Tidak diketahui';
    }

    public function progressPercent(?WorkProgramProgressUpdate $update = null): ?float
    {
        $update ??= $this->latestProgress;

        if (! $update || (float) $this->target <= 0) {
            return null;
        }

        return min(100, round(((float) $update->actual / (float) $this->target) * 100, 1));
    }
}
