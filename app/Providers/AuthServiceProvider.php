<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('registerUser', function () {
            return false;
        });

        Gate::define('addUsers', function (User $user) {
            return $user->getRole() == 'admin';
        });

        Gate::define('viewUsers', function (User $user) {
            return $user->getRole() == 'admin';
        });

        Gate::define('editUsers', function (User $user) {
            return $user->getRole() == 'admin';
        });

        Gate::define('deleteUsers', function (User $user) {
            return $user->getRole() == 'admin';
        });

        Gate::define('editProfile', function (User $user) {
            $result = false;

            if ( in_array($user->getRole(), ['admin', 'editor', 'subscriber']) ) {
                $result = true;
            }

            return $result;
        });

        Gate::define('editOptions', function (User $user) {
            return $user->getRole() == 'admin';
        });

        Gate::define('addPosts', function (User $user) {
            return $user->getRole() == 'editor';
        });

        Gate::define('viewPosts', function (User $user) {
            $result = false;

            if ( in_array($user->getRole(), ['editor', 'subscriber']) ) {
                $result = true;
            }

            return $result;
        });

        Gate::define('editPosts', function (User $user) {
            return $user->getRole() == 'editor';
        });

        Gate::define('deletePosts', function (User $user) {
            return $user->getRole() == 'editor';
        });

        Gate::define('sendAlerts', function (User $user) {
            return $user->getRole() == 'editor';
        });

        Gate::define('receiveAlerts', function (User $user) {
            return $user->getRole() == 'subscriber';
        });

        Gate::define('accessMessages', function (User $user) {
            $result = false;

            if ( in_array($user->getRole(), ['admin', 'editor', 'subscriber']) ) {
                $result = true;
            }

            return $result;
        });
    }
}
