<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Activity;
use App\Models\Entry;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class Dashboard extends Component
{
    // Properties for editing an entry
    public $editingEntryId = null;
    public $editingEntryActivityId;
    public $editingEntryDuration;
    public $editingEntryNotes;
    public $editingEntryDate;

    // Property for delete confirmation
    public $confirmingDeletionId = null;

    // Timer properties
    public $timerActive = false;
    public $timerDuration = 1; // Default 1 minute
    public $timerRemaining = 0;
    public $timerInterval;
    public $selectedActivityForTimer = null;
    public $timerNotes = ''; // Added notes field for timer

    public function mount()
    {
        // No need for date initialization since we're using timer only
    }

    // Timer methods
    public function startTimer()
    {
        $this->validate([
            'selectedActivityForTimer' => 'required|exists:activities,id',
            'timerDuration' => 'required|integer|min:1|max:240', // Max 4 hours
            'timerNotes' => 'nullable|string|max:500',
        ]);

        $this->timerActive = true;
        $this->timerRemaining = $this->timerDuration * 60; // Convert minutes to seconds

        // Start the JavaScript timer
        $this->dispatch('start-timer', duration: $this->timerRemaining);
    }

    public function stopTimer()
    {
        $this->timerActive = false;
        $this->timerRemaining = 0;
        $this->dispatch('stop-timer');
    }


    // Log entry from timer
    public function logEntryFromTimer()
    {
        // Validate that we have the required data
        $this->validate([
            'selectedActivityForTimer' => 'required|exists:activities,id',
            'timerDuration' => 'required|integer|min:1',
        ]);

        // Create the entry first
        Auth::user()->entries()->create([
            'activity_id' => $this->selectedActivityForTimer,
            'duration' => $this->timerDuration,
            'notes' => $this->timerNotes,
            'date' => now()->format('Y-m-d'),
        ]);

        // Reset timer fields AFTER creating the entry
        $this->reset(['timerNotes']);
        $this->timerDuration = 1; // Reset to default
        // Don't reset selectedActivityForTimer here - keep it for potential reuse
    }

    // Also update the timerCompleted method to handle errors
    #[On('timer-completed')]
    public function timerCompleted()
    {
        $this->timerActive = false;

        try {
            // Auto-log the entry with notes
            $this->logEntryFromTimer();

            // Play notification sound
            $this->dispatch('play-notification-sound');

            session()->flash('message', 'Timer completed! Entry logged successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to log entry: ' . $e->getMessage());
        }
    }


    // Edit an existing entry
    public function editEntry($entryId)
    {
        $entry = Entry::findOrFail($entryId);

        // Check if the entry belongs to the current user
        if ($entry->user_id !== Auth::id()) {
            abort(403);
        }

        $this->editingEntryId = $entryId;
        $this->editingEntryActivityId = $entry->activity_id;
        $this->editingEntryDuration = $entry->duration;
        $this->editingEntryNotes = $entry->notes;
        $this->editingEntryDate = $entry->date->format('Y-m-d');
    }

    // Update an entry
    public function updateEntry()
    {
        $this->validate([
            'editingEntryActivityId' => 'required|exists:activities,id',
            'editingEntryDuration' => 'required|integer|min:1',
            'editingEntryNotes' => 'nullable|string',
            'editingEntryDate' => 'required|date',
        ]);

        $entry = Entry::findOrFail($this->editingEntryId);

        // Check if the entry belongs to the current user
        if ($entry->user_id !== Auth::id()) {
            abort(403);
        }

        $entry->update([
            'activity_id' => $this->editingEntryActivityId,
            'duration' => $this->editingEntryDuration,
            'notes' => $this->editingEntryNotes,
            'date' => $this->editingEntryDate,
        ]);

        $this->cancelEdit();
        session()->flash('message', 'Entry updated successfully!');
    }

    // Cancel editing
    public function cancelEdit()
    {
        $this->reset([
            'editingEntryId',
            'editingEntryActivityId',
            'editingEntryDuration',
            'editingEntryNotes',
            'editingEntryDate'
        ]);
    }

    // Confirm deletion
    public function confirmDelete($entryId)
    {
        $this->confirmingDeletionId = $entryId;
    }

    // Cancel deletion
    public function cancelDelete()
    {
        $this->confirmingDeletionId = null;
    }

    // Delete an entry
    public function deleteEntry()
    {
        $entry = Entry::findOrFail($this->confirmingDeletionId);

        // Check if the entry belongs to the current user
        if ($entry->user_id !== Auth::id()) {
            abort(403);
        }

        $entry->delete();

        $this->confirmingDeletionId = null;
        session()->flash('message', 'Entry deleted successfully!');
    }

    // This method renders the view and passes data to it
    public function render()
    {
        // Get the current user
        $user = Auth::user();

        // Data for the "Recent Activity" list - include time now
        $recentEntries = $user->entries()
                            ->with('activity.goal')
                            ->orderBy('created_at', 'desc') // Order by creation time
                            ->take(10)
                            ->get();

        // Data for the timer dropdown
        $activities = $user->activities()
                         ->with('goal')
                         ->orderBy('name')
                         ->get();

        return view('livewire.dashboard', [
            'recentEntries' => $recentEntries,
            'activities' => $activities,
        ])->layout('layouts.app');
    }
}

