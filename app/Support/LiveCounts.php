<?php

namespace App\Support;

use App\Models\Admin;
use App\Models\Contact;
use App\Models\DefenseMember;
use App\Models\ProjectComment;
use App\Models\Student;
use App\Models\Supervisor;

/**
 * العدّادات الحيّة للّوحة: الجرس وشارات الشريط الجانبي.
 *
 * مصدر واحد لما يُرسم مع الصفحة (View Composer) ولما يُحدَّث بعدها كل
 * بضع ثوانٍ (LiveController) — فلا يختلف رقم الصفحة عن رقم التحديث.
 * null = البند غير موجود لهذا الدور (أو ميزته غير مفعّلة) فلا يُرسم.
 */
class LiveCounts
{
    /** المستخدم الحالي أيّاً كان حارسه */
    public static function user(): Admin|Supervisor|Student|null
    {
        return auth('admin')->user() ?? auth('supervisor')->user() ?? auth('student')->user();
    }

    /**
     * @return array{notifications: int, discussion: ?int, requests: ?int, defenses: ?int, contacts: ?int}
     */
    public static function for(Admin|Supervisor|Student $user): array
    {
        $counts = [
            'notifications' => $user->unreadNotifications()->count(),
            'discussion' => null,
            'requests' => null,
            'defenses' => null,
            'contacts' => null,
        ];

        // النقاش: المشرف قناته وحدها، والطالب القناتان معاً — تبويب واحد يحملهما
        if (! $user instanceof Admin) {
            $ids = Discussion::projectsFor($user)->pluck('id');
            $counts['discussion'] = array_sum(Discussion::unreadFor($user, $ids))
                + ($user instanceof Student ? array_sum(Discussion::unreadFor($user, $ids, ProjectComment::TEAM)) : 0);
        }

        if ($user instanceof Supervisor) {
            // «طلبات الإشراف»: المعلّقة وحدها، لا كل الإشعارات
            $counts['requests'] = $user->pendingRequests()->count();

            // «مناقشاتي»: بدأت ولم أرصد درجتي، ودرجتها لم تُعتمد
            if (DefenseScheduler::enabled()) {
                $counts['defenses'] = DefenseMember::where('supervisor_id', $user->id)
                    ->whereNull('grade')
                    ->whereHas('defense', fn ($q) => $q->whereIn('status', ['scheduled', 'done'])->where('starts_at', '<=', now())
                        ->whereHas('project', fn ($p) => $p->whereNull('grade_locked_at')))
                    ->count();
            }
        }

        if ($user instanceof Admin) {
            $counts['contacts'] = Contact::where('is_read', 0)->count();
            $counts['defenses'] = DefenseScheduler::enabled() ? DefenseScheduler::awaiting()->count() : null;
        }

        return $counts;
    }
}
