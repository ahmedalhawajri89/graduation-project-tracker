<?php

namespace App\Http\Controllers;

use App\Models\Defense;
use App\Support\DefenseIcs;
use App\Support\DefenseScheduler;

/**
 * تنزيل ملف تقويم (.ics) للمناقشة — للإدارة، ولأعضاء لجنتها، ولفريقها.
 * لغيرهم كأنها غير موجودة.
 */
class DefenseCalendarController extends Controller
{
    public function __invoke(Defense $defense)
    {
        abort_unless(DefenseScheduler::enabled() && $defense->status !== Defense::CANCELLED, 404);

        $allowed = match (true) {
            auth('admin')->check() => true,
            auth('supervisor')->check() => $defense->members()->where('supervisor_id', auth('supervisor')->id())->exists(),
            auth('student')->check() => $defense->project()->whereHas('group', fn ($q) => $q->where('student_id', auth('student')->id()))->exists(),
            default => false,
        };
        abort_unless($allowed, 404);

        return response(DefenseIcs::make($defense), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . DefenseIcs::filename($defense) . '"',
        ]);
    }
}
