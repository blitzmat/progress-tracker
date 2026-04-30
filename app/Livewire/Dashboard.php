<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Activity;
use App\Models\Entry;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class Dashboard extends Component
{
    public $editingEntryId = null;
    public $editingEntryActivityId;
    public $editingEntryDuration;
    public $editingEntryNotes;
    public $editingEntryDate;
    public $confirmingDeletionId = null;

    // Timer
    public bool $timerActive = false;          // ← restored
    public $timerDuration = 1;
    public $timerRemaining = 0;
    public $selectedActivityForTimer = null;
    public $timerNotes = '';

    // Metronome
    public bool $hasMetronome = false;
    public int $widgetTempo = 120;
    public string $widgetSound = 'beep';
    public float $widgetVolume = 0.5;
    public string $widgetTimeSignature = '4/4';

    public function mount() {}

    public function startTimer()
    {
        $this->validate([
            'selectedActivityForTimer' => 'required|exists:activities,id',
            'timerDuration' => 'required|integer|min:1|max:240',
            'timerNotes' => 'nullable|string|max:500',
        ]);

        $this->timerActive = true;
        $this->timerRemaining = $this->timerDuration * 60;
        $this->dispatch('start-timer', duration: $this->timerRemaining);
    }

    public function stopTimer()
    {
        $this->timerActive = false;
        $this->timerRemaining = 0;
        $this->dispatch('stop-timer');
    }

    public function logEntryFromTimer()
    {
        $this->validate([
            'selectedActivityForTimer' => 'required|exists:activities,id',
            'timerDuration' => 'required|integer|min:1',
        ]);

        $entry = Auth::user()->entries()->create([
            'activity_id' => $this->selectedActivityForTimer,
            'duration'    => $this->timerDuration,
            'notes'       => $this->timerNotes,
            'date'        => now()->format('Y-m-d'),
        ]);

        if ($this->hasMetronome) {
            $entry->widgets()->create([
                'type'     => \App\Enums\WidgetType::Metronome,
                'label'    => 'Metronome',
                'settings' => [
                    'tempo'         => $this->widgetTempo,
                    'sound'         => $this->widgetSound,
                    'volume'        => $this->widgetVolume,
                    'timeSignature' => $this->widgetTimeSignature,
                ],
                'position' => 0,
            ]);
        }

        $this->reset([
            'timerNotes', 'hasMetronome', 'widgetTempo', 'widgetSound', 'widgetVolume',
        ]);
        $this->timerDuration = 1;
    }

    #[On('timer-completed')]
    public function timerCompleted()
    {
        $this->timerActive = false;
        try {
            $this->logEntryFromTimer();
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

        if ($entry->user_id !== Auth::id()) {
            abort(403);
        }

        $this->editingEntryId = $entryId;
        $this->editingEntryActivityId = $entry->activity_id;
        $this->editingEntryDuration = $entry->duration;
        $this->editingEntryNotes = $entry->notes;
        $this->editingEntryDate = $entry->date->format('Y-m-d');
    }

    public function updateEntry()
    {
        $this->validate([
            'editingEntryActivityId' => 'required|exists:activities,id',
            'editingEntryDuration' => 'required|integer|min:1',
            'editingEntryNotes' => 'nullable|string',
            'editingEntryDate' => 'required|date',
        ]);

        $entry = Entry::findOrFail($this->editingEntryId);

        if ($entry->user_id !== Auth::id()) {
            abort(403);
        }

        $entry->update([
            'activity_id' => $this->editingEntryActivityId,
            'duration'    => $this->editingEntryDuration,
            'notes'       => $this->editingEntryNotes,
            'date'        => $this->editingEntryDate,
        ]);

        $this->cancelEdit();
        session()->flash('message', 'Entry updated successfully!');
    }

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

    public function confirmDelete($entryId)
    {
        $this->confirmingDeletionId = $entryId;
    }

    public function cancelDelete()
    {
        $this->confirmingDeletionId = null;
    }

    public function deleteEntry()
    {
        $entry = Entry::findOrFail($this->confirmingDeletionId);

        if ($entry->user_id !== Auth::id()) {
            abort(403);
        }

        $entry->delete();

        $this->confirmingDeletionId = null;
        session()->flash('message', 'Entry deleted successfully!');
    }

    public function render()
    {
        $user = Auth::user();
        $recentEntries = $user->entries()
            ->with('activity.goal', 'widgets')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        $activities = $user->activities()->with('goal')->orderBy('name')->get();

        return view('livewire.dashboard', [
            'recentEntries' => $recentEntries,
            'activities'    => $activities,
        ])->layout('layouts.app');
    }
}
