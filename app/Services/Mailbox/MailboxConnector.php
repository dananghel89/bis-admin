<?php

namespace App\Services\Mailbox;

use App\Models\MailAccount;
use DirectoryTree\ImapEngine\Mailbox;
use DirectoryTree\ImapEngine\MailboxInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Opens a user's own mail servers: IMAP (directorytree/imapengine) to read and
 * SMTP (Symfony Mailer) to send. Tests bind a fake in its place.
 */
class MailboxConnector
{
    public function imap(MailAccount $account): MailboxInterface
    {
        return new Mailbox([
            'host' => $account->imap_host,
            'port' => $account->imap_port,
            'username' => $account->loginName(),
            'password' => $account->password,
            'encryption' => match ($account->imap_encryption) {
                'ssl' => 'ssl',
                'starttls' => 'starttls',
                default => null,
            },
            'timeout' => 20,
        ]);
    }

    public function smtp(MailAccount $account): TransportInterface
    {
        // tls true = implicit TLS (usually port 465); otherwise STARTTLS when asked for.
        $transport = new EsmtpTransport($account->smtp_host, $account->smtp_port, $account->smtp_encryption === 'ssl');

        if ($account->smtp_encryption === 'starttls') {
            $transport->setRequireTls(true);
        } elseif ($account->smtp_encryption === 'none') {
            $transport->setAutoTls(false);
        }

        $transport->setUsername($account->loginName());
        $transport->setPassword((string) $account->password);

        return $transport;
    }
}
