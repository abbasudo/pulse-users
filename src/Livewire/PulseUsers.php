<?php

namespace Abbasudo\PulseUsers\Livewire;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Laravel\Pulse\Facades\Pulse;
use Laravel\Pulse\Livewire\Card;
use Laravel\Pulse\Livewire\Concerns;
use Laravel\Pulse\Recorders\Queues as QueuesRecorder;
use Livewire\Attributes\Lazy;
use Livewire\Livewire;

/**
 * @internal
 */
#[Lazy]
class PulseUsers extends Card
{
    use Concerns\HasPeriod, Concerns\RemembersQueries;

    /**
     * Render the component.
     */
    public function render(): Renderable
    {
        [$queues, $time, $runAt] = $this->remember(fn () => Pulse::graph(
            ['user_request'],
            'count',
            $this->periodAsInterval(),
        ));

        if (Livewire::isLivewireRequest()) {
            $this->dispatch('queues-chart-update', queues: $queues);
        }

        return View::make('pulse-users::livewire.usage-hours', [
            'queues' => $queues,
            'showConnection' => $queues->keys()->map(fn ($queue) => Str::before($queue, ':'))->unique()->count() > 1,
            'time' => $time,
            'runAt' => $runAt,
            'config' => Config::get('pulse.recorders.'.QueuesRecorder::class),
        ]);
    }
}
