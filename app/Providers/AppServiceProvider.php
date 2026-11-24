<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        $this->defineGates();
        $this->defineRateLimiters();
    }

    /**
     * The abilities checked by the `can:` route middleware and by @can in the views.
     * Staff = admin, Architect = editor. Which properties an architect sees is not a
     * gate: Property::visibleTo() limits every query.
     */
    protected function defineGates(): void
    {
        $roles = fn (string ...$roles) => fn (User $user) => in_array($user->getRole(), $roles, true);

        // Staff only
        Gate::define('manageUsers', $roles('admin'));
        Gate::define('readContacts', $roles('admin'));
        Gate::define('manageProperties', $roles('admin')); // add, edit, delete, publish, assign, upload

        // Staff and architects
        Gate::define('viewProperties', $roles('admin', 'editor')); // lists, pages, maps, downloads
        Gate::define('updateWorkStatus', $roles('admin', 'editor'));
        Gate::define('useMailbox', $roles('admin', 'editor'));
        Gate::define('editProfile', $roles('admin', 'editor'));
    }

    /**
     * Public forms: 3 requests per 10 minutes per IP. Named limiters keep a separate
     * counter per limiter, so one form never uses up another form's attempts.
     */
    protected function defineRateLimiters(): void
    {
        RateLimiter::for('password-email', fn (Request $request) => Limit::perMinutes(10, 3)->by($request->ip()));
        RateLimiter::for('contact', fn (Request $request) => Limit::perMinutes(10, 3)->by($request->ip())
            ->response(function (Request $request, array $headers) {
                $message = 'Too many messages. Please try again in a few minutes.';

                return $request->expectsJson()
                    ? response()->json(['message' => $message], 429, $headers)
                    : redirect()->to(route('front').'#contact')->withInput()->withErrors(['contact' => $message]);
            }));
    }
}
