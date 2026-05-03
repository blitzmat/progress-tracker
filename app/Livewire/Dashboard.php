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
    public ?string $selectedActivityDescription = null;

    // Finger Warmup widget
    public bool $hasFingerWarmup = false;
    public string $fingerWarmupNoteType = 'quarter';
    public string $fingerWarmupPattern = '1-2-3-4';

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
    public ?string $widgetSummary = null;

    public function mount() {}

    public function startTimer()
    {
        $this->validate([
            'selectedActivityForTimer' => 'required|exists:activities,id',
            'timerDuration' => 'required|integer|min:1|max:240',
            'timerNotes' => 'nullable|string|max:500',
        ]);

        $this->timerActive = true;
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

        if ($this->hasFingerWarmup) {
            $entry->widgets()->create([
                'type'     => \App\Enums\WidgetType::FingerWarmup,
                'label'    => 'Finger Warm‑up',
                'settings' => [
                    'note_type' => $this->fingerWarmupNoteType,
                    'pattern'   => $this->fingerWarmupPattern,
                ],
                'position' => 1,
            ]);
        }

        $this->reset([
            'timerNotes', 'hasMetronome', 'widgetTempo', 'widgetSound', 'widgetVolume',
            'hasFingerWarmup', 'fingerWarmupNoteType', 'fingerWarmupPattern',
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

        $activities = Activity::with('goal')
            ->select('activities.*')
            ->join('goals', 'activities.goal_id', '=', 'goals.id')
            ->where(function ($q) use ($user) {
                $q->where('activities.user_id', $user->id)
                  ->orWhereNull('activities.user_id');
            })
            ->orderBy('goals.name')
            ->orderBy('activities.name')
            ->get();

        return view('livewire.dashboard', [
            'recentEntries' => $recentEntries,
            'activities'    => $activities,
            'selectedActivityDescription' => $this->selectedActivityDescription,
        ])->layout('layouts.app');
    }


    public function setActivity($value)
    {
        $this->selectedActivityForTimer = $value;

        if ($value) {
            $activity = Activity::find($value);
            if ($activity) {
                // --- Metronome ---
                $metro = $activity->getWidgetSettings('metronome');
                $this->hasMetronome = !empty($metro['tempo']);
                if ($this->hasMetronome) {
                    $this->widgetTempo = $metro['tempo'];
                    $this->widgetSound = $metro['sound'] ?? 'beep';
                    $this->widgetVolume = $metro['volume'] ?? 0.5;
                    $this->widgetTimeSignature = $metro['time_signature'] ?? '4/4';
                } else {
                    // reset to defaults if no metronome
                    $this->widgetTempo = 120;
                    $this->widgetSound = 'beep';
                    $this->widgetVolume = 0.5;
                    $this->widgetTimeSignature = '4/4';
                }

                // --- Finger Warm‑up ---
                $finger = $activity->getWidgetSettings('finger_warmup');
                $this->hasFingerWarmup = !empty($finger['note_type']);
                if ($this->hasFingerWarmup) {
                    $this->fingerWarmupNoteType = $finger['note_type'];
                    $this->fingerWarmupPattern = $finger['pattern'] ?? '1-2-3-4';
                } else {
                    $this->fingerWarmupNoteType = 'quarter';
                    $this->fingerWarmupPattern = '1-2-3-4';
                }

                // --- Description for the UI ---
                $parts = [];
                if ($activity->description) {
                    $parts[] = $activity->description;
                }
                if ($this->hasMetronome) {
                    $parts[] = "Metronome: {$metro['tempo']} bpm, {$metro['time_signature']}";
                }
                if ($this->hasFingerWarmup) {
                    $parts[] = "Finger: {$finger['pattern']} ({$finger['note_type']})";
                }
                $this->selectedActivityDescription = $parts ? implode(' | ', $parts) : null;
            }
        } else {
            $this->selectedActivityDescription = null;
            $this->hasMetronome = false;
            $this->hasFingerWarmup = false;
        }
    }
}
