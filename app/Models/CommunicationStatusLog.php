<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'communication_topic_id',
        'user_id',
        'from_status',
        'to_status',
        'action_note',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(CommunicationTopic::class, 'communication_topic_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromStatusLabel(): string
    {
        return CommunicationTopic::statusLabels()[$this->from_status] ?? $this->from_status;
    }

    public function toStatusLabel(): string
    {
        return CommunicationTopic::statusLabels()[$this->to_status] ?? $this->to_status;
    }
}
