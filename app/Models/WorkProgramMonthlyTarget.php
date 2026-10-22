<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkProgramMonthlyTarget extends Model
{
    use HasFactory;

    public const STATUS_NOT_STARTED = 'not_started';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_ACHIEVED = 'achieved';
    public const STATUS_NEEDS_ATTENTION = 'needs_attention';

    protected $fillable = [
        'work_program_id',
        'month',
        'target',
        'actual',
        'notes',
        'status',
    ];

    protected $casts = [
        'month' => 'integer',
        'target' => 'decimal:2',
        'actual' => 'decimal:2',
    ];

    public static function monthNames(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_NOT_STARTED => 'Belum Dimulai',
            self::STATUS_IN_PROGRESS => 'Berjalan',
            self::STATUS_ACHIEVED => 'Tercapai',
            self::STATUS_NEEDS_ATTENTION => 'Perlu Perhatian',
        ];
    }

    public function workProgram(): BelongsTo
    {
        return $this->belongsTo(WorkProgram::class);
    }

    public function monthName(): string
    {
        return self::monthNames()[$this->month] ?? 'Bulan '.$this->month;
    }

    public function effectiveStatus(): string
    {
        if ($this->status && $this->status !== self::STATUS_NOT_STARTED) {
            return $this->status;
        }

        $pct = $this->progressPercent();
        if ($pct === null || (float) $this->target <= 0) {
            return self::STATUS_NOT_STARTED;
        }

        if ($pct >= 100.0) {
            return self::STATUS_ACHIEVED;
        }

        if ($pct > 0.0) {
            return $pct < 50.0 ? self::STATUS_NEEDS_ATTENTION : self::STATUS_IN_PROGRESS;
        }

        return self::STATUS_NOT_STARTED;
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->effectiveStatus()] ?? 'Belum Dimulai';
    }

    public function progressPercent(): ?float
    {
        if ((float) $this->target <= 0) {
            return null;
        }

        if ($this->actual === null) {
            return 0.0;
        }

        return round(((float) $this->actual / (float) $this->target) * 100, 1);
    }
}
