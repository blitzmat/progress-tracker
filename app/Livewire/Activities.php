<?php

namespace App\Livewire;

use App\Models\Activity;
use App\Models\Goal;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Activities extends Component
{
    public $name;
    public $description;
    public $goal_id;

    public $editingActivityId = null;
    public $editingName;
    public $editingDescription;
    public $editingGoalId;

    public $confirmingDeletionId = null;

    // AlphaTab settings
    public bool $enableAlphaTab = false;
    public string $alphaTabTex = '';
    public int $alphaTabTempo = 120;
    public float $alphaTabVolume = 0.5;
    public string $alphaTabTimeSignature = '4/4';

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'goal_id' => 'required|exists:goals,id',
    ];

    public function render()
    {
        $user = Auth::user();

        return view('livewire.activities', [
            'activities' => Activity::with('goal')
                ->select('activities.*')
                ->join('goals', 'activities.goal_id', '=', 'goals.id')
                ->where(function ($q) use ($user) {
                    $q->where('activities.user_id', $user->id)
                      ->orWhereNull('activities.user_id');
                })
                ->orderBy('goals.name')
                ->orderBy('activities.name')
                ->get(),

            'goals' => Goal::with('stage')->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereNull('user_id');
            })->orderBy('stage_id')->orderBy('name')->get(),
        ])->layout('layouts.app');
    }

    public function create()
    {
        $this->validate();

        $widgetSettings = [];
        if ($this->enableAlphaTab) {
            $widgetSettings['alpha_tab'] = [
                'tex' => $this->alphaTabTex,
                'default_tempo' => $this->alphaTabTempo,
                'default_volume' => $this->alphaTabVolume,
                'default_time_signature' => $this->alphaTabTimeSignature,
            ];
        }

        Auth::user()->activities()->create([
            'name' => $this->name,
            'description' => $this->description,
            'goal_id' => $this->goal_id,
            'widget_settings' => $widgetSettings,
        ]);

        $this->reset(['name', 'description', 'goal_id', 'enableAlphaTab', 'alphaTabTex', 'alphaTabTempo', 'alphaTabVolume', 'alphaTabTimeSignature']);
        session()->flash('message', 'Activity created successfully!');
    }

    public function edit($activityId)
    {
        $activity = Activity::findOrFail($activityId);
        if ($activity->user_id !== Auth::id()) {
            abort(403);
        }

        $this->editingActivityId = $activityId;
        $this->editingName = $activity->name;
        $this->editingDescription = $activity->description;
        $this->editingGoalId = $activity->goal_id;

        $this->enableAlphaTab = $activity->hasAlphaTab();
        $this->alphaTabTex = $activity->getAlphaTabSetting('tex', '');
        $this->alphaTabTempo = $activity->getAlphaTabSetting('default_tempo', 120);
        $this->alphaTabVolume = $activity->getAlphaTabSetting('default_volume', 0.5);
        $this->alphaTabTimeSignature = $activity->getAlphaTabSetting('default_time_signature', '4/4');
    }

    public function update()
    {
        $this->validate([
            'editingName' => 'required|string|max:255',
            'editingDescription' => 'nullable|string',
            'editingGoalId' => 'required|exists:goals,id',
        ]);

        $activity = Activity::findOrFail($this->editingActivityId);
        if ($activity->user_id !== Auth::id()) {
            abort(403);
        }

        $widgetSettings = [];
        if ($this->enableAlphaTab) {
            $widgetSettings['alpha_tab'] = [
                'tex' => $this->alphaTabTex,
                'default_tempo' => $this->alphaTabTempo,
                'default_volume' => $this->alphaTabVolume,
                'default_time_signature' => $this->alphaTabTimeSignature,
            ];
        }

        $activity->update([
            'name' => $this->editingName,
            'description' => $this->editingDescription,
            'goal_id' => $this->editingGoalId,
            'widget_settings' => $widgetSettings,
        ]);

        $this->cancelEdit();
        session()->flash('message', 'Activity updated successfully!');
    }

    public function cancelEdit()
    {
        $this->reset(['editingActivityId', 'editingName', 'editingDescription', 'editingGoalId',
                      'enableAlphaTab', 'alphaTabTex', 'alphaTabTempo', 'alphaTabVolume', 'alphaTabTimeSignature']);
    }

    public function confirmDelete($activityId)
    {
        $this->confirmingDeletionId = $activityId;
    }

    public function cancelDelete()
    {
        $this->confirmingDeletionId = null;
    }

    public function delete()
    {
        $activity = Activity::findOrFail($this->confirmingDeletionId);
        if ($activity->user_id !== Auth::id()) {
            abort(403);
        }
        $activity->entries()->delete();
        $activity->delete();
        $this->confirmingDeletionId = null;
        session()->flash('message', 'Activity deleted successfully!');
    }
}
