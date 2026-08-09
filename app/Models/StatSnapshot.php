<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatSnapshot extends Model
{
    use HasFactory;

    protected $table = 'stat_snapshots';

    protected $fillable = [
        'date',
        'students',
        'supervisors',
        'groups',
        'messages',
        'has_group',
        'not_has_group',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
