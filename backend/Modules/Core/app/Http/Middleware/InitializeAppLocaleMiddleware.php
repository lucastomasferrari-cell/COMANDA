<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Setting\Models\Setting;
use Modules\Setting\Repositories\SettingRepository;

class InitializeAppLocaleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->get('locale', $request->header('X-Comanda-Locale', setting('default_locale')));

        if (in_array($locale, supportedLocaleKeys())) {
            $oldLocale = locale();

            app()->setLocale($locale);
            
            if ($locale != $oldLocale) {
                app()->forgetInstance('setting');
                app()->singleton('setting', fn() => new SettingRepository(Setting::allCached()));
            }
        }

        return $next($request);
    }
}
