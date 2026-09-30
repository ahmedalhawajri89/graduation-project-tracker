<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DefenseController;
use App\Http\Controllers\Supervisor\DefenseController as SupervisorDefenseController;
use App\Http\Controllers\Admin\GroupsController;
use App\Http\Controllers\Admin\semesterController;
use App\Http\Controllers\Admin\SpecializeController;
use App\Http\Controllers\Admin\SpecializeProjectController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SupervisorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FileNoteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Student\ProjectCommentController as StudentProjectCommentController;
use App\Http\Controllers\Student\ProjectFileController as StudentProjectFileController;
use App\Http\Controllers\Student\MilestoneSubmissionController as StudentMilestoneSubmissionController;
use App\Http\Controllers\Student\TeamRolesController as StudentTeamRolesController;
use App\Http\Controllers\Supervisor\DashboardController as SupervisorDashboardController;
use App\Http\Controllers\Supervisor\ProfileController as SupervisorProfileController;
use App\Http\Controllers\Supervisor\ProjectManageController;
use App\Http\Controllers\Supervisor\StagePlanController;
use App\Http\Controllers\Supervisor\DiscussionController as SupervisorDiscussionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('site.home');
// حد أقصى 5 رسائل تواصل بالدقيقة — حماية من السبام
Route::post('/send', [HomeController::class, 'send'])->name('site.send')->middleware('throttle:5,1');

Route::middleware(['guest:admin,supervisor,student', 'PreventBackHistory'])->group(function () {
    Route::get('/login', [AuthController::class, 'loginView'])->name('login');
    // Route::get('/', [AuthController::class, 'login'])->name('login');
    // حد أقصى 5 محاولات دخول بالدقيقة — حماية من تخمين كلمات السر
    Route::post('/login', [AuthController::class, 'login'])->name('login.check')->middleware('throttle:5,1');

    // استرجاع كلمة المرور. الخنق لازم: النموذج يُرسل بريداً عند كل
    // طلب، فبلا حدّ يصير أداة إغراق. والدور جزء من الرابط لأن النظام
    // ثلاثة حُرّاس بثلاثة وسطاء كلمات مرور.
    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])
        ->name('password.email')->middleware('throttle:5,10');

    Route::get('/reset-password/{guard}/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password/{guard}', [PasswordResetController::class, 'reset'])
        ->name('password.update')->middleware('throttle:10,10');
});

Route::middleware(['auth:student,supervisor,admin', 'PreventBackHistory'])->group(function () {

    // الحارس الخارجي \u200Eauth:student,supervisor,admin\u200E يمرّ إن نجح أيّ من
    // الثلاثة — فكان أي طالب مسجَّل دخوله يفتح \u200E/admin/administrators\u200E
    // ويُرسل \u200EPOST\u200E فيصير أدمن. لوحة التحكم والملف الشخصي وحدهما كانا
    // يحملان \u200Eauth:admin\u200E في مُنشِئهما، وبقية المتحكّمات مكشوفة.
    Route::prefix('/admin')->name('admin.')
        ->middleware(['auth:admin', 'semester'])
        ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // الملف الشخصي
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/avatar', [AdminProfileController::class, 'uploadAvatar'])->name('profile.avatar.store');
        Route::delete('/profile/avatar', [AdminProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');

        //====================== start admin data
        Route::get('administrators/data', [AdminController::class, 'getData'])->name('administrators.getData');
        Route::resource('administrators', AdminController::class)->except('create', 'edit', 'show');
        //====================== end admin data

        //====================== start supervisor data
        Route::get('supervisors/data', [SupervisorController::class, 'getData'])->name('supervisors.getData');
        Route::get('supervisors/export', [SupervisorController::class, 'export'])->name('supervisors.export');
        Route::post('supervisors/import', [SupervisorController::class, 'import'])->name('supervisors.import');
        Route::get('supervisors/import/template', [SupervisorController::class, 'template'])->name('supervisors.template');
        Route::get('supervisors/{id}/groups', [SupervisorController::class, 'groups'])->name('supervisors.groups');
        Route::resource('supervisors', SupervisorController::class)->except('create', 'edit', 'show');
        //====================== end supervisor data

        //====================== start student data
        Route::get('students/data', [StudentController::class, 'getData'])->name('students.getData');
        Route::post('students/import', [StudentController::class, 'import'])->name('students.import');
        Route::get('students/import/template', [StudentController::class, 'template'])->name('students.template');
        // الاستيراد كان بلا تصدير مقابل — نصف دورة
        Route::get('students/export', [StudentController::class, 'export'])->name('students.export');
        Route::resource('students', StudentController::class)->except('create', 'edit', 'show');
        //====================== end student data

        //====================== start specialize data
        Route::post('specialize/{id}/archive', [SpecializeController::class, 'archive'])->name('specialize.archive');
        Route::post('specialize/{id}/restore', [SpecializeController::class, 'restore'])->name('specialize.restore');
        Route::resource('specialize', SpecializeController::class)->except('create', 'edit', 'show');

        Route::prefix('specialize/projects/')->name('specialize.projects.')->group(function () {
            // \u200E{specialize_id}/data\u200E أُزيل: الصفحة صارت قائمة تُعرض من
            // \u200Eindex()\u200E مباشرةً، فلم يبقَ من يطلب البيانات عبر ajax
            Route::get('{specialize_id}', [SpecializeProjectController::class, 'index'])->name('index');
            Route::post('{specialize_id}/store', [SpecializeProjectController::class, 'store'])->name('store');
            Route::put('{specialize_id}/update', [SpecializeProjectController::class, 'update'])->name('update');
            Route::delete('{specialize_id}/delete', [SpecializeProjectController::class, 'destroy'])->name('destroy');
        });

        //====================== end specialize data

        //====================== start semester data
        // \u200Esemesters/data\u200E أُزيل: الصفحة تُعرض من \u200Eindex()\u200E مباشرةً
        Route::post('semesters/{id}/activate', [semesterController::class, 'activate'])->name('semesters.activate');
        Route::resource('semesters', semesterController::class)->except('create', 'edit', 'show');

        //====================== start groups data
        Route::get('groups/index', [GroupsController::class, 'index'])->name('groups.index');
        Route::get('groups/data', [GroupsController::class, 'getData'])->name('groups.getData');
        Route::get('groups/export', [GroupsController::class, 'export'])->name('groups.export');
        // المحذوفات: الحذف صار ناعماً فصار الاسترجاع ممكناً
        Route::get('groups/trash', [GroupsController::class, 'trash'])->name('groups.trash');
        Route::post('groups/{id}/restore', [GroupsController::class, 'restore'])->name('groups.restore');
        Route::delete('groups/{id}/force', [GroupsController::class, 'forceDestroy'])->name('groups.forceDestroy');
        Route::get('groups/{id}/show', [GroupsController::class, 'show'])->name('groups.show');
        Route::get('groups/{id}/edit', [GroupsController::class, 'edit'])->name('groups.edit');
        Route::post('groups/update', [GroupsController::class, 'update'])->name('groups.update');
        Route::delete('groups/{id}/delete', [GroupsController::class, 'destroy'])->name('groups.destroy');
        // فكّ اعتماد الدرجة — مخرج الأدمن حين تُعتمد خطأً، بسبب مكتوب
        Route::post('groups/{id}/grade/unlock', [GroupsController::class, 'unlockGrade'])->name('groups.grade.unlock');
        // إدارة أعضاء الفريق: كانت الإضافة ممكنة والإزالة لا — فصفحة
        // التعديل تعرض «الفريق ٥ والحدّ ٣» ولا تملك ما تُصلح به
        // بحث المتاحين: القائمة لم تعد تُرسل كاملة في الصفحة
        Route::get('groups/{id}/students/search', [GroupsController::class, 'searchStudents'])->name('groups.students.search');
        Route::delete('groups/{id}/members/{member}', [GroupsController::class, 'removeMember'])->name('groups.members.remove');
        Route::post('groups/{id}/members/{member}/leader', [GroupsController::class, 'setLeader'])->name('groups.members.leader');
        //====================== end groups data

        //====================== start defenses
        // المناقشات: لجنة وموعد وقاعة أو رابط لكل مشروع مكتمل
        Route::get('defenses', [DefenseController::class, 'index'])->name('defenses.index');
        Route::get('defenses/export', [DefenseController::class, 'export'])->name('defenses.export');
        Route::post('defenses', [DefenseController::class, 'store'])->name('defenses.store');
        Route::post('defenses/plan', [DefenseController::class, 'planStore'])->name('defenses.plan.store');
        Route::put('defenses/{defense}', [DefenseController::class, 'update'])->name('defenses.update');
        Route::post('defenses/{defense}/cancel', [DefenseController::class, 'cancel'])->name('defenses.cancel');
        Route::post('defenses/rooms', [DefenseController::class, 'storeRoom'])->name('defenses.rooms.store');
        Route::post('defenses/rooms/{room}/toggle', [DefenseController::class, 'toggleRoom'])->name('defenses.rooms.toggle');
        Route::delete('defenses/rooms/{room}', [DefenseController::class, 'destroyRoom'])->name('defenses.rooms.destroy');
        //====================== end defenses

        //====================== start audit log
        // قراءة وتصدير فقط: لا مسار تعديل ولا حذف. سجلّ يُعدَّل ليس سجلّاً.
        Route::get('audit', [AuditController::class, 'index'])->name('audit.index');
        Route::get('audit/export', [AuditController::class, 'export'])->name('audit.export');
        //====================== end audit log

        //====================== start contact data
        Route::get('contacts/index', [ContactController::class, 'index'])->name('contact.index');
        Route::post('contacts/read-all', [ContactController::class, 'markAllRead'])->name('contact.readAll');
        Route::post('contacts/{id}/unread', [ContactController::class, 'markUnread'])->name('contact.unread');
        Route::delete('contacts/{id}/delete', [ContactController::class, 'destroy'])->name('contact.destroy');
        //====================== end contact data
//====================== end semester data

    });

    Route::prefix('student')->name('student.')->middleware('semester')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::post('/dashboard/project/create', [StudentDashboardController::class, 'createProject'])->name('project.create');
        // سحب طلب معلّق — القائد وحده، قبل ردّ المشرف
        Route::post('/projects/{project}/withdraw', [StudentDashboardController::class, 'withdrawProject'])->name('project.withdraw');
        // بحث زملاء التخصص المتاحين — يغذّي منتقي الفريق
        Route::get('/mates/search', [StudentDashboardController::class, 'searchMates'])->name('mates.search');
        Route::get('/dashboard/projects/request', [StudentDashboardController::class, 'showNotification'])->name('showNotification');
        Route::get('/projects/explore', [StudentDashboardController::class, 'exploreProjects'])->name('projects.explore');
        // «مشاريع منجزة مشابهة» أثناء كتابة عنوان المقترح
        Route::get('/projects/similar', [StudentDashboardController::class, 'similarProjects'])->name('projects.similar');

        // الملف الشخصي
        Route::get('/profile', [StudentProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [StudentProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/avatar', [StudentProfileController::class, 'uploadAvatar'])->name('profile.avatar.store');
        Route::delete('/profile/avatar', [StudentProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');

        // ملفات المشروع
        Route::post('/projects/{project}/files', [StudentProjectFileController::class, 'store'])->name('files.store');
        Route::delete('/files/{file}', [StudentProjectFileController::class, 'destroy'])->name('files.destroy');

        // الفريق والأدوار: صفحة يراها كل الفريق، ويوزّع فيها القائد وحده
        Route::get('/team', [StudentTeamRolesController::class, 'index'])->name('team');
        Route::post('/projects/{project}/roles', [StudentTeamRolesController::class, 'update'])->name('roles.update');

        // تسليم مرحلة — تنتظر بعده مراجعة المشرف
        Route::post('/milestones/{milestone}/submit', [StudentMilestoneSubmissionController::class, 'store'])->name('milestones.submit');

        // تعليقات المشروع
        Route::post('/projects/{project}/comments', [StudentProjectCommentController::class, 'store'])->name('comments.store');
        Route::delete('/comments/{comment}', [StudentProjectCommentController::class, 'destroy'])->name('comments.destroy');

        // النقاش — تبويب مستقلّ لا قسم في ذيل اللوحة
        Route::get('/discussion', [StudentProjectCommentController::class, 'index'])->name('discussion');
    });

    Route::prefix('supervisor')->name('supervisor.')->middleware('semester')->group(function () {
        Route::get('/dashboard', [SupervisorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/projects/request', [SupervisorDashboardController::class, 'showNotification'])->name('showNotification');
        // الردّ على الطلب بالمشروع لا بالإشعار: الطلب يبقى قابلاً للردّ ولو قُرئ إشعاره
        Route::post('/requests/{project_id}/reply', [SupervisorDashboardController::class, 'replayProject'])->name('replay.project');
        Route::post('/dashboard/{project_id}/complete', [SupervisorDashboardController::class, 'compoleteProject'])->name('project.complete');

        // أرشيف مشاريعي (يجب أن يسبق مسار {project} حتى لا تُلتقط كلمة archive كمعرّف)
        Route::get('/projects/archive', [ProjectManageController::class, 'archive'])->name('projects.archive');
        // مناقشاتي: اللجان التي هو عضو فيها — ملف المشروع ورصد الدرجة
        Route::get('/defenses', [SupervisorDefenseController::class, 'index'])->name('defenses.index');
        Route::get('/defenses/{defense}', [SupervisorDefenseController::class, 'show'])->name('defenses.show');
        Route::post('/defenses/{defense}/grade', [SupervisorDefenseController::class, 'grade'])->name('defenses.grade');

        // صفحة إدارة المشروع
        Route::get('/projects/{project}', [ProjectManageController::class, 'show'])->name('projects.show');

        // مراحل المشروع (Milestones)
        Route::post('/projects/{project}/milestones', [ProjectManageController::class, 'milestoneStore'])->name('milestones.store');
        Route::post('/milestones/{milestone}/toggle', [ProjectManageController::class, 'milestoneToggle'])->name('milestones.toggle');
        // مراجعة تسليم: اعتماد، أو «مطلوب تعديل» بملاحظة
        Route::post('/milestones/{milestone}/review', [ProjectManageController::class, 'milestoneReview'])->name('milestones.review');
        Route::delete('/milestones/{milestone}', [ProjectManageController::class, 'milestoneDestroy'])->name('milestones.destroy');

        // ملفات المشروع
        Route::post('/projects/{project}/files', [ProjectManageController::class, 'fileStore'])->name('files.store');
        Route::delete('/files/{file}', [ProjectManageController::class, 'fileDestroy'])->name('files.destroy');

        // تعليقات المشروع
        Route::post('/projects/{project}/comments', [ProjectManageController::class, 'commentStore'])->name('comments.store');
        Route::delete('/comments/{comment}', [ProjectManageController::class, 'commentDestroy'])->name('comments.destroy');

        // النقاش: صندوق وارد بمجموعات المشرف، والمحادثة المختارة بجانبه
        Route::get('/discussion/{project?}', [SupervisorDiscussionController::class, 'index'])->name('discussion');

        // خطة المراحل: المرحلة تُعرَّف مرّة فتُنشأ في كل المجموعات
        Route::get('/plan', [StagePlanController::class, 'index'])->name('plan');
        Route::post('/plan/stages', [StagePlanController::class, 'store'])->name('plan.store');
        Route::post('/plan/stages/{stage}', [StagePlanController::class, 'update'])->name('plan.update');
        Route::delete('/plan/stages/{stage}', [StagePlanController::class, 'destroy'])->name('plan.destroy');

        // الموعد النهائي والتقييم
        Route::post('/projects/{project}/deadline', [ProjectManageController::class, 'deadlineUpdate'])->name('deadline.update');
        Route::post('/projects/{project}/evaluate', [ProjectManageController::class, 'evaluate'])->name('project.evaluate');
        // اعتماد الدرجة: يقفلها على المشرف، ولا يفكّها إلا مسؤول النظام
        Route::post('/projects/{project}/grade/lock', [ProjectManageController::class, 'lockGrade'])->name('project.grade.lock');

        // الملف الشخصي
        Route::get('/profile', [SupervisorProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [SupervisorProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/avatar', [SupervisorProfileController::class, 'uploadAvatar'])->name('profile.avatar.store');
        Route::delete('/profile/avatar', [SupervisorProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
    });

    // ملف تقويم المناقشة (.ics) — للإدارة ولجنتها وفريقها
    Route::get('/defenses/{defense}/calendar.ics', \App\Http\Controllers\DefenseCalendarController::class)->name('defenses.ics');

    // تنزيل ملفات المشاريع (أدمن/مشرف المشروع/أعضاء الفريق)
    Route::get('/files/{file}/download', [FileController::class, 'download'])->name('files.download');
    Route::get('/stages/{stage}/template', [FileController::class, 'stageTemplate'])->name('stages.template');
    Route::get('/submissions/{submission}/file', [FileController::class, 'submissionFile'])->name('submissions.file');

    // ملاحظات على ملفات المشروع — للفريق ولمشرفه، والصلاحية في المتحكّم
    Route::post('/files/{file}/notes', [FileNoteController::class, 'store'])->name('files.notes.store');
    Route::post('/file-notes/{note}/toggle', [FileNoteController::class, 'toggle'])->name('files.notes.toggle');
    Route::delete('/file-notes/{note}', [FileNoteController::class, 'destroy'])->name('files.notes.destroy');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

});
