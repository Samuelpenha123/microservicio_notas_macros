<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\HelpdeskClientException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\HelpdeskClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExternalAuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $client = new HelpdeskClient();

        try {
            $payload = $client->login($request->email, $request->password);
        } catch (HelpdeskClientException $exception) {
            return back()
                ->withErrors(['email' => $exception->getMessage()])
                ->withInput();
        }

        if (! $payload['token'] || ! $payload['user']) {
            return back()
                ->withErrors(['email' => 'No se pudo validar la sesión en la API externa.'])
                ->withInput();
        }

        $request->session()->put('ext_token', $payload['token']);
        $request->session()->put('ext_user', $payload['user']);
        $request->session()->regenerate();

        return redirect()->intended(route('tickets.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget(['ext_token', 'ext_user']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
