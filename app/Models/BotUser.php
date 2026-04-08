<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotUser extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'username',
        'language_code',
        'first_seen',
        'last_active',
        'message_count',
    ];

    protected $casts = [
        'first_seen' => 'datetime',
        'last_active' => 'datetime',
    ];
}
