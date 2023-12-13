<?php

namespace Abbasudo\PulseUsers;

use Abbasudo\PulseUsers\Livewire\PulseUsers;
use Illuminate\Contracts\Foundation\Application;
use Livewire\LivewireManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PulseUsersServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('pulse-users')
            ->hasViews('pulse-users');

        $this->callAfterResolving('livewire', function (LivewireManager $livewire, Application $app) {
            $livewire->component('pulse.usage-hours', PulseUsers::class);
        });
    }
}
