<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Activity;
use App\Models\Goal;
use Illuminate\Support\Facades\Auth;

class Activities extends Component
{
    // Properties for form
    public $name;
    public $description;
    public $goal_id;

    // Properties for editing
    public $editingActivityId = null;
    public $editingName;
    public $editingDescription;
    public $editingGoalId;

    // Property for delete confirmation
    public $confirmingDeletionId = null;

    // Validation rules
    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'goal_id' => 'required|exists:goals,id',
    ];

    public function render()
    {
        $user = Auth::user();

        return view('livewire.activities', [
            'activities' => $user->activities()->with('goal')->get(),
            'goals' => $user->goals()->orderBy('name')->get(),
        ])->layout('layouts.app');
    }

    // CREATE METHOD - This was missing!
    public function create()
    {
        $this->validate();

        Auth::user()->activities()->create([
            'name' => $this->name,
            'description' => $this->description,
            'goal_id' => $this->goal_id,
        ]);

        // Reset form fields
        $this->reset(['name', 'description', 'goal_id']);

        session()->flash('message', 'Activity created successfully!');
    }

    public function edit($activityId)
    {
        $activity = Activity::findOrFail($activityId);

        // Check if the activity belongs to the current user
        if ($activity->user_id !== Auth::id()) {
            abort(403);
        }

        $this->editingActivityId = $activityId;
        $this->editingName = $activity->name;
        $this->editingDescription = $activity->description;
        $this->editingGoalId = $activity->goal_id;
    }

    public function update()
    {
        $this->validate([
            'editingName' => 'required|string|max:255',
            'editingDescription' => 'nullable|string',
            'editingGoalId' => 'required|exists:goals,id',
        ]);

        $activity = Activity::findOrFail($this->editingActivityId);

        // Check if the activity belongs to the current user
        if ($activity->user_id !== Auth::id()) {
            abort(403);
        }

        $activity->update([
            'name' => $this->editingName,
            'description' => $this->editingDescription,
            'goal_id' => $this->editingGoalId,
        ]);

        $this->cancelEdit();
        session()->flash('message', 'Activity updated successfully!');
    }

    public function cancelEdit()
    {
        $this->reset(['editingActivityId', 'editingName', 'editingDescription', 'editingGoalId']);
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

        // Check if the activity belongs to the current user
        if ($activity->user_id !== Auth::id()) {
            abort(403);
        }

        $activity->delete();

        $this->confirmingDeletionId = null;
        session()->flash('message', 'Activity deleted successfully!');
    }
}
