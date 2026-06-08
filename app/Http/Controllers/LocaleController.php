<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        $supportedLocales = ['en', 'ar'];

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = config('app.locale');
        }

        if ($request->hasSession()) {
            $request->session()->put('locale', $locale);
        }

        $redirect = $request->input('redirect', url()->previous() ?? route('home'));

        if (! str_starts_with($redirect, url('/')) && ! str_starts_with($redirect, '/')) {
            $redirect = route('home');
        }

        return redirect($redirect);
    }
}
