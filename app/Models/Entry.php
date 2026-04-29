<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Entry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'activity_id',
        'date',
        'duration',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date', // This converts the string to a Carbon instance
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the entry.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the activity that owns the entry.
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}

