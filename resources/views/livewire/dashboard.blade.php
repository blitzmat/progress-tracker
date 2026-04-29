<div>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Flash Messages -->
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


            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column: Practice Timer -->
                <div class="lg:col-span-1" x-data="{
                    timerActive: @entangle('timerActive'),
                    secondsRemaining: 0,
                    interval: null,

                    init() {
                        // Listen for Livewire timer start events
                        this.$wire.$on('start-timer', (event) => {
                            this.secondsRemaining = event.duration;
                            this.startTimer();
                        });

                        this.$wire.$on('stop-timer', () => {
                            this.stopTimer();
                        });
                    },

                    startTimer() {
                        this.stopTimer();
                        this.interval = setInterval(() => {
                            this.secondsRemaining--;
                            document.getElementById('timer-display').textContent =
                                Math.floor(this.secondsRemaining / 60) + ':' +
                                (this.secondsRemaining % 60).toString().padStart(2, '0');

                            if (this.secondsRemaining <= 0) {
                                this.stopTimer();
                                this.$wire.dispatch('timer-completed');
                            }
                        }, 1000);
                    },

                    stopTimer() {
                        if (this.interval) {
                            clearInterval(this.interval);
                            this.interval = null;
                        }
                    }
                }">
                    <div
                        class="bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700 p-6 h-full">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Practice Timer</h2>

                        @if (!$timerActive)
                            <!-- Timer Setup Form with Notes -->
                            <form wire:submit.prevent="startTimer">
                                <div class="space-y-4">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Activity</label>
                                        <select wire:model="selectedActivityForTimer" required
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 rounded-md dark:bg-gray-600 dark:text-white">
                                            <option value="">Select an Activity</option>
                                            @foreach ($activities as $activity)
                                                <option value="{{ $activity->id }}">[{{ $activity->goal->name }}]
                                                    {{ $activity->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration
                                            (minutes)</label>
                                        <input wire:model="timerDuration" type="number" min="1" max="240"
                                            required
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 rounded-md dark:bg-gray-600 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes
                                            (Optional)</label>
                                        <textarea wire:model="timerNotes" rows="3" placeholder="What are you working on?"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 rounded-md dark:bg-gray-600 dark:text-white"></textarea>
                                    </div>
                                    <div>
                                        <button type="submit"
                                            class="w-full px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                                            Start Practice Session
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @else
                            <!-- Active Timer Display -->
                            <div class="text-center">
                                <div class="mb-6">
                                    <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-2"
                                        id="timer-display">
                                        {{ floor($timerRemaining / 60) }}:{{ sprintf('%02d', $timerRemaining % 60) }}
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Time remaining</p>
                                </div>

                                <div class="mb-6 p-4 bg-white dark:bg-gray-700 rounded-lg">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Current
                                        Session:</p>
                                    @if ($selectedActivityForTimer)
                                        @php
                                            $activity = $activities->firstWhere('id', $selectedActivityForTimer);
                                        @endphp
                                        <p class="text-lg text-blue-600 dark:text-blue-400 font-semibold">
                                            {{ $activity ? $activity->name : 'Loading...' }}
                                        </p>
                                        @if ($timerNotes)
                                            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                                "{{ $timerNotes }}"
                                            </p>
                                        @endif
                                    @endif
                                </div>

                                <button wire:click="stopTimer"
                                    class="w-full px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 font-medium">
                                    Stop Timer
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right Column: Recent Practice Sessions -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full">
                        <div class="p-6">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Recent Practice
                                Sessions</h2>

                            @if ($recentEntries->count() > 0)
                                <div class="space-y-4 max-h-96 overflow-y-auto">
                                    @foreach ($recentEntries as $entry)
                                        <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                                            @if ($editingEntryId === $entry->id)
                                                <!-- Edit Entry Form -->
                                                <form wire:submit.prevent="updateEntry">
                                                    <div class="space-y-3">
                                                        <div>
                                                            <label
                                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Activity</label>
                                                            <select wire:model="editingEntryActivityId" required
                                                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 rounded-md dark:bg-gray-600 dark:text-white">
                                                                @foreach ($activities as $activity)
                                                                    <option value="{{ $activity->id }}"
                                                                        {{ $editingEntryActivityId == $activity->id ? 'selected' : '' }}>
                                                                        [{{ $activity->goal->name }}]
                                                                        {{ $activity->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label
                                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration
                                                                (minutes)
                                                            </label>
                                                            <input wire:model="editingEntryDuration" type="number"
                                                                min="1" required
                                                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 rounded-md dark:bg-gray-600 dark:text-white">
                                                        </div>
                                                        <div>
                                                            <label
                                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date
                                                                & Time</label>
                                                            <input wire:model="editingEntryDate" type="datetime-local"
                                                                required
                                                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 rounded-md dark:bg-gray-600 dark:text-white">
                                                        </div>
                                                        <div>
                                                            <label
                                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                                                            <textarea wire:model="editingEntryNotes" rows="2"
                                                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 rounded-md dark:bg-gray-600 dark:text-white"></textarea>
                                                        </div>
                                                        <div class="flex space-x-2 pt-2">
                                                            <button type="submit"
                                                                class="px-3 py-1 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">
                                                                Save
                                                            </button>
                                                            <button type="button" wire:click="cancelEdit"
                                                                class="px-3 py-1 bg-gray-500 text-white rounded-md text-sm hover:bg-gray-600">
                                                                Cancel
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            @else
                                                <!-- Display Mode -->
                                                <div class="flex justify-between items-start">
                                                    <div class="flex-1">
                                                        <div class="flex items-center justify-between">
                                                            <h3
                                                                class="font-medium text-gray-900 dark:text-gray-100 text-sm">
                                                                {{ $entry->activity->name }}</h3>
                                                            <span
                                                                class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded-full">
                                                                {{ $entry->duration }}m
                                                            </span>
                                                        </div>
                                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                            <span class="font-medium">Goal:</span>
                                                            {{ $entry->activity->goal->name }}
                                                        </p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                            <svg class="w-4 h-4 inline mr-1" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                                </path>
                                                            </svg>
                                                            {{ $entry->date->format('M j, Y') }}
                                                            <svg class="w-4 h-4 inline ml-3 mr-1" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z">
                                                                </path>
                                                            </svg>
                                                            {{ $entry->created_at->format('g:i A') }}
                                                        </p>
                                                        @if ($entry->notes)
                                                            <p class="mt-2 text-xs text-gray-700 dark:text-gray-300">
                                                                {{ Str::limit($entry->notes, 100) }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="flex space-x-1 ml-2">
                                                        <button wire:click="editEntry({{ $entry->id }})"
                                                            class="px-2 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700"
                                                            title="Edit Entry">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                        <button wire:click="confirmDelete({{ $entry->id }})"
                                                            class="px-2 py-1 bg-red-600 text-white rounded text-xs hover:bg-red-700"
                                                            title="Delete Entry">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <div class="text-gray-400 dark:text-gray-500 mb-4">
                                        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a极简的MP3音频数据"
                                                type="audio/mpeg">
                                        </svg>
                                    </div>
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

    <!-- Delete Confirmation Modal -->
    @if ($confirmingDeletionId)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl max-w-md w-full">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Confirm Deletion</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Are you sure you want to delete this practice session? This action cannot be undone.
                </p>
                <div class="flex justify-end space-x-3">
                    <button wire:click="cancelDelete"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button wire:click="deleteEntry"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Delete Session
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- JavaScript for Timer and Sound -->
    <script>
        document.addEventListener('livewire:init', () => {
            let timerInterval;
            let totalSeconds = 0;
            let secondsRemaining = 0;

            // Function to update the timer display
            function updateTimerDisplay(seconds) {
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = seconds % 60;
                const displayElement = document.getElementById('timer-display');
                if (displayElement) {
                    displayElement.textContent =
                        `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
                }
            }

            // Listen for Livewire events
            Livewire.on('start-timer', (event) => {
                totalSeconds = event.duration;
                secondsRemaining = totalSeconds;

                // Update initial display
                updateTimerDisplay(secondsRemaining);

                // Clear any existing timer
                clearInterval(timerInterval);

                // Start new timer
                timerInterval = setInterval(() => {
                    secondsRemaining--;

                    // Update the display
                    updateTimerDisplay(secondsRemaining);

                    if (secondsRemaining <= 0) {
                        clearInterval(timerInterval);
                        // Notify Livewire that timer completed
                        Livewire.dispatch('timer-completed');
                    }
                }, 1000);
            });

            Livewire.on('stop-timer', () => {
                clearInterval(timerInterval);
                // Reset the display
                updateTimerDisplay(0);
            });

            Livewire.on('play-notification-sound', () => {
                // Play notification sound
                const audio = document.getElementById('notification-sound');
                if (audio) {
                    audio.currentTime = 0;
                    audio.play().catch(e => console.log('Audio play failed:', e));
                }
            });

            // Clean up on page leave
            document.addEventListener('livewire:before-navigate', () => {
                clearInterval(timerInterval);
            });

            // Also clean up when Livewire component is updated
            document.addEventListener('livewire:update', () => {
                // If timer was active but we lost the interval, restart it
                if (window.Livewire && window.Livewire.find('dashboard')) {
                    const component = window.Livewire.find('dashboard');
                    if (component.get('timerActive') && secondsRemaining > 0) {
                        clearInterval(timerInterval);
                        timerInterval = setInterval(() => {
                            secondsRemaining--;
                            updateTimerDisplay(secondsRemaining);
                            if (secondsRemaining <= 0) {
                                clearInterval(timerInterval);
                                Livewire.dispatch('timer-completed');
                            }
                        }, 1000);
                    }
                }
            });
        });
    </script>


    <!-- Notification Sound -->
    <audio id="notification-sound" src="https://assets.mixkit.co/sfx/preview/mixkit-alarm-digital-clock-beep-989.mp3"
        preload="auto"></audio>
</div>
