<?php

namespace Modules\Core\Providers;

use App\Forkiva;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Modules\User\Exceptions\TooManyLoginAttemptsException;
use Nwidart\Modules\Facades\Module;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->routes(fn() => $this->mapModulesRoutes());
        $this->configureRateLimiting();

        Route::pattern('id', '[0-9]+');
    }

    /**
     * Map routes from all enabled modules.
     *
     * @return void
     */
    private function mapModulesRoutes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        foreach (Module::getOrdered() as $module) {
            foreach (config('core.routes') as $config) {
                $this->mapRoutes(
                    "{$module->getPath()}/routes/{$config['file']}",
                    "Modules\\{$module->getName()}\\{$config['namespace']}",
                    $config
                );
            }
        }
    }

    /**
     * Map routes.
     *
     * @param string $path
     * @param string $namespace
     * @param array $config
     * @return void
     */
    private function mapRoutes(string $path, string $namespace, array $config): void
    {
        if (!file_exists($path)) {
            return;
        }

        $route = Route::middleware($config['middleware'])
            ->namespace($namespace);

        if (isset($config["name"])) {
            $route->name($config["name"]);
        }

        if (Forkiva::routeDomainEnabled()) {
            $route->domain($config['domain']);
            if (isset($config['version'])) {
                $route->prefix($config['version']);
            }
        } else {
            if (isset($config['prefix'])) {
                $route->prefix($config['prefix']);
            }

            if (isset($config['version'])) {
                $route->prefix("{$config['prefix']}/{$config['version']}");
            }
        }

        $route->group(fn() => require $path);
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            $username = 'identifier';
            return Limit::perMinute(3)->by($request->{$username} . $request->ip())
                ->response(function ($request, $headers) use ($username) {
                    throw new TooManyLoginAttemptsException(
                        retryAfter: $headers['Retry-After']
                    );
                });
        });

    }
}
