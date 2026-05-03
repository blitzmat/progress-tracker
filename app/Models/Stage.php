<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stage extends Model
{
    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }
}
