<x-filament-panels::page>
    <form wire:submit="create" class="fi-sc-form">
        {{ $this->form }}

        <div
            class="flex items-center gap-6 mt-16"
            x-data="timer()"
            x-init="init()"
        >
            <x-filament::button
                type="submit"
                tooltip="Занимает от 30 до 300 секунд"
                @click="start()"
            >
                Рассчитать
            </x-filament::button>

            <x-filament::loading-indicator wire:loading class="h-5 w-5"/>

            <!-- Таймер -->
            <div
                class="text-sm text-gray-600 tabular-nums"
                x-show="running"
                x-text="formatted"
                wire:loading
            ></div>
        </div>
    </form>

    @if($this->result)


        <div class="mt-6">
            <h3 class="text-lg font-semibold mb-2">Результат расчёта</h3>

            <pre class="bg-gray-900 text-gray-100 p-4 rounded overflow-auto text-sm leading-relaxed">
@php
    $lines = explode("\n", json_encode($this->result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
@endphp
                @foreach ($lines as $i => $line)
                    <span class="text-gray-500 select-none">{{ str_pad($i + 1, 3, ' ', STR_PAD_LEFT) }} |</span> {{ $line }}
                @endforeach
        </pre>
        </div>
    @endif

    <x-filament-actions::modals/>

    <!-- Alpine logic -->
    <script>
        function timer() {
            return {
                startTime: null,
                interval: null,
                elapsed: 0,
                running: false,

                init() {
                    // когда Livewire закончил — стопаем таймер
                    Livewire.hook('message.processed', () => {
                        this.stop()
                    })
                },

                start() {
                    this.stop()
                    this.running = true
                    this.startTime = performance.now()

                    this.interval = setInterval(() => {
                        this.elapsed = performance.now() - this.startTime
                    }, 16) // ~60fps
                },

                stop() {
                    if (this.interval) {
                        clearInterval(this.interval)
                        this.interval = null
                    }
                    this.running = false
                },

                get formatted() {
                    const totalMs = Math.floor(this.elapsed)
                    const ms = totalMs % 1000
                    const sec = Math.floor(totalMs / 1000)

                    return `${sec}.${ms.toString().padStart(2, '0')}`
                },
            }
        }
    </script>
</x-filament-panels::page>
