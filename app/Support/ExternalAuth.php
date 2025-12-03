<?php

namespace App\Support;

class ExternalAuth
{
    public static function user(): ?array
    {
        return session('ext_user');
    }

    public static function id(): ?int
    {
        $id = data_get(self::user(), 'id');

        return $id !== null ? (int) $id : null;
    }

    public static function name(): ?string
    {
        return data_get(self::user(), 'name');
    }

    public static function email(): ?string
    {
        return data_get(self::user(), 'email');
    }
}
