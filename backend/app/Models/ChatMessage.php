<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'session_id',
        'role',
        'message',
        'response',
        'context',
        'metadata',
        'intent',
        'has_attachments'
    ];

    protected $casts = [
        'context' => 'array',
        'metadata' => 'array',
        'has_attachments' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get conversation history for a session
     */
    public static function getHistory(string $sessionId, int $limit = 10): array
    {
        return self::where('session_id', $sessionId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(function ($msg) {
                return [
                    'role' => $msg->role,
                    'message' => $msg->message,
                    'response' => $msg->response,
                    'timestamp' => $msg->created_at
                ];
            })
            ->toArray();
    }

    /**
     * Get context from recent messages
     */
    public static function getContextFromHistory(string $sessionId, int $messageCount = 5): string
    {
        $messages = self::where('session_id', $sessionId)
            ->orderBy('created_at', 'desc')
            ->limit($messageCount)
            ->get()
            ->reverse();

        $context = "Histórico recente da conversa:\n\n";

        foreach ($messages as $msg) {
            if ($msg->role === 'user') {
                $context .= "Usuário: " . $msg->message . "\n";
            }
            if ($msg->response) {
                $context .= "EvolutIA: " . substr($msg->response, 0, 200) . "...\n";
            }
        }

        return $context;
    }
}
