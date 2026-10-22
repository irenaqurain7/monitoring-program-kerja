<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'communication_topic_id',
        'user_id',
        'message',
        'attachment_path',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(CommunicationTopic::class, 'communication_topic_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
