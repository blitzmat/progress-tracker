<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}

