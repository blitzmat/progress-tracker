<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Manage Activities</h1>

                <!-- Flash Messages -->
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

                        <div class="mt-4">
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                Create Activity
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Activities List -->
                <div>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Your Activities</h2>

                    @if ($activities->count() > 0)
                        <div class="space-y-4">
                            @foreach ($activities as $activity)
                                <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                                    @if ($editingActivityId === $activity->id)
                                        <!-- Edit Form (unchanged) -->
                                        {{-- ... your existing edit form ... --}}
                                    @else
                                        <!-- Display Mode -->
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <h3 class="font-medium text-gray-900 dark:text-gray-100 text-lg">
                                                    {{ $activity->name }}</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                    <span class="font-medium">Goal:</span> {{ $activity->goal->name }}
                                                </p>
                                                @if ($activity->description)
                                                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
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
                                                {{-- [NEW] Widgets button --}}
                                                <button wire:click="toggleManageWidgets({{ $activity->id }})"
                                                    class="px-3 py-1 bg-purple-600 text-white rounded-md text-sm hover:bg-purple-700">
                                                    Widgets
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- [NEW] Widget Management Panel --}}
                                    @if ($managingWidgetsActivityId === $activity->id)
                                        <div class="mt-4 border-t pt-4 border-gray-200 dark:border-gray-600">
                                            <div class="flex justify-between items-center mb-3">
                                                <h4 class="font-medium text-gray-800 dark:text-gray-200">Widgets for
                                                    {{ $activity->name }}</h4>
                                                <button wire:click="closeWidgetManagement"
                                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                                    &times; Close
                                                </button>
                                            </div>

                                            {{-- List of current widgets --}}
                                            @php $activityWidgets = $activity->widgets; @endphp
                                            @if ($activityWidgets->count() > 0)
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                                                    @foreach ($activityWidgets as $widget)
                                                        <div
                                                            class="p-3 bg-gray-50 dark:bg-gray-700 rounded flex justify-between items-start">
                                                            <div>
                                                                <span class="font-medium">{{ $widget->label }}</span>
                                                                <span
                                                                    class="ml-2 text-xs text-gray-500 dark:text-gray-400">{{ $widget->type->name }}</span>
                                                                @if (!empty($widget->settings))
                                                                    <pre class="text-xs mt-1 text-gray-500">{{ json_encode($widget->settings) }}</pre>
                                                                @endif
                                                            </div>
                                                            <div class="flex space-x-1">
                                                                <button wire:click="editWidget({{ $widget->id }})"
                                                                    class="text-blue-500 text-sm hover:underline">Edit</button>
                                                                <button wire:click="deleteWidget({{ $widget->id }})"
                                                                    class="text-red-500 text-sm hover:underline">Delete</button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">No widgets yet.
                                                    Add one below.</p>
                                            @endif

                                            {{-- Add widget form --}}
                                            <div class="flex items-center space-x-2">
                                                <select wire:model="newWidgetType"
                                                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md text-sm py-1 px-2">
                                                    <option value="">Select type</option>
                                                    @foreach ($widgetTypes as $type)
                                                        <option value="{{ $type->value }}">{{ $type->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button wire:click="addWidget"
                                                    class="px-3 py-1 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">
                                                    Add
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
                                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">No activities yet.</p>
                            <p class="text-gray-400 dark:text-gray-500">Create your first activity using the form above!
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if ($editingWidgetId)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl max-w-md w-full">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Edit Widget Settings</h3>

            {{-- Example: Metronome tempo input. Customize per widget type as needed --}}
            @php $widget = \App\Models\Widget::find($editingWidgetId) @endphp
            @switch($widget->type->value)
                @case('metronome')
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tempo (BPM)</label>
                    <input type="number" wire:model="editSettings.tempo"
                        class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
                        min="40" max="240" placeholder="120">

                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-4">Sound</label>
                    <select wire:model="editSettings.sound"
                        class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                        <option value="beep">Classic Beep</option>
                        <option value="click">Digital Click</option>
                        <option value="woodblock">Woodblock</option>
                        <option value="pulse">Pulse</option>
                    </select>
                @break

                @default
                    <textarea wire:model="editSettings" rows="4"
                        class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
                        placeholder='{"key": "value"}'></textarea>
            @endswitch

            <div class="flex justify-end space-x-3 mt-6">
                <button wire:click="$set('editingWidgetId', null)"
                    class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Cancel</button>
                <button wire:click="updateWidget"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Save</button>
            </div>
        </div>
    </div>
@endif

<!-- Delete Confirmation Modal -->
@if ($confirmingDeletionId)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl max-w-md w-full">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Confirm Deletion</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Are you sure you want to delete this activity? Any entries associated with this activity will also be
                deleted. This action cannot be undone.
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
