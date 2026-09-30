<?php

namespace App\Http\Controllers;

use App\Models\Defense;
use App\Support\DefenseScheduler;

/**
 * محضر المناقشة: صفحة A4 تُطبع أو تُحفظ PDF من المتصفح — العربية فيها
 * صحيحة الاتجاه والتشكيل، وهو ما تكسره مكتبات PDF في PHP.
 *
 * للإدارة ولأعضاء اللجنة وحدهم (فيه درجة كل عضو وملاحظاته). قبل اكتمال
 * الدرجات يُفتح «مسودة» تُطبع للتوقيع اليدوي يوم المناقشة.
 */
class DefenseMinutesController extends Controller
{
    public function __invoke(Defense $defense)
    {
        abort_unless(DefenseScheduler::enabled() && $defense->status !== Defense::CANCELLED, 404);

        $allowed = match (true) {
            auth('admin')->check() => true,
            auth('supervisor')->check() => $defense->members()->where('supervisor_id', auth('supervisor')->id())->exists(),
            default => false,
        };
        abort_unless($allowed, 404);

        $defense->load(['room', 'members.supervisor', 'project.group.student', 'project.project_type.specialize', 'project.supervisor', 'project.semester']);

        return view('dashboard.defense.minutes', [
            'defense' => $defense,
            'project' => $defense->project,
            'draft' => $defense->status !== Defense::DONE || $defense->members->contains(fn ($m) => is_null($m->grade)),
            'back' => auth('admin')->check()
                ? route('admin.defenses.index', ['tab' => $defense->status === Defense::DONE ? 'past' : 'upcoming'])
                : route('supervisor.defenses.show', $defense->id),
        ]);
    }
}
