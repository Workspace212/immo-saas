<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TenantContext
{
    private static ?int $agencyId = null;

    private static bool $bypassed = false;

    public static function setAgencyId(?int $agencyId): void
    {
        self::$agencyId = $agencyId;
    }

    public static function clear(): void
    {
        self::$agencyId = null;
        self::$bypassed = false;
    }

    public static function bypass(bool $enabled = true): void
    {
        self::$bypassed = $enabled;
    }

    public static function isBypassed(): bool
    {
        return self::$bypassed;
    }

    public function agencyId(): ?int
    {
        // Jobs, commands, seeders, and tests should call setAgencyId() before
        // querying tenant-owned models when no authenticated request exists.
        if (self::$agencyId !== null) {
            return self::$agencyId;
        }

        $agencyId = $this->user()?->agency_id;

        return $agencyId === null ? null : (int) $agencyId;
    }

    public function user(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }
}
