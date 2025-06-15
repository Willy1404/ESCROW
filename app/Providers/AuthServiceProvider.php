<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\EscrowTransaction;
use App\Models\Dispute;
use App\Policies\EscrowTransactionPolicy;
use App\Policies\DisputePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        EscrowTransaction::class => EscrowTransactionPolicy::class,
        Dispute::class => DisputePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define a gate for bank staff operations (now includes maker, checker, it_support)
        Gate::define('bank-staff', function ($user) {
            return in_array($user->role, ['maker', 'checker', 'it_support']);
        });

        // Define gates for buyer and seller
        Gate::define('buyer', function ($user) {
            return $user->role === 'buyer';
        });

        Gate::define('seller', function ($user) {
            return $user->role === 'seller';
        });
        
        // Define gates for maker-checker process
        Gate::define('maker', function ($user) {
            return $user->role === 'maker';
        });

        Gate::define('checker', function ($user) {
            return $user->role === 'checker';
        });

        // A maker should not be able to check actions
        Gate::define('check-action', function ($user, $pendingAction) {
            return $user->role === 'checker' && $user->user_id !== $pendingAction->maker_id;
        });

        // IT Support gate
        Gate::define('it_support', function ($user) {
            return $user->role === 'it_support';
        });
    }
}