<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Prioridade: 1. Session, 2. User preference, 3. Browser, 4. Default

        if (session()->has('locale')) {
            // Se tiver na sessão, usa
            App::setLocale(session('locale'));
        } elseif (Auth::check() && Auth::user()->language) {
            // Se o usuário estiver logado e tiver preferência salva
            App::setLocale(Auth::user()->language);
            session(['locale' => Auth::user()->language]);
        } elseif ($request->hasHeader('Accept-Language')) {
            // Detectar do navegador
            $browserLang = substr($request->header('Accept-Language'), 0, 2);
            $availableLocales = ['en', 'pt', 'it', 'es', 'fr', 'de'];

            if (in_array($browserLang, $availableLocales)) {
                App::setLocale($browserLang);
            }
        }

        return $next($request);
    }
}
