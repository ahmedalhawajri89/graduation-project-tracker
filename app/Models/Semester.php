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

    /**
     * الاسم يُكتب نصاً واحداً («الفصل الدراسي الأول 2022\2023») فيلتفّ في
     * الشريط الجانبي على سطرين. يُفصل هنا إلى فصل وسنة لعرض أوضح.
     *
     * @return array{term: string, year: ?string}
     */
    public function parts(): array
    {
        $name = trim((string) $this->name);

        if (! preg_match('/(\d{4})\s*[\\\\\/\-–]\s*(\d{2,4})/u', $name, $m)) {
            return ['term' => $name, 'year' => null];
        }

        $term = trim(preg_replace('/\s+/u', ' ', str_replace([$m[0], 'الدراسي'], '', $name)), " \t-–·");

        return [
            'term' => $term !== '' ? $term : $name,
            'year' => $m[1] . '–' . substr($m[2], -2),
        ];
    }
}
