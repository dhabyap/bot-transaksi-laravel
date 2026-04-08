<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tipe',
        'nominal',
        'kategori',
        'item',
        'timestamp', // Legacy datetime column
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'nominal' => 'double',
        'timestamp' => 'datetime',
    ];

    /**
     * Map the legacy timestamp column to created_at if necessary, 
     * or handle it manually.
     */
}
