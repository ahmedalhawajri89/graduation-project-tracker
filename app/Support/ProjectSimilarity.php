<?php

namespace App\Support;

use App\Models\Project;
use Illuminate\Support\Collection;

/**
 * «هل نُفّذت فكرتي؟» — يُسأل أثناء كتابة عنوان المقترح لا في صفحة منفصلة.
 *
 * مطابقة كلمات لا بحث دلالي: تكفي لتنبيه الطالب، والتنبيه لا يمنع
 * الإرسال. المكتمل وحده يُقارن به — الجاري فكرة زميل لم تُنجز بعد.
 */
class ProjectSimilarity
{
    /** كلمات تتكرّر في كل عنوان فلا تميّز شيئاً */
    private const STOP = [
        'نظام', 'تطبيق', 'منصة', 'منصّة', 'موقع', 'إدارة', 'ادارة', 'باستخدام', 'استخدام',
        'تصميم', 'تطوير', 'بناء', 'الكتروني', 'إلكتروني', 'ذكي', 'مشروع', 'على', 'في', 'من',
        'إلى', 'الى', 'عن', 'مع', 'the', 'and', 'for', 'with', 'using', 'system', 'app',
    ];

    /** @return Collection<int, Project> أقرب ثلاثة، الأكثر تطابقاً أولاً ثم تخصص الطالب */
    public static function find(string $title, ?int $specializeId = null, int $limit = 3): Collection
    {
        $words = self::words($title);

        if ($words->isEmpty()) {
            return collect();
        }

        // كل التخصصات: الفكرة المكرّرة مكرّرة أيّاً كان من نفّذها.
        // التخصص يُقدِّم في الترتيب فقط، عند تساوي التطابق
        $candidates = Project::where('status', 'complete')
            ->where(function ($q) use ($words) {
                foreach ($words as $w) {
                    $q->orWhere('title', 'like', "%{$w}%")->orWhere('description', 'like', "%{$w}%");
                }
            })
            ->with(['supervisor:id,name', 'semester:id,name', 'project_type:id,specialize_id'])
            ->limit(50)
            ->get();

        // كلمتان فأكثر، أو نصف كلمات العنوان إن كان قصيراً
        $needed = min(2, (int) ceil($words->count() / 2));

        return $candidates
            ->map(function ($p) use ($words) {
                $hay = mb_strtolower($p->title . ' ' . $p->description);
                $p->similarity = $words->filter(fn ($w) => str_contains($hay, $w))->count();

                return $p;
            })
            ->filter(fn ($p) => $p->similarity >= $needed)
            ->sortBy([
                ['similarity', 'desc'],
                fn ($a, $b) => ((int) $b->project_type?->specialize_id === (int) $specializeId)
                    <=> ((int) $a->project_type?->specialize_id === (int) $specializeId),
            ])
            ->take($limit)
            ->values();
    }

    /** كلمات العنوان المميِّزة: بلا «ال» التعريف ولا القصير ولا الشائع */
    private static function words(string $title): Collection
    {
        return collect(preg_split('/[\s\-—،,.:؛;()]+/u', mb_strtolower(trim($title))))
            ->map(fn ($w) => preg_replace('/^(وال|بال|لل|ال)/u', '', $w))
            ->filter(fn ($w) => mb_strlen($w) >= 3 && ! in_array($w, self::STOP, true))
            ->unique()
            ->take(8)
            ->values();
    }
}
