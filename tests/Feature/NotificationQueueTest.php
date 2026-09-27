<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Student;
use App\Models\Supervisor;
use App\Notifications\StudentReplayProjectNotify;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * البريد في الطابور وبعد الالتزام.
 *
 * كان يُرسل داخل معاملة قرار المشرف، فتعطّل SMTP يُرجع القبول كلّه.
 *
 * ‎phpunit.xml‎ لا يضبط قاعدة اختبار منفصلة: الحالة و‎read_at‎ تُستعاد في
 * ‎finally‎، والإشعارات وسجلّات التدقيق المُنشأة تُحذف.
 */
class NotificationQueueTest extends TestCase
{
    public function test_mail_is_queued_while_the_in_app_copy_is_immediate(): void
    {
        Queue::fake();
        config(['queue.default' => 'database']);

        $student = Student::whereNotNull('email')->first();
        Notification::send($student, new StudentReplayProjectNotify([
            'project' => 'مشروع', 'supervisor_name' => 'مشرف', 'msg' => 'تم قبول فكرة المشروع',
        ]));

        $jobs = Queue::pushedJobs()[SendQueuedNotifications::class] ?? [];
        $byChannel = collect($jobs)->mapWithKeys(fn ($j) => [implode(',', $j['job']->channels) => $j['job']->connection]);

        $this->assertSame('sync', $byChannel['database'] ?? null, 'نسخة المنصّة ليست فوريّة.');
        $this->assertArrayHasKey('mail', $byChannel->all(), 'البريد لم يُطبَّر.');
        $this->assertNotSame('sync', $byChannel['mail']);
    }

    /** تعطّل البريد لا يُرجع قرار المشرف */
    public function test_a_mail_outage_does_not_undo_an_accept(): void
    {
        $supervisor = Supervisor::has('pendingRequests')->get()->first(fn ($s) => $s->seatsLeft() > 0);

        if (! $supervisor) {
            $this->markTestSkipped('لا مشرف بطلب معلّق ومقعد.');
        }

        $project = $supervisor->pendingRequests()->first();
        $snap = [
            'statuses' => $supervisor->projects()->pluck('status', 'id')->all(),
            'unread' => DB::table('notifications')->whereNull('read_at')->pluck('id')->all(),
            'notifMark' => DB::table('notifications')->max('created_at'),
            'auditMark' => (int) (AuditLog::max('id') ?? 0),
        ];

        // خادم بريد يرفض الاتصال، والطابور متزامن: البريد يُرسل فعلاً — ويفشل
        config([
            'queue.default' => 'sync',
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => '127.0.0.1',
            'mail.mailers.smtp.port' => 1,
            'mail.mailers.smtp.timeout' => 2,
        ]);

        try {
            $this->actingAs($supervisor, 'supervisor')
                ->post(route('supervisor.replay.project', $project->id), ['btnAccept' => 'accept'])
                ->assertSessionHas('success');

            $this->assertSame('accept', $project->fresh()->status, 'تعطّل البريد أرجع القبول.');
        } finally {
            foreach ($snap['statuses'] as $id => $status) {
                DB::table('projects')->where('id', $id)->update(['status' => $status]);
            }
            DB::table('notifications')->whereIn('id', $snap['unread'])->update(['read_at' => null]);
            DB::table('notifications')->where('created_at', '>', $snap['notifMark'])->delete();
            AuditLog::where('id', '>', $snap['auditMark'])->delete();
        }
    }
}
