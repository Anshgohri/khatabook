<?php

namespace App\Providers;

use App\Models\Expense;
use App\Models\Sale;
use App\Models\User;
use App\Observers\ExpenseObserver;
use App\Observers\SaleExcelObserver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public static function likeOperator(): string
    {
        return \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Vite::useBuildDirectory('dashboard-assets/build');

        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $view->with('storeDetails', \App\Models\Setting::getStoreDetails());
        });

        $this->configureDefaults();
        $this->configureAuthorization();
        $this->configureObservers();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * An Admin has unrestricted access to every ability; all other roles fall through to their policies.
     */
    protected function configureAuthorization(): void
    {
        Gate::before(fn (User $user) => $user->isAdmin() ?: null);
    }

    /**
     * Register Eloquent model observers.
     */
    protected function configureObservers(): void
    {
        Sale::observe(SaleExcelObserver::class);
        Expense::observe(ExpenseObserver::class);
    }
}
