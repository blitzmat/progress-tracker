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

            <div class="grid grid-cols-1 gap-8">
                {{-- Left Column: Practice Timer --}}
                <div class="lg:col-span-1">
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
                                            <option value="{{ $activity->id }}"
                                                @if ($selectedActivityForTimer == $activity->id) selected @endif>
                                                @if ($activity->hasAlphaTab())
                                                    🎼
                                                @endif
                                                [{{ $activity->goal->name }}] {{ $activity->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($selectedActivityDescription)
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            {{ $selectedActivityDescription }}</p>
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
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-600 dark:text-white">{{ e($timerNotes) }}</textarea>
                                </div>

                                {{-- AlphaTab playback controls (shown only if activity has alphaTab) --}}
                                @if ($hasAlphaTab)
                                    <div class="pl-4 border-l-2 border-purple-300 dark:border-purple-500 space-y-3">
                                        <div class="flex items-center space-x-2">
                                            <label class="text-sm text-gray-700 dark:text-gray-300 w-16">Tempo</label>
                                            <input wire:model.live="alphaTabTempo" type="number" min="40"
                                                max="240"
                                                class="w-20 border rounded px-2 py-1 dark:bg-gray-600 dark:text-white text-sm">
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <label class="text-sm text-gray-700 dark:text-gray-300 w-16">Volume</label>
                                            <input wire:model.live="alphaTabVolume" type="range" min="0"
                                                max="1" step="0.01"
                                                class="w-full h-2 bg-gray-200 rounded-lg dark:bg-gray-600">
                                            <span class="text-xs w-10">{{ intval($alphaTabVolume * 100) }}%</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <label class="text-sm text-gray-700 dark:text-gray-300 w-16">Time
                                                Sig.</label>
                                            <select wire:model.live="alphaTabTimeSignature"
                                                class="border rounded px-2 py-1 dark:bg-gray-600 dark:text-white text-sm">
                                                <option value="2/4">2/4</option>
                                                <option value="3/4">3/4</option>
                                                <option value="4/4">4/4</option>
                                                <option value="6/8">6/8</option>
                                            </select>
                                        </div>
                                    </div>
                                @endif

                                <button wire:click="startTimer"
                                    class="w-full px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                                    Start Practice Session
                                </button>
                            </div>
                        </div>
                    @else
                        {{-- Active Timer --}}
                        <div wire:ignore>
                            <div x-data="timerComponentData($wire)" x-init="init()">
                                {{-- Pre‑declare essential properties --}}
                                <div
                                    class="bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700 p-6 h-full">
                                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Practice Timer
                                    </h2>
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
                                            @php $currentActivity = $activities->firstWhere('id', $selectedActivityForTimer); @endphp
                                            <p class="text-lg text-blue-600 dark:text-blue-400 font-semibold">
                                                {{ $currentActivity ? $currentActivity->name : 'Loading...' }}
                                            </p>
                                            @if ($timerNotes)
                                                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                                    "{{ $timerNotes }}"</p>
                                            @endif
                                        </div>

                                        {{-- AlphaTab container --}}
                                        @if ($hasAlphaTab)
                                            <div
                                                class="mt-4 border-t pt-4 border-purple-200 dark:border-purple-700 text-left">
                                                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                                    AlphaTab Exercise</h3>
                                                <div class="at-wrap">
                                                    <div class="at-viewport">
                                                        <div wire:ignore id="alphaTab-container"
                                                            class="w-full relative overflow-x-auto h-full max-h-min min-h-[200px] bg-white">
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- Inline controls (can adjust during session) --}}
                                                <div class="mt-3 grid grid-cols-3 gap-2">
                                                    <div>
                                                        <label class="text-xs text-gray-500">Tempo</label>
                                                        <input wire:model.live="alphaTabTempo" type="number"
                                                            min="40" max="240"
                                                            class="w-full border rounded px-2 py-1 dark:bg-gray-600 dark:text-white text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="text-xs text-gray-500">Volume</label>
                                                        <input wire:model.live="alphaTabVolume" type="range"
                                                            min="0" max="1" step="0.01" class="w-full">
                                                    </div>
                                                    <div>
                                                        <label class="text-xs text-gray-500">Time Sig.</label>
                                                        <select wire:model.live="alphaTabTimeSignature"
                                                            class="w-full border rounded px-2 py-1 dark:bg-gray-600 dark:text-white text-sm">
                                                            <option value="2/4">2/4</option>
                                                            <option value="3/4">3/4</option>
                                                            <option value="4/4">4/4</option>
                                                            <option value="6/8">6/8</option>
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
                                <div x-data="{ showCountdown: false, countdownNumber: 3 }" x-init="$watch('$root.showCountdown', value => showCountdown = value);
                                $watch('$root.countdownNumber', value => countdownNumber = value)">

                                    <div x-show="showCountdown" x-cloak
                                        class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50">

                                        <div class="text-center">
                                            <div x-text="countdownNumber" class="text-8xl font-bold text-white"></div>

                                            <div x-show="countdownNumber === 0" class="text-3xl font-bold text-white">
                                                Go!
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Right Column: Recent Practice Sessions (unchanged, but update widget display) --}}
                <div class="lg:col-span-1">
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
                                                        @php $alphaTab = $entry->widgets->firstWhere('type', \App\Enums\WidgetType::AlphaTab); @endphp
                                                        @if ($alphaTab && isset($alphaTab->settings['tex']))
                                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                                🎼 AlphaTab: {{ $alphaTab->settings['tempo'] ?? 120 }}
                                                                bpm
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

</div>
