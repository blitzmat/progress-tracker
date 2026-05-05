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

    // Timer
    public bool $timerActive = false;
    public $timerDuration = 1;
    public $timerRemaining = 0;
    public $selectedActivityForTimer = null;
    public $timerNotes = '';

    // AlphaTab widget settings
    public bool $hasAlphaTab = false;
    public string $alphaTabTex = '';
    public int $alphaTabTempo = 120;
    public float $alphaTabVolume = 0.5;
    public string $alphaTabTimeSignature = '4/4';

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

        // Attach alphaTab widget if enabled
        if ($this->hasAlphaTab) {
            $entry->widgets()->create([
                'type'     => \App\Enums\WidgetType::AlphaTab,
                'label'    => 'AlphaTab Exercise',
                'settings' => [
                    'tex'           => $this->alphaTabTex,
                    'tempo'         => $this->alphaTabTempo,
                    'volume'        => $this->alphaTabVolume,
                    'time_signature' => $this->alphaTabTimeSignature,
                ],
                'position' => 0,
            ]);
        }

        $this->reset([
            'timerNotes', 'hasAlphaTab', 'alphaTabTex',
            'alphaTabTempo', 'alphaTabVolume', 'alphaTabTimeSignature',
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

    // Edit / update / delete methods unchanged (not shown for brevity)

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
                $alpha = $activity->getAlphaTabSetting('tex');
                $this->hasAlphaTab = !empty($alpha);
                if ($this->hasAlphaTab) {
                    $this->alphaTabTex = $alpha;
                    $this->alphaTabTempo = $activity->getAlphaTabSetting('default_tempo', 120);
                    $this->alphaTabVolume = $activity->getAlphaTabSetting('default_volume', 0.5);
                    $this->alphaTabTimeSignature = $activity->getAlphaTabSetting('default_time_signature', '4/4');
                }

                $parts = [];
                if ($activity->description) {
                    $parts[] = $activity->description;
                }
                if ($this->hasAlphaTab) {
                    $parts[] = "AlphaTab: {$this->alphaTabTempo} bpm, {$this->alphaTabTimeSignature}";
                }
                $this->selectedActivityDescription = $parts ? implode(' | ', $parts) : null;
            }
        } else {
            $this->selectedActivityDescription = null;
            $this->hasAlphaTab = false;
        }
    }
}
