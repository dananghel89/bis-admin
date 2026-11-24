<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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
        $this->defineGates();
        $this->defineRateLimiters();
    }

    /**
     * The abilities checked by the `can:` route middleware and by @can in the views.
     */
    protected function defineGates(): void
    {
        $roles = fn (string ...$roles) => fn (User $user) => in_array($user->getRole(), $roles, true);

        Gate::define('addUsers', $roles('admin'));
        Gate::define('viewUsers', $roles('admin'));
        Gate::define('editUsers', $roles('admin'));
        Gate::define('deleteUsers', $roles('admin'));
        Gate::define('editProfile', $roles('admin', 'editor', 'subscriber'));
        Gate::define('addPosts', $roles('editor'));
        Gate::define('viewPosts', $roles('editor', 'subscriber'));
        Gate::define('editPosts', $roles('editor'));
        Gate::define('deletePosts', $roles('editor'));
        Gate::define('sendAlerts', $roles('editor'));
        Gate::define('receiveAlerts', $roles('subscriber'));
        Gate::define('accessMessages', $roles('admin', 'editor', 'subscriber'));
    }

    /**
     * Public forms: 3 requests per 10 minutes per IP. Named limiters keep a separate
     * counter per limiter, so one form never uses up another form's attempts.
     */
    protected function defineRateLimiters(): void
    {
        RateLimiter::for('password-email', fn (Request $request) => Limit::perMinutes(10, 3)->by($request->ip()));
        RateLimiter::for('contact', fn (Request $request) => Limit::perMinutes(10, 3)->by($request->ip())
            ->response(fn () => redirect()->to(route('front').'#contact')
                ->withInput()
                ->withErrors(['contact' => 'Too many messages. Please try again in a few minutes.'])));
    }
}
