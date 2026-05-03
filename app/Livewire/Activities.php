<?php

namespace App\Livewire;

use App\Enums\WidgetType;
use App\Models\Activity;
use App\Models\Goal;
use App\Models\Widget;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

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

    public $managingWidgetsActivityId = null;   // ID of activity whose widgets we are viewing
    public $newWidgetType = '';
    public array $widgetSettings = [];
    public $editingWidgetId = null;
    public array $editSettings = [];
    public array $editingWidgetSettings = [];


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
            })
                                          ->orderBy('stage_id')
                                          ->orderBy('name')
                                          ->get(),
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
            'widget_settings' => $this->widgetSettings,
        ]);

        // Reset form fields
        $this->reset(['name', 'description', 'goal_id', 'widgetSettings']);

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
        $this->editingWidgetSettings = $activity->widget_settings ?? [];

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

        $activity->update([
            'name' => $this->editingName,
            'description' => $this->editingDescription,
            'goal_id' => $this->editingGoalId,
            'widget_settings' => $this->editingWidgetSettings,
        ]);

        $this->cancelEdit();
        session()->flash('message', 'Activity updated successfully!');
    }

    public function cancelEdit()
    {
        $this->reset(['editingActivityId', 'editingName', 'editingDescription', 'editingGoalId', 'editingWidgetSettings']);
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

        // Delete related entries (cascades to widgets)
        $activity->entries()->delete();

        $activity->delete();

        $this->confirmingDeletionId = null;
        session()->flash('message', 'Activity deleted successfully!');
    }

    /**
     * Open/close the widget management panel for a specific activity.
     */
    public function toggleManageWidgets(int $activityId): void
    {
        $activity = Activity::findOrFail($activityId);
        $this->authorizeActivityOwnership($activity);  // helper below

        // Toggle: if already managing this activity, close; otherwise open
        if ($this->managingWidgetsActivityId === $activityId) {
            $this->closeWidgetManagement();
        } else {
            $this->managingWidgetsActivityId = $activityId;
            $this->reset(['newWidgetType', 'editingWidgetId', 'editSettings']);
        }
    }

    public function closeWidgetManagement(): void
    {
        $this->managingWidgetsActivityId = null;
        $this->reset(['newWidgetType', 'editingWidgetId', 'editSettings']);
    }

    /**
     * Add a new widget of the selected type to the managed activity.
     */
    public function addWidget(): void
    {
        $activity = Activity::findOrFail($this->managingWidgetsActivityId);
        $this->authorizeActivityOwnership($activity);

        $type = WidgetType::tryFrom($this->newWidgetType);
        if (! $type) {
            session()->flash('error', 'Invalid widget type.');
            return;
        }

        $activity->widgets()->create([
            'type'     => $type,
            'label'    => $type->name,
            'settings' => [
                'tempo'  => 120,
                'sound'  => 'beep',
                'volume' => 0.5,    // default 50%
            ],
            'position' => $activity->widgets()->max('position') + 1,
        ]);

        $this->newWidgetType = '';
        session()->flash('message', 'Widget added.');
    }

    /**
     * Prepare a widget for editing its settings.
     */
    public function editWidget(int $widgetId): void
    {
        $widget = Widget::findOrFail($widgetId);
        $this->authorizeActivityOwnership($widget->activity); // ensure activity belongs to user

        $this->editingWidgetId   = $widget->id;
        $this->editSettings      = $widget->settings;
    }

    /**
     * Save widget settings.
     */
    public function updateWidget(): void
    {
        $widget = Widget::findOrFail($this->editingWidgetId);
        $this->authorizeActivityOwnership($widget->activity);

        $widget->update(['settings' => $this->editSettings]);

        $this->reset(['editingWidgetId', 'editSettings']);
        session()->flash('message', 'Widget settings updated.');
    }

    /**
     * Delete a widget.
     */
    public function deleteWidget(int $widgetId): void
    {
        $widget = Widget::findOrFail($widgetId);
        $this->authorizeActivityOwnership($widget->activity);

        $widget->delete();
        session()->flash('message', 'Widget removed.');
    }

    /**
     * Simple ownership guard – abort 403 if not owner.
     */
    protected function authorizeActivityOwnership(Activity $activity): void
    {
        if ($activity->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
