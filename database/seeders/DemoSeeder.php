<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Supervisor;
use App\Notifications\SuperVisorRequestProjectNotify;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

/**
 * بذرة العرض — تُكمل البيانات الموجودة ولا تمسحها.
 * قابلة لإعادة التشغيل: كل خطوة تتحقق من الحالة قبل الكتابة.
 */
class DemoSeeder extends Seeder
{
    public function run()
    {
        $this->activateSemester();
        $this->previousSemester();
        $this->pendingRequestNotifications();
        $this->deadlines();
        $this->unreadContacts();
        $this->realFiles();
    }

    /** بلا فصل مفعّل تعتمد كل الشاشات على fallback صامت */
    private function activateSemester()
    {
        if (Semester::where('is_active', true)->exists()) {
            return;
        }

        $semester = Semester::withCount('projects')->orderByDesc('projects_count')->first();

        if ($semester) {
            $semester->update(['is_active' => true]);
            $this->command->info("تم تفعيل الفصل: {$semester->name}");
        }
    }

    /** فصل سابق تُنقل إليه مشاريع مقيَّمة — حتى يصبح فلتر الفصل في الأرشيف والاستكشاف ذا معنى */
    private function previousSemester()
    {
        $previous = Semester::firstOrCreate(
            ['name' => 'الفصل الدراسي الثاني 2021\2022'],
            ['is_active' => false]
        );

        if ($previous->projects()->exists()) {
            return;
        }

        $moved = Project::where('status', 'complete')
            ->whereNotNull('grade')
            ->where('semester_id', '!=', $previous->id)
            ->limit(4)
            ->update(['semester_id' => $previous->id]);

        $this->command->info("نُقل {$moved} مشروعاً مقيَّماً إلى الفصل السابق");
    }

    /**
     * شاشة طلبات المشرف تقرأ من الإشعارات غير المقروءة لا من جدول المشاريع،
     * فكل مشروع معلّق بلا إشعار هو مشروع غير مرئي لمشرفه.
     */
    private function pendingRequestNotifications()
    {
        $notified = DB::table('notifications')
            ->where('type', SuperVisorRequestProjectNotify::class)
            ->pluck('data')
            ->map(fn($data) => json_decode($data, true)['project_id'] ?? null)
            ->filter()
            ->all();

        $projects = Project::where('status', 'request')
            ->whereNotNull('supervisor_id')
            ->whereNotIn('id', $notified)
            ->with(['group', 'project_type'])
            ->get();

        foreach ($projects as $project) {
            $supervisor = Supervisor::find($project->supervisor_id);

            if (! $supervisor) {
                continue;
            }

            $universityIds = Student::whereIn('id', $project->group->pluck('student_id'))
                ->pluck('university_id')
                ->all();

            if (empty($universityIds)) {
                continue;
            }

            Notification::send($supervisor, new SuperVisorRequestProjectNotify(
                $project,
                $universityIds,
                $project->project_type->name
            ));
        }

        $this->command->info("أُنشئ {$projects->count()} إشعار طلب معلّق");
    }

    /** بلا موعد نهائي لا تظهر days_left ولا شريط الإلحاح في لوحتي الطالب والمشرف */
    private function deadlines()
    {
        $projects = Project::where('status', 'accept')->whereNull('date_line')->get();

        foreach ($projects as $index => $project) {
            // مدى متدرّج: متأخر، وشيك، ومريح — لتظهر الحالات الثلاث في الواجهة
            $project->update([
                'date_line' => now()->addDays([-3, 5, 12, 30, 60][$index % 5]),
            ]);
        }

        $this->command->info("ضُبط موعد نهائي لـ {$projects->count()} مشروعاً");
    }

    /** شارة العدّاد في سايدبار الأدمن لا تظهر إلا مع رسائل غير مقروءة */
    private function unreadContacts()
    {
        if (Contact::where('is_read', 0)->count() >= 3) {
            return;
        }

        $messages = [
            [
                'name' => 'منى عبد الهادي',
                'email' => 'mona.abdelhadi@example.com',
                'subject' => 'استفسار عن موعد تسليم المقترحات',
                'message' => 'السلام عليكم، أود معرفة آخر موعد لتقديم مقترح مشروع التخرج لهذا الفصل، وهل يمكن تعديل المقترح بعد إرساله للمشرف؟ شكراً لكم.',
            ],
            [
                'name' => 'خالد أبو ندى',
                'email' => 'khaled.abunada@example.com',
                'subject' => 'مشكلة في تسجيل الدخول',
                'message' => 'لا أستطيع الدخول برقمي الجامعي رغم أنه صحيح، تظهر لي رسالة بيانات غير صحيحة. أرجو المساعدة.',
            ],
            [
                'name' => 'د. إيمان الشوا',
                'email' => 'eman.alshawa@example.com',
                'subject' => 'طلب رفع الحد الأقصى للمجموعات',
                'message' => 'أرجو زيادة الحد الأقصى لعدد المجموعات المسندة إليّ من ٣ إلى ٥ لهذا الفصل، لوجود طلبات إضافية من طلاب التخصص.',
            ],
        ];

        foreach ($messages as $message) {
            Contact::firstOrCreate(
                ['email' => $message['email'], 'subject' => $message['subject']],
                $message + ['is_read' => 0]
            );
        }

        // بعض الرسائل القديمة تعود غير مقروءة حتى يظهر الصندوق مختلطاً
        Contact::where('is_read', 1)->limit(2)->update(['is_read' => 0]);

        $this->command->info('رسائل تواصل غير مقروءة جاهزة');
    }

    /**
     * صفوف ProjectFile الموجودة يتيمة — لا ملف لها على القرص، فزر التنزيل يعيد 404.
     * نكتب ملفات حقيقية ونصحّح المسارات لتطابق ما يكتبه الرفع الفعلي.
     */
    private function realFiles()
    {
        foreach (ProjectFile::all() as $file) {
            if (Storage::disk('local')->exists($file->path)) {
                continue;
            }

            $title = pathinfo($file->title, PATHINFO_FILENAME);
            $extension = pathinfo($file->title, PATHINFO_EXTENSION) ?: 'pdf';
            $path = "project_files/{$file->project_id}/" . uniqid() . ".{$extension}";

            $contents = $this->documentFor($title, $extension);
            Storage::disk('local')->put($path, $contents);

            $file->update([
                'title' => $title,
                'path' => $path,
                'size' => strlen($contents),
            ]);
        }

        // مشروع نشط غني بالمراحل والنقاش لكنه بلا ملفات — نضيف مرفقين ليكتمل المشهد
        if (ProjectFile::where('title', 'خطة المشروع')->exists()) {
            return;
        }

        $project = Project::where('status', 'accept')
            ->withCount(['milestones', 'files'])
            ->having('files_count', 0)
            ->orderByDesc('milestones_count')
            ->first();

        if ($project) {
            $leader = $project->group()->where('type', 'leader')->first();

            foreach ([['خطة المشروع', 'pdf'], ['العرض التقديمي الأولي', 'pptx']] as [$title, $extension]) {
                $path = "project_files/{$project->id}/" . uniqid() . ".{$extension}";
                $contents = $this->documentFor($title, $extension);
                Storage::disk('local')->put($path, $contents);

                $project->files()->create([
                    'title' => $title,
                    'path' => $path,
                    'size' => strlen($contents),
                    'uploader_type' => Student::class,
                    'uploader_id' => $leader?->student_id,
                ]);
            }

            $this->command->info("أُضيف مرفقان للمشروع #{$project->id}");
        }
    }

    /** PDF صالح فعلاً حتى يفتح بعد التنزيل، وغيره ملف نصي بسيط */
    private function documentFor($title, $extension)
    {
        if ($extension !== 'pdf') {
            return "{$title}\n\nملف تجريبي لعرض واجهة نظام تخرُّج.\n";
        }

        $stream = "BT /F1 18 Tf 60 760 Td (Takharruj - demo document) Tj ET";
        $length = strlen($stream);

        $pdf = "%PDF-1.4\n";
        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n",
            "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n",
            "5 0 obj\n<< /Length {$length} >>\nstream\n{$stream}\nendstream\nendobj\n",
        ];

        $offsets = [];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= str_pad($offset, 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";

        return $pdf;
    }
}
