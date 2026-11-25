<?php

namespace Tests\Support;

use DirectoryTree\ImapEngine\Testing\FakeMessage;

/**
 * ImapEngine's fake message has no move() (the real Message has); this one
 * records where it was moved to.
 */
class FakeMovableMessage extends FakeMessage
{
    public ?string $movedTo = null;

    public function move(string $folder, bool $expunge = false): ?int
    {
        $this->movedTo = $folder;

        return null;
    }
}
