<?php

namespace Abbasudo\PulseUsers\Livewire;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;
use Laravel\Pulse\Livewire\Card;
use Laravel\Pulse\Livewire\Concerns;
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
        [$usage, $time, $runAt] = $this->remember(function () {
            return $this->graph('user_request', 'count', $this->periodAsInterval());
        });


        if (Livewire::isLivewireRequest()) {
            $this->dispatch('usage-hours-update', lable: $usage->keys(), data: $usage->values());
        }

        return View::make('pulse-users::livewire.usage-hours', [
            'usage' => $usage,
            'time'  => $time,
            'runAt' => $runAt,
        ]);
    }

    /**
     * Retrieve aggregate values for plotting on a graph.
     *
     * @param list<string> $types
     * @param 'count'|'min'|'max'|'sum'|'avg' $aggregate
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<string, int|null>>>
     */
    public function graph(string $type, string $aggregate, CarbonInterval $interval): Collection
    {
        if (!in_array($aggregate, $allowed = ['count', 'min', 'max', 'sum', 'avg'])) {
            throw new InvalidArgumentException(
                "Invalid aggregate type [$aggregate], allowed types: [" . implode(', ', $allowed) . '].'
            );
        }

        $period = $interval->totalSeconds / 60;

        $structure = collect()->range(0, 23)->mapWithKeys(function ($key) {
            return [str_pad($key, 3, ':0', STR_PAD_LEFT) => 0];
        })->keyBy(function ($item, $key) {
            return (string)$key;
        });

        $readings = $this->connection()->table('pulse_aggregates')
            ->select(['bucket', 'type', 'key', 'value'])
            ->where('type', $type)
            ->where('aggregate', $aggregate)
            ->where('period', $period)
            ->get();


        return $structure->merge(
            $readings->mapWithKeys(function ($reading) {
                return [CarbonImmutable::createFromTimestamp($reading->bucket)->format(':H') => (int)$reading->value];
            })
        )->keyBy(function ($item, $key) {
            return (string)str($key)->after(':');
        });
    }

    /**
     * Resolve the database connection.
     */
    protected function connection(): Connection
    {
        return DB::connection(config()->get('pulse.storage.database.connection'));
    }

}
