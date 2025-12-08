<?php

namespace App\Support;

use Illuminate\Http\Request;

class ExternalAuth
{
    public static function user(): ?array
    {
        $request = self::resolveRequest();

        if ($request) {
            if ($request->attributes->has('ext_user')) {
                return $request->attributes->get('ext_user');
            }

            if ($request->hasSession() && $request->session()->has('ext_user')) {
                return $request->session()->get('ext_user');
            }
        }

        return null;
    }

    public static function id(): ?int
    {
        $id = data_get(self::user(), 'id');

        return is_numeric($id) ? (int) $id : null;
    }

    public static function name(): ?string
    {
        return data_get(self::user(), 'name');
    }

    public static function email(): ?string
    {
        return data_get(self::user(), 'email');
    }

    protected static function resolveRequest(): ?Request
    {
        return app()->bound('request') ? app('request') : null;
    }
}
