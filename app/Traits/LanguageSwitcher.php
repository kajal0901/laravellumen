<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait LanguageSwitcher
{
    /**
     * Update language from header or set default language
     *
     * @param Request $request
     *
     * @return Request
     */
    public function updateLanguage(Request $request): Request
    {
        $language = config('app.locale');
        if (app('auth')->user()) {
            $language = app('auth')->user()->language;
        } elseif ($request->hasHeader('x-request-language')
            && in_array($request->header('x-request-language'), ['en', 'de'])
        ) {
            $language = $request->header('x-request-language');
        }
        $request->setLocale($language);
        app('translator')->setLocale($language);
        return $request;
    }
}
