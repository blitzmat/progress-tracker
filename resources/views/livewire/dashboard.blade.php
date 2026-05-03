<div>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session()->has('message'))
                <div class="mb-6 px-4 py-3 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-lg">
                    {{ session('message') }}
                </div>
            @endif
            @if (session()->has('error'))
                <div class="mb-6 px-4 py-3 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Left Column --}}
                <div class="lg:col-span-1">
                    {{-- Setup Form (visible when timer not active) --}}
                    @if (!$timerActive)
                        <div
                            class="bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700 p-6">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Practice Timer</h2>
                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Activity</label>
                                    <select wire:change="setActivity($event.target.value)" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-600 dark:text-white">
                                        <option value="">Select an Activity</option>
                                        @foreach ($activities as $activity)
                                            <option value="{{ $activity->id }}">
                                                @if ($activity->default_tempo)
                                                    🎵
                                                @endif
                                                @if ($activity->default_finger_note_type)
                                                    🖐️
                                                @endif
                                                [{{ $activity->goal->name }}] {{ $activity->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($selectedActivityDescription)
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            {{ $selectedActivityDescription }}
                                        </p>
                                    @endif
                                    @if ($selectedActivityForTimer && ($hasMetronome || $hasFingerWarmup))
                                        <div
                                            class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Widgets for this Activity</h4>
                                            @if ($hasMetronome)
                                                <div
                                                    class="flex items-center text-sm text-gray-600 dark:text-gray-400 space-x-2">
                                                    <span class="text-blue-500">🎵</span>
                                                    <span>Metronome: {{ $widgetTempo }} bpm,
                                                        {{ $widgetTimeSignature }}, {{ $widgetSound }}
                                                        ({{ intval($widgetVolume * 100) }}%)</span>
                                                </div>
                                            @endif
                                            @if ($hasFingerWarmup)
                                                <div
                                                    class="flex items-center text-sm text-gray-600 dark:text-gray-400 space-x-2 mt-1">
                                                    <span class="text-green-500">🖐️</span>
                                                    <span>Finger Warm‑up: {{ $fingerWarmupPattern }}
                                                        ({{ ucfirst($fingerWarmupNoteType) }} notes)</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration
                                        (min)</label>
                                    <input wire:model="timerDuration" type="number" min="1" max="240"
                                        required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-600 dark:text-white">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                                    <textarea wire:model="timerNotes" rows="3" placeholder="What are you working on?"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-600 dark:text-white"></textarea>
                                </div>

                                <button wire:click="startTimer"
                                    class="w-full px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                                    Start Practice Session
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- Active Timer + Countdown (inside wire:ignore, shown when timerActive) --}}
                    @if ($timerActive)
                        <div x-data="timerComponentData($wire)" wire:ignore>
                            {{-- Timer Card --}}
                            <div
                                class="bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700 p-6 h-full">
                                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Practice Timer
                                </h2>

                                {{-- Active Timer Display --}}
                                <div class="text-center">
                                    <div class="mb-6">
                                        <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-2"
                                            x-text="Math.floor(secondsRemaining / 60) + ':' + (secondsRemaining % 60).toString().padStart(2, '0')">
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Time remaining</p>
                                    </div>
                                    <div class="mb-6 p-4 bg-white dark:bg-gray-700 rounded-lg">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Current
                                            Session:</p>
                                        @php
                                            $currentActivity = $activities->firstWhere('id', $selectedActivityForTimer);
                                        @endphp
                                        <p class="text-lg text-blue-600 dark:text-blue-400 font-semibold">
                                            {{ $currentActivity ? $currentActivity->name : 'Loading...' }}
                                        </p>
                                        @if ($timerNotes)
                                            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                                "{{ $timerNotes }}"</p>
                                        @endif
                                    </div>

                                    {{-- Metronome Controls --}}
                                    @if ($hasMetronome)
                                        <div class="mt-4 border-t pt-4 border-blue-200 dark:border-blue-700 text-left">
                                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                                Metronome</h3>
                                            <div class="space-y-2">
                                                <div class="flex items-center space-x-2">
                                                    <label
                                                        class="text-xs text-gray-500 dark:text-gray-400 w-10">BPM</label>
                                                    <input wire:model.live="widgetTempo" type="number" min="40"
                                                        max="240"
                                                        class="w-16 border rounded px-2 py-1 text-sm dark:bg-gray-600 dark:text-white">
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <label
                                                        class="text-xs text-gray-500 dark:text-gray-400 w-10">Sound</label>
                                                    <select wire:model.live="widgetSound"
                                                        class="border rounded px-2 py-1 text-sm dark:bg-gray-600 dark:text-white">
                                                        <option value="beep">Beep</option>
                                                        <option value="click">Click</option>
                                                        <option value="woodblock">Wood</option>
                                                        <option value="pulse">Pulse</option>
                                                    </select>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <label class="text-xs text-gray-500 dark:text-gray-400 w-10">Time
                                                        Sig.</label>
                                                    <select wire:model.live="widgetTimeSignature"
                                                        class="border rounded px-2 py-1 dark:bg-gray-600 dark:text-white text-sm">
                                                        <option value="2/4">2/4</option>
                                                        <option value="3/4">3/4</option>
                                                        <option value="4/4">4/4</option>
                                                        <option value="6/8">6/8</option>
                                                    </select>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <label
                                                        class="text-xs text-gray-500 dark:text-gray-400 w-10">Volume</label>
                                                    <input wire:model.live="widgetVolume" type="range" min="0"
                                                        max="1" step="0.01"
                                                        class="w-full h-2 bg-gray-200 rounded dark:bg-gray-600">
                                                    <span
                                                        class="text-xs w-10">{{ intval($widgetVolume * 100) }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Finger Warm‑up Display --}}
                                    @if ($hasFingerWarmup)
                                        <div
                                            class="mt-4 border-t pt-4 border-green-200 dark:border-green-700 text-left">
                                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Finger
                                                Warm-up</h3>
                                            <div class="flex gap-1 justify-center">
                                                <template x-for="(num, index) in fingerPatternArray"
                                                    :key="index">
                                                    <div class="w-12 h-12 flex items-center justify-center rounded text-lg font-bold border"
                                                        :class="fingerPosition === index ?
                                                            'bg-green-600 text-white border-green-600' :
                                                            'bg-gray-100 dark:bg-gray-600 text-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-500'"
                                                        x-text="num">
                                                    </div>
                                                </template>
                                            </div>
                                            <p class="text-xs text-center text-gray-400 mt-1">
                                                Pattern: <span x-text="fingerWarmupPattern"></span> &bull;
                                                <span
                                                    x-text="fingerWarmupNoteType.charAt(0).toUpperCase() + fingerWarmupNoteType.slice(1)"></span>
                                                notes
                                            </p>

                                            {{-- Allow changing note type / pattern during session --}}
                                            <div class="mt-3 grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="text-xs text-gray-500 dark:text-gray-400">Note
                                                        Type</label>
                                                    <select wire:model.live="fingerWarmupNoteType"
                                                        class="w-full border rounded px-2 py-1 dark:bg-gray-600 dark:text-white text-sm">
                                                        <option value="quarter">Quarter</option>
                                                        <option value="eighth">Eighth</option>
                                                        <option value="sixteenth">16th</option>
                                                        <option value="thirty-second">32nd</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label
                                                        class="text-xs text-gray-500 dark:text-gray-400">Pattern</label>
                                                    <select wire:model.live="fingerWarmupPattern"
                                                        class="w-full border rounded px-2 py-1 dark:bg-gray-600 dark:text-white text-sm">
                                                        <option value="1-2-3-4">1‑2‑3‑4</option>
                                                        <option value="1-4-2-3">1‑4‑2‑3</option>
                                                        <option value="4-3-2-1">4‑3‑2‑1</option>
                                                        <option value="1-3-2-4">1‑3‑2‑4</option>
                                                        <option value="2-4-1-3">2‑4‑1‑3</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <button @click="stopTimer()"
                                        class="w-full mt-4 px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 font-medium">
                                        Stop Timer
                                    </button>
                                </div>
                            </div>

                            {{-- Countdown Overlay --}}
                            <div x-show="showCountdown" x-cloak
                                class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50">
                                <div class="text-center">
                                    <div x-text="countdownNumber" class="text-8xl font-bold text-white"></div>
                                    <div x-show="countdownNumber === 0" class="text-3xl font-bold text-white">Go!
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Right Column: Recent Practice Sessions --}}
                <div class="lg:col-span-1">
                    <!-- Same as before, unchanged -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full">
                        <div class="p-6">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Recent Practice
                                Sessions</h2>
                            @if ($recentEntries->count() > 0)
                                <div class="space-y-4 overflow-y-auto">
                                    @foreach ($recentEntries as $entry)
                                        <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1">
                                                    <div class="flex items-center justify-between">
                                                        <h3
                                                            class="font-medium text-gray-900 dark:text-gray-100 text-sm">
                                                            {{ $entry->activity->name }}</h3>
                                                        <span
                                                            class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded-full">{{ $entry->duration }}m</span>
                                                    </div>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                        <span class="font-medium">Goal:</span>
                                                        {{ $entry->activity->goal->name }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                        {{ $entry->date->format('M j, Y') }} at
                                                        {{ $entry->created_at->format('g:i A') }}
                                                    </p>
                                                    @if ($entry->notes)
                                                        <p class="mt-2 text-xs text-gray-700 dark:text-gray-300">
                                                            {{ Str::limit($entry->notes, 100) }}</p>
                                                    @endif
                                                    @if ($entry->widgets->isNotEmpty())
                                                        @php $metronome = $entry->widgets->firstWhere('type', \App\Enums\WidgetType::Metronome); @endphp
                                                        @if ($metronome && isset($metronome->settings['tempo']))
                                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                                🥁 {{ $metronome->settings['tempo'] }} bpm
                                                                ({{ $metronome->settings['sound'] ?? 'beep' }})
                                                                @if (isset($metronome->settings['timeSignature']))
                                                                    ({{ $metronome->settings['timeSignature'] }})
                                                                @endif
                                                            </p>
                                                        @endif
                                                    @endif
                                                </div>
                                                <div class="flex space-x-1 ml-2">
                                                    <button wire:click="confirmDelete({{ $entry->id }})"
                                                        class="px-2 py-1 bg-red-600 text-white rounded text-xs hover:bg-red-700"
                                                        title="Delete Entry">✕</button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <p class="text-gray-500 dark:text-gray-400">No practice sessions yet.</p>
                                    <p class="text-gray-400 dark:text-gray-500 text-sm">Start a timer to begin
                                        tracking!</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    @if ($confirmingDeletionId)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl max-w-md w-full">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Confirm Deletion</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">Are you sure you want to delete this practice session?
                    This action cannot be undone.</p>
                <div class="flex justify-end space-x-3">
                    <button wire:click="cancelDelete"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Cancel</button>
                    <button wire:click="deleteEntry"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Delete Session</button>
                </div>
            </div>
        </div>
    @endif

    <audio id="notification-sound" src="https://assets.mixkit.co/sfx/preview/mixkit-alarm-digital-clock-beep-989.mp3"
        preload="auto"></audio>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</div>
