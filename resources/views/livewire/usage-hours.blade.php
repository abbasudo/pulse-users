<x-pulse::card :cols="$cols" :rows="$rows" :class="$class">
    <x-pulse::card-header
            name="Usage Distribution"
            title="Time: {{ number_format($time) }}ms; Run at: {{ $runAt }};"
            details="past 7 days"
    >
        <x-slot:icon>
            <x-pulse::icons.clock/>
        </x-slot:icon>
        <x-slot:actions>
            <x-pulse::select
                    wire:model.live="timezone"
                    label="Timezone"
                    :options="$timzones"
                    @change="loading = true"
            />
        </x-slot:actions>
    </x-pulse::card-header>

    <x-pulse::scroll wire:poll.5s="">
        @if ($usage->filter()->isEmpty())
            <x-pulse::no-results/>
        @else
            <div class="grid gap-3 mx-px mb-px">
                <div wire:key="usage-hours">
                    @php
                        $highest = $usage->flatten()->max();
                    @endphp

                    <div class="mt-3 relative">
                        <div class="absolute -left-px -top-2 max-w-fit h-4 flex items-center px-1 text-xs leading-none text-white font-bold bg-purple-500 rounded after:[--triangle-size:4px] after:border-l-purple-500 after:absolute after:right-[calc(-1*var(--triangle-size))] after:top-[calc(50%-var(--triangle-size))] after:border-t-[length:var(--triangle-size)] after:border-b-[length:var(--triangle-size)] after:border-l-[length:var(--triangle-size)] after:border-transparent">
                            {{ number_format($highest) }}
                        </div>

                        <div
                                wire:ignore
                                x-data="usageChart({
                                labels: @js($usage->keys()),
                                data: @js($usage->values()),
                       })"
                        >
                            <canvas x-ref="canvas"
                                    class="ring-1 ring-gray-900/5 dark:ring-gray-100/10 bg-gray-50 dark:bg-gray-800 rounded-md shadow-sm"></canvas>
                        </div>
                    </div>
                    <div class="flex items-center text-gray-400 dark:text-gray-600 font-medium">
                        <span class="px-1">
                            0
                        </span>
                        <hr class="flex-grow">
                        <span class="px-3">
                            7
                        </span>
                        <hr class="flex-grow">
                        <span class="px-3">
                            15
                        </span>
                        <hr class="flex-grow">
                        <span class="pl-3">
                            23
                        </span>
                    </div>
                </div>
            </div>
        @endif
    </x-pulse::scroll>
</x-pulse::card>

@script
<script>
  Alpine.data('usageChart', (config) => ({
    init() {
      let chart = new Chart(
        this.$refs.canvas,
        {
          type: 'bar',
          data: {
            labels: config.labels,
            datasets: [
              {
                label: 'Requests',
                borderColor: '#9333ea',
                backgroundColor: 'rgba(147,51,234,0.10)',
                borderWidth: 2,
                borderRadius: 3,
                data: config.data,
              },
            ],
          },
          options: {
            maintainAspectRatio: false,
            layout: {
              autoPadding: false,
              padding: {
                top: 1,
              },
            },
            datasets: {
              line: {
                borderWidth: 2,
                borderCapStyle: 'round',
                pointHitRadius: 10,
                pointStyle: false,
                tension: 0.2,
                spanGaps: false,
                segment: {
                  borderColor: (ctx) => ctx.p0.raw === 0 && ctx.p1.raw === 0 ? 'transparent' : undefined,
                }
              }
            },
            scales: {
              x: {
                display: false,
              },
              y: {
                display: false,
                min: 0,
                max: this.highest(config.data),
              },
            },
            plugins: {
              legend: {
                display: false,
              },
              tooltip: {
                mode: 'index',
                position: 'nearest',
                intersect: false,
                callbacks: {
                  beforeBody: (context) => context
                    .map(item => `${item.dataset.label}: ${config.sampleRate < 1 ? '~' : ''}${item.formattedValue}`)
                    .join(', '),
                  label: () => null,
                },
              },
            },
          },
        }
      )

      Livewire.on('usage-hours-update', ({ labels, data }) => {
        if (chart === undefined) {
          return
        }

        chart.data.labels = labels
        chart.data.datasets[0].data = data
        chart.options.scales.y.max = this.highest(data)
        chart.update()
      })
    },
    highest(readings) {
      return Math.max(...Object.values(readings))
    }
  }))
</script>
@endscript
