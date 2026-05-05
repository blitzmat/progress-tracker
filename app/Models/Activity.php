<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'goal_id',
        'name',
        'description',
        'widget_settings',
    ];

    protected function casts(): array
    {
        return [
            'widget_settings' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    public function entries()
    {
        return $this->hasMany(Entry::class);
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(Widget::class)->orderBy('position');
    }

    /**
     * Check if an alphaTab exercise is configured.
     */
    public function hasAlphaTab(): bool
    {
        return isset($this->widget_settings['alpha_tab']['tex']);
    }

    /**
     * Get a specific alphaTab setting.
     */
    public function getAlphaTabSetting(string $key, mixed $default = null): mixed
    {
        return $this->widget_settings['alpha_tab'][$key] ?? $default;
    }
}
