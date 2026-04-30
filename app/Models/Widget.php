<?php

namespace App\Models;

use App\Enums\WidgetType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Widget extends Model
{
    protected $fillable = [
        'entry_id',   // now entry, not activity
        'type',
        'label',
        'settings',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'type' => WidgetType::class,
            'settings' => 'array',
        ];
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }
}
