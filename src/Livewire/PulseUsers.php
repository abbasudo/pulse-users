<?php

namespace Abbasudo\PulseUsers\Livewire;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;
use Laravel\Pulse\Livewire\Card;
use Laravel\Pulse\Livewire\Concerns;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Url;
use Livewire\Livewire;

/**
 * @internal
 */
#[Lazy]
class PulseUsers extends Card
{
    use Concerns\HasPeriod, Concerns\RemembersQueries;

    const DEFAULT_TIMEZONE = 'UTC';

    #[Url(as: 'usage-hours-timezone')]
    public string $timezone = self::DEFAULT_TIMEZONE;

    /**
     * Render the component.
     */
    public function render(): Renderable
    {
        [$usage, $time, $runAt] = $this->remember(function () {
            return $this->graph(['user_request'], 'count');
        });

        if (Livewire::isLivewireRequest()) {
            $this->dispatch('usage-hours-update', labels: $usage->keys(), data: $usage->values());
        }

        return View::make('pulse-users::livewire.usage-hours', [
            'usage' => $usage,
            'time'  => $time,
            'runAt' => $runAt,
            'timzones' => $this->timezones(),
        ]);
    }

    public function timezones(): array
    {
        $timezones = config('pulse.usage_hours.timezones');

        if (empty($timezones)) {
            $timezones = [(new \DateTimeZone(self::DEFAULT_TIMEZONE))->getName()];
        }

        return array_combine($timezones, $timezones);
    }

    public function getTimezoneOffset($timezoneName): string
    {
        // Create a DateTime object with the specified timezone
        $date = new \DateTime('now', new \DateTimeZone($timezoneName));

        // Get the offset in seconds from UTC
        $offset = $date->getOffset();

        // Convert seconds to hours and minutes
        $hours = intdiv($offset, 3600);
        $minutes = abs($offset % 3600 / 60);

        // Format the offset as "+HH:MM" or "-HH:MM"
        return sprintf("%+03d:%02d", $hours, $minutes);
    }

    /**
     * Retrieve aggregate values for plotting on a graph.
     *
     * @param array $types
     * @param 'count'|'min'|'max'|'sum'|'avg' $aggregate
     * @return Collection<string, \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<string, int|null>>>
     */
    public function graph(array $types, string $aggregate): Collection
    {
        if (!in_array($aggregate, $allowed = ['count', 'min', 'max', 'sum', 'avg'])) {
            throw new InvalidArgumentException(
                "Invalid aggregate type [$aggregate], allowed types: [" . implode(', ', $allowed) . '].'
            );
        }

        $firstBucket = CarbonImmutable::now()->subWeek();

        $structure = collect(range(0, 23))->mapWithKeys(function ($value) {
            return ['hour : ' . $value => 0];
        });

        $readings = $this->connection()->table('pulse_aggregates')
            ->selectRaw('sum(value) as requests')
            ->whereIn('type', [$types])
            ->where('bucket', '>=', $firstBucket)
            ->where('aggregate', $aggregate)
            ->groupBy('hour');

        if ($this->timezone === self::DEFAULT_TIMEZONE) {
            $readings->selectRaw('HOUR(FROM_UNIXTIME(bucket)) as hour');
        } else {
            $readings->selectRaw('HOUR(CONVERT_TZ(FROM_UNIXTIME(bucket), "+00:00", ?)) as hour', [
                $this->getTimezoneOffset($this->timezone)
            ]);
        }

        return $structure->merge(
            $readings->get()->mapWithKeys(function ($reading) {
                return ['hour : ' . $reading->hour => (int)$reading->requests];
            })
        );
    }

    /**
     * Resolve the database connection.
     */
    protected function connection(): Connection
    {
        return DB::connection(config()->get('pulse.storage.database.connection'));
    }

}
