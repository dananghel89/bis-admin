<?php

namespace App\Services\Mailbox;

use RuntimeException;

/**
 * A mail server problem, with a message that is safe to show the user.
 */
class MailboxException extends RuntimeException {}
