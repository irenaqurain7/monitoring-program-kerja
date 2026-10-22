<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkProgramProgressUpdate extends Model
{
    protected $fillable = [
        'work_program_id',
        'reported_by',
        'actual',
        'notes',
    ];

    protected $casts = [
        'actual' => 'decimal:2',
    ];

    public function workProgram(): BelongsTo
    {
        return $this->belongsTo(WorkProgram::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
