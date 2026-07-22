<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsernameHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'old_username',
        'new_username',
        'actor_type',
        'actor_id',
        'source',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
