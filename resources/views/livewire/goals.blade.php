<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Manage Goals</h1>

                <!-- Flash Messages -->
                @if (session()->has('message'))
                    <div
                        class="mb-6 px-4 py-3 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-lg">
                        {{ session('message') }}
                    </div>
                @endif

                <!-- Create Goal Form -->
                <div class="mb-8 p-6 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Create New Goal</h2>
                    <form wire:submit.prevent="create">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label for="name"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Goal Name</label>
                                <input wire:model="name" type="text" id="name" required
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 rounded-md dark:bg-gray-600 dark:text-white">
                                @error('name')
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
                                Create Goal
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Goals List -->
                <div>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Your Goals</h2>

                    @if ($goals->count() > 0)
                        <div class="space-y-4">
                            @foreach ($goals as $goal)
                                <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                                    @if ($editingGoalId === $goal->id)
                                        <!-- Edit Form -->
                                        <form wire:submit.prevent="update">
                                            <div class="grid grid-cols-1 gap-4">
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Goal
                                                        Name</label>
                                                    <input wire:model="editingName" type="text" required
                                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 rounded-md dark:bg-gray-600 dark:text-white">
                                                    @error('editingName')
                                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="mt-4">
                                                <label
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                                <textarea wire:model="editingDescription" rows="2"
                                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500 rounded-md dark:bg-gray-600 dark:text-white"></textarea>
                                                @error('editingDescription')
                                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="mt-4 flex space-x-2">
                                                <button type="submit"
                                                    class="px-3 py-1 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">
                                                    Save
                                                </button>
                                                <button type="button" wire:click="cancelEdit"
                                                    class="px-3 py-1 bg-gray-500 text-white rounded-md text-sm hover:bg-gray-600">
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    @else
                                        <!-- Display Mode -->
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <h3 class="font-medium text-gray-900 dark:text-gray-100 text-lg">
                                                    {{ $goal->name }}</h3>
                                                @if ($goal->description)
                                                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                                        {{ $goal->description }}</p>
                                                @endif
                                                <div
                                                    class="mt-3 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span>Created: {{ $goal->created_at->format('M j, Y') }}</span>
                                                </div>
                                                <div
                                                    class="mt-1 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                                        </path>
                                                    </svg>
                                                    <span>Activities:
                                                        {{ $goal->activities_count ?? $goal->activities()->count() }}</span>
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <button wire:click="edit({{ $goal->id }})"
                                                    class="px-3 py-1 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">
                                                    Edit
                                                </button>
                                                <button wire:click="confirmDelete({{ $goal->id }})"
                                                    class="px-3 py-1 bg-red-600 text-white rounded-md text-sm hover:bg-red-700">
                                                    Delete
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
                            <p class="text-gray-500 dark:text-gray-400 text-lg">No goals yet.</p>
                            <p class="text-gray-400 dark:text-gray-500">Create your first goal to get started!</p>
                        </div>
                    @endif
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
                Are you sure you want to delete this goal? All activities associated with this goal will also be
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
