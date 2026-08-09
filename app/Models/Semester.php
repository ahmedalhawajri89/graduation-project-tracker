<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    ################# relations
    public function projects()
    {
        return $this->hasMany(Project::class, 'semester_id', 'id');
    }
    #relation is one to many
    ################# end relations

    /**
     * الفصل الدراسي الحالي — المصدر الوحيد للحقيقة في كل النظام.
     * الفصل المفعّل يدوياً من الأدمن، وإن لم يوجد نرجع لآخر فصل (توافقاً مع السلوك القديم).
     */
    public static function current()
    {
        return static::where('is_active', true)->first() ?? static::latest()->first();
    }
}
