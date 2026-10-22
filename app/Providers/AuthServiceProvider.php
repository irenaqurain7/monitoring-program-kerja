<?php

namespace App\Providers;

use App\Models\Campus;
use App\Models\Unit;
use App\Models\Team;
use App\Models\WorkProgram;
use App\Policies\CampusPolicy;
use App\Policies\UnitPolicy;
use App\Policies\TeamPolicy;
use App\Policies\UserPolicy;
use App\Policies\WorkProgramPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Campus::class => CampusPolicy::class,
        Unit::class => UnitPolicy::class,
        Team::class => TeamPolicy::class,
        User::class => UserPolicy::class,
        WorkProgram::class => WorkProgramPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
