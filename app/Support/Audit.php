<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * تسجيل أحداث التدقيق.
 *
 * يُسجَّل عند نقاط القرار وحدها لا عند كل كتابة: تسجيل كل تعديل حقل
 * يُنتج ضجيجاً يُهمَل، فيضيع فيه ما يهمّ.
 */
class Audit
{
    /**
     * @param  string  $action  مفتاح من \u200EAuditLog::LABELS\u200E
     * @param  Model|null  $subject  الكيان المتأثّر
     * @param  array  $changes  ['grade' => ['from' => 80, 'to' => 90]]
     */
    public static function record(string $action, ?Model $subject = null, array $changes = []): void
    {
        try {
            [$actor, $role] = self::actor();

            AuditLog::create([
                'actor_type' => $actor ? $actor::class : null,
                'actor_id' => $actor?->getKey(),
                // لقطة نصّية: الفاعل قد يُحذف لاحقاً
                'actor_name' => $actor?->name,
                'actor_role' => $role,
                'action' => $action,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'subject_label' => $subject ? self::label($subject) : null,
                'changes' => $changes ?: null,
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            // فشل التسجيل لا يُسقط العملية نفسها: حذف مشروع يجب أن
            // ينجح ولو تعذّر تسجيله. لكنه لا يُبتلع أيضاً.
            Log::error('فشل تسجيل حدث تدقيق', ['action' => $action, 'exception' => $e]);
        }
    }

    /** @return array{0: Model|null, 1: string|null} */
    private static function actor(): array
    {
        foreach (['admin', 'supervisor', 'student'] as $guard) {
            if (auth($guard)->check()) {
                return [auth($guard)->user(), $guard];
            }
        }

        return [null, null];
    }

    /**
     * نصّ يعرّف الكيان بعد حذفه.
     *
     * \u200Etitle\u200E للمشاريع و\u200Ename\u200E لبقية النماذج — ولا واحد منهما يعني أن
     * السطر يفقد معناه.
     */
    private static function label(Model $subject): string
    {
        return (string) ($subject->title ?? $subject->name ?? ('#' . $subject->getKey()));
    }
}
