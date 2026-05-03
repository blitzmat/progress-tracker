<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Goal;
use Illuminate\Support\Facades\Auth;

class Goals extends Component
{
    // Properties for form
    public $name;
    public $description;

    // Properties for editing
    public $editingGoalId = null;
    public $editingName;
    public $editingDescription;

    // Property for delete confirmation
    public $confirmingDeletionId = null;

    // Validation rules
    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
    ];

    public function render()
    {
        $user = Auth::user();

        $goals = Goal::with('activities')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereNull('user_id');
            })
            ->orderBy('name')
            ->get();

        return view('livewire.goals', [
            'goals' => $goals,
        ])->layout('layouts.app');
    }

    public function create()
    {
        $this->validate();

        Auth::user()->goals()->create([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        // Reset form fields
        $this->reset(['name', 'description']);

        session()->flash('message', 'Goal created successfully!');
    }

    public function edit($goalId)
    {
        $goal = Goal::findOrFail($goalId);

        // Check if the goal belongs to the current user
        if ($goal->user_id !== Auth::id()) {
            abort(403);
        }

        $this->editingGoalId = $goalId;
        $this->editingName = $goal->name;
        $this->editingDescription = $goal->description;
    }

    public function update()
    {
        $this->validate([
            'editingName' => 'required|string|max:255',
            'editingDescription' => 'nullable|string',
        ]);

        $goal = Goal::findOrFail($this->editingGoalId);

        // Check if the goal belongs to the current user
        if ($goal->user_id !== Auth::id()) {
            abort(403);
        }

        $goal->update([
            'name' => $this->editingName,
            'description' => $this->editingDescription,
        ]);

        $this->cancelEdit();
        session()->flash('message', 'Goal updated successfully!');
    }

    public function cancelEdit()
    {
        $this->reset(['editingGoalId', 'editingName', 'editingDescription']);
    }

    public function confirmDelete($goalId)
    {
        $this->confirmingDeletionId = $goalId;
    }

    public function cancelDelete()
    {
        $this->confirmingDeletionId = null;
    }

    public function delete()
    {
        $goal = Goal::findOrFail($this->confirmingDeletionId);

        // Check if the goal belongs to the current user
        if ($goal->user_id !== Auth::id()) {
            abort(403);
        }

        $goal->delete();

        $this->confirmingDeletionId = null;
        session()->flash('message', 'Goal deleted successfully!');
    }
}

