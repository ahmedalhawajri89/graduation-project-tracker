<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** قاعة مناقشة تُدار في المنصّة — بها يُمنع حجز قاعة واحدة لمناقشتين متداخلتين */
class DefenseRoom extends Model
{
    protected $fillable = ['name', 'location', 'capacity', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
    ];

    public function defenses()
    {
        return $this->hasMany(Defense::class, 'room_id');
    }
}
