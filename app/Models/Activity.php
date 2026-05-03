<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'goal_id',
        'name',
        'description',
        'widget_settings'
    ];

    /**
     * Get the user that owns the activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the goal that owns the activity.
     */
    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    /**
     * Get the entries for the activity.
     */
    public function entries()
    {
        return $this->hasMany(Entry::class);
    }

    /**
     * Get the widgets for the activity.
     */
    public function widgets(): HasMany
    {
        return $this->hasMany(Widget::class)->orderBy('position');
    }

    /**
     * Get all enabled widget types for this activity.
     */
    public function getEnabledWidgets(): array
    {
        return array_keys($this->widget_settings ?? []);
    }

    /**
     * Get settings for a specific widget type.
     */
    public function getWidgetSettings(string $type): array
    {
        return $this->widget_settings[$type] ?? [];
    }

    /**
     * Shortcut to check if a widget type is configured.
     */
    public function hasWidget(string $type): bool
    {
        return isset($this->widget_settings[$type]);
    }

    protected function casts(): array
    {
        return [
            'default_volume' => 'float',
            'widget_settings' => 'array',
        ];
    }


}

