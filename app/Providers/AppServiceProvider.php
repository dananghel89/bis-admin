<?php

namespace App\Providers;

use App\Http\Models\Options;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // All site options as [name => value], read by get_option(). Lazy: nothing touches
        // the database until the first get_option() call, so artisan works without MySQL.
        $this->app->singleton('project.options', function () {
            $model = new Options();

            if (! Schema::hasTable($model->getTable())) {
                return [];
            }

            return $model->newQuery()->pluck('value', 'name')->all();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }
}
