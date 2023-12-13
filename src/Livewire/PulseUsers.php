<?php

namespace Abbasudo\PulseUsers\Livewire;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\View;
use Laravel\Pulse\Livewire\Card;
use Laravel\Pulse\Livewire\Concerns;

class PulseUsers extends Card
{
    use Concerns\HasPeriod, Concerns\RemembersQueries;

    /**
     * Render the component.
     */
    public function render(): Renderable
    {
        return View::make('pulse-users::livewire.usage-hours');
    }
}
