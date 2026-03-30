<?php

declare(strict_types=1);

namespace App;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Archived = 'archived';

    public function isPaid(): bool
    {
        return $this === self::Paid;
    }

    public function isCancelled(): bool
    {
        return $this === self::Cancelled;
    }

    public function isArchived(): bool
    {
        return $this === self::Archived;
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Paid, self::Cancelled, self::Archived], true);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Draft->value => 'Draft',
            self::Sent->value => 'Sent',
            self::Paid->value => 'Paid',
            self::Cancelled->value => 'Cancelled',
            self::Archived->value => 'Archived',
        ];
    }
}
