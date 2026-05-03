<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Manage Activities</h1>

                    @if (session()->has('message'))
                        <div
                            class="mb-6 px-4 py-3 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-lg">
                            {{ session('message') }}
                        </div>
                    @endif

                    <!-- Create Activity Form -->
                    <div class="mb-8 p-6 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Create New Activity</h2>
                        <form wire:submit.prevent="create">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="name"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Activity
                                        Name</label>
                                    <input wire:model="name" type="text" id="name" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 rounded-md dark:bg-gray-600 dark:text-white">
                                    @error('name')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label for="goal_id"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Associated
                                        Goal</label>
                                    <select wire:model="goal_id" id="goal_id" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 rounded-md dark:bg-gray-600 dark:text-white">
                                        <option value="">Select a Goal</option>
                                        @foreach ($goals as $goal)
                                            <option value="{{ $goal->id }}">{{ $goal->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('goal_id')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="mt-4">
                                <label for="description"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description
                                    (Optional)</label>
                                <textarea wire:model="description" id="description" rows="3"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 rounded-md dark:bg-gray-600 dark:text-white"></textarea>
                                @error('description')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            {{-- Widget Settings (create form) --}}
                            <div x-data="activityWidgetSettings($wire, false)">
                                <div class="mt-4 border-t pt-4">
                                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Widget
                                        Settings</h3>

                                    <!-- Metronome -->
                                    <div class="flex items-center space-x-3 mb-3">
                                        <input id="widget-metronome" type="checkbox" @change="toggleMetronome()"
                                            :checked="metronome?.tempo"
                                            class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-600">
                                        <label for="widget-metronome"
                                            class="text-sm text-gray-700 dark:text-gray-300">Enable Metronome</label>
                                    </div>
                                    <template x-if="metronome">
                                        <div
                                            class="pl-4 border-l-2 border-blue-300 dark:border-blue-500 space-y-3 mb-4">
                                            <div class="flex items-center space-x-2">
                                                <label class="text-sm text-gray-700 dark:text-gray-300 w-16">BPM</label>
                                                <input type="number" x-model.number="metronome.tempo" min="40"
                                                    max="240" @input="updateWidgets()"
                                                    class="w-20 border rounded px-2 py-1 dark:bg-gray-600 dark:text-white text-sm">
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <label
                                                    class="text-sm text-gray-700 dark:text-gray-300 w-16">Sound</label>
                                                <select x-model="metronome.sound" @change="updateWidgets()"
                                                    class="border rounded px-2 py-1 dark:bg-gray-600 dark:text-white text-sm">
                                                    <option value="beep">Beep</option>
                                                    <option value="click">Click</option>
                                                    <option value="woodblock">Wood</option>
                                                    <option value="pulse">Pulse</option>
                                                </select>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <label class="text-sm text-gray-700 dark:text-gray-300 w-16">Time
                                                    Sig.</label>
                                                <select x-model="metronome.time_signature" @change="updateWidgets()"
                                                    class="border rounded px-2 py-1 dark:bg-gray-600 dark:text-white text-sm">
                                                    <option value="2/4">2/4</option>
                                                    <option value="3/4">3/4</option>
                                                    <option value="4/4">4/4</option>
                                                    <option value="6/8">6/8</option>
                                                </select>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <label
                                                    class="text-sm text-gray-700 dark:text-gray-300 w-16">Volume</label>
                                                <input type="range" x-model.number="metronome.volume" min="0"
                                                    max="1" step="0.01" @input="updateWidgets()"
                                                    class="w-full h-2 bg-gray-200 rounded-lg dark:bg-gray-600">
                                                <span class="text-xs w-10"
                                                    x-text="Math.round(metronome.volume * 100) + '%'"></span>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Finger Warm-up -->
                                    <div class="flex items-center space-x-3 mb-3">
                                        <input id="widget-finger-warmups" type="checkbox" @change="toggleFingerWarmup()"
                                            :checked="finger?.note_type"
                                            class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-600">
                                        <label for="widget-finger-warmups"
                                            class="text-sm text-gray-700 dark:text-gray-300">Enable Finger
                                            Warm‑up</label>
                                    </div>
                                    <template x-if="finger">
                                        <div class="pl-4 border-l-2 border-green-300 dark:border-green-500 space-y-3">
                                            <div class="flex items-center space-x-2">
                                                <label class="text-sm text-gray-700 dark:text-gray-300 w-20">Note
                                                    Type</label>
                                                <select x-model="finger.note_type" @change="updateWidgets()"
                                                    class="border rounded px-2 py-1 dark:bg-gray-600 dark:text-white text-sm">
                                                    <option value="quarter">Quarter</option>
                                                    <option value="eighth">Eighth</option>
                                                    <option value="sixteenth">16th</option>
                                                    <option value="thirty-second">32nd</option>
                                                </select>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <label
                                                    class="text-sm text-gray-700 dark:text-gray-300 w-20">Pattern</label>
                                                <select x-model="finger.pattern" @change="updateWidgets()"
                                                    class="border rounded px-2 py-1 dark:bg-gray-600 dark:text-white text-sm">
                                                    <option value="1-2-3-4">1‑2‑3‑4</option>
                                                    <option value="1-4-2-3">1‑4‑2‑3</option>
                                                    <option value="4-3-2-1">4‑3‑2‑1</option>
                                                    <option value="1-3-2-4">1‑3‑2‑4</option>
                                                    <option value="2-4-1-3">2‑4‑1‑3</option>
                                                </select>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    Create Activity
                                </button>
                            </div>
                        </form>
                    </div>

                    @php
                        $systemActivities = $activities->whereNull('user_id');
                        $myActivities = $activities->where('user_id', Auth::id());
                        $hasMyActivities = $myActivities->isNotEmpty();
                        $hasSystemActivities = $systemActivities->isNotEmpty();
                        $defaultTab = $hasMyActivities ? 'my' : 'system';
                    @endphp

                    @if ($hasMyActivities || $hasSystemActivities)
                        <div x-data="{ activeTab: '{{ $defaultTab }}' }" class="mb-4">
                            <!-- Tab buttons -->
                            <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                                <nav class="flex space-x-4" role="tablist">
                                    @if ($hasMyActivities)
                                        <button @click="activeTab = 'my'"
                                            :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'my', 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300': activeTab !== 'my' }"
                                            class="px-4 py-2 text-sm font-medium border-b-2 focus:outline-none"
                                            role="tab">
                                            My Activities
                                        </button>
                                    @endif
                                    @if ($hasSystemActivities)
                                        <button @click="activeTab = 'system'"
                                            :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'system', 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300': activeTab !== 'system' }"
                                            class="px-4 py-2 text-sm font-medium border-b-2 focus:outline-none"
                                            role="tab">
                                            System Activities
                                        </button>
                                    @endif
                                </nav>
                            </div>

                            <!-- My Activities Tab -->
                            @if ($hasMyActivities)
                                <div x-show="activeTab === 'my'" x-cloak>
                                    <div class="space-y-4">
                                        @foreach ($myActivities as $activity)
                                            <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                                                @if ($editingActivityId === $activity->id)
                                                    <form wire:submit.prevent="update">
                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                                                <input wire:model="editingName" type="text"
                                                                    required
                                                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 rounded-md dark:bg-gray-600 dark:text-white">
                                                                @error('editingName')
                                                                    <span
                                                                        class="text-red-500 text-sm">{{ $message }}</span>
                                                                @enderror
                                                            </div>
                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Goal</label>
                                                                <select wire:model="editingGoalId" required
                                                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 rounded-md dark:bg-gray-600 dark:text-white">
                                                                    @foreach ($goals as $goal)
                                                                        <option value="{{ $goal->id }}"
                                                                            {{ $editingGoalId == $goal->id ? 'selected' : '' }}>
                                                                            {{ $goal->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @error('editingGoalId')
                                                                    <span
                                                                        class="text-red-500 text-sm">{{ $message }}</span>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="mt-4">
                                                            <label
                                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                                            <textarea wire:model="editingDescription" rows="2"
                                                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 rounded-md dark:bg-gray-600 dark:text-white"></textarea>
                                                            @error('editingDescription')
                                                                <span
                                                                    class="text-red-500 text-sm">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                        {{-- Widget Settings (edit form) --}}
                                                        <div x-data="activityWidgetSettings($wire, true)">
                                                            <div class="mt-4 border-t pt-4">
                                                                <h3
                                                                    class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                                                    Widget Settings</h3>

                                                                <!-- Metronome -->
                                                                <div class="flex items-center space-x-3 mb-3">
                                                                    <input type="checkbox" @change="toggleMetronome()"
                                                                        :checked="metronome?.tempo"
                                                                        class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-600">
                                                                    <label
                                                                        class="text-sm text-gray-700 dark:text-gray-300">Enable
                                                                        Metronome</label>
                                                                </div>
                                                                <template x-if="metronome">
                                                                    <div
                                                                        class="pl-4 border-l-2 border-blue-300 dark:border-blue-500 space-y-3 mb-4">
                                                                        <!-- fields identical to create form -->
                                                                    </div>
                                                                </template>

                                                                <!-- Finger Warm-up -->
                                                                <div class="flex items-center space-x-3 mb-3">
                                                                    <input type="checkbox"
                                                                        @change="toggleFingerWarmup()"
                                                                        :checked="finger?.note_type"
                                                                        class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-600">
                                                                    <label
                                                                        class="text-sm text-gray-700 dark:text-gray-300">Enable
                                                                        Finger Warm‑up</label>
                                                                </div>
                                                                <template x-if="finger">
                                                                    <div
                                                                        class="pl-4 border-l-2 border-green-300 dark:border-green-500 space-y-3">
                                                                        <!-- fields identical to create form -->
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <div class="mt-4 flex space-x-2">
                                                            <button type="submit"
                                                                class="px-3 py-1 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">Save</button>
                                                            <button type="button" wire:click="cancelEdit"
                                                                class="px-3 py-1 bg-gray-500 text-white rounded-md text-sm hover:bg-gray-600">Cancel</button>
                                                        </div>
                                                    </form>
                                                @else
                                                    <div class="flex justify-between items-start">
                                                        <div class="flex-1">
                                                            <h3
                                                                class="font-medium text-gray-900 dark:text-gray-100 text-lg">
                                                                {{ $activity->name }}</h3>
                                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                                <span class="font-medium">Goal:</span>
                                                                {{ $activity->goal->name }}
                                                            </p>
                                                            @if ($activity->description)
                                                                <p
                                                                    class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                                                    {{ $activity->description }}</p>
                                                            @endif
                                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                                                Created: {{ $activity->created_at->format('M j, Y') }}
                                                            </p>
                                                        </div>
                                                        <div class="flex space-x-2">
                                                            <button wire:click="edit({{ $activity->id }})"
                                                                class="px-3 py-1 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">
                                                                Edit
                                                            </button>
                                                            <button wire:click="confirmDelete({{ $activity->id }})"
                                                                class="px-3 py-1 bg-red-600 text-white rounded-md text-sm hover:bg-red-700">
                                                                Delete
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- System Activities Tab -->
                            @if ($hasSystemActivities)
                                <div x-show="activeTab === 'system'" x-cloak>
                                    <div class="space-y-4">
                                        @foreach ($systemActivities as $activity)
                                            <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                                                <div class="flex justify-between items-start">
                                                    <div class="flex-1">
                                                        <h3
                                                            class="font-medium text-gray-900 dark:text-gray-100 text-lg">
                                                            {{ $activity->name }}</h3>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                            <span class="font-medium">Goal:</span>
                                                            {{ $activity->goal->name }}
                                                        </p>
                                                        @if ($activity->description)
                                                            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                                                {{ $activity->description }}</p>
                                                        @endif
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                                            Created: {{ $activity->created_at->format('M j, Y') }}
                                                        </p>
                                                    </div>
                                                    {{-- No edit/delete buttons for system items --}}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="text-gray-400 dark:text-gray-500 mb-4">
                                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">No activities yet.</p>
                            <p class="text-gray-400 dark:text-gray-500">Create your first activity using the form
                                above!</p>
                        </div>
                    @endif
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
                    Are you sure you want to delete this activity? Any entries associated with this activity will also
                    be deleted. This action cannot be undone.
                </p>
                <div class="flex justify-end space-x-3">
                    <button wire:click="cancelDelete"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button wire:click="delete"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
