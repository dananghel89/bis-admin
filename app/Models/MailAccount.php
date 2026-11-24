<?php

namespace App\Models;

use Database\Factories\MailAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A user's own mailbox for Alerts: IMAP to read, SMTP to send.
 */
class MailAccount extends Model
{
    /** @use HasFactory<MailAccountFactory> */
    use HasFactory;

    public const ENCRYPTIONS = ['ssl' => 'SSL/TLS', 'starttls' => 'STARTTLS', 'none' => 'None'];

    protected $fillable = [
        'email', 'username', 'password',
        'imap_host', 'imap_port', 'imap_encryption',
        'smtp_host', 'smtp_port', 'smtp_encryption',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'imap_port' => 'integer',
            'smtp_port' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The login name for both servers: the username, or the email when it's empty.
     */
    public function loginName(): string
    {
        return $this->username ?: $this->email;
    }
}
