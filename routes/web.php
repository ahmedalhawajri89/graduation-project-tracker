<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GroupsController;
use App\Http\Controllers\Admin\semesterController;
use App\Http\Controllers\Admin\SpecializeController;
use App\Http\Controllers\Admin\SpecializeProjectController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SupervisorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Student\ProjectCommentController as StudentProjectCommentController;
use App\Http\Controllers\Student\ProjectFileController as StudentProjectFileController;
use App\Http\Controllers\Supervisor\DashboardController as SupervisorDashboardController;
use App\Http\Controllers\Supervisor\ProfileController as SupervisorProfileController;
use App\Http\Controllers\Supervisor\ProjectManageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('site.home');
// حد أقصى 5 رسائل تواصل بالدقيقة — حماية من السبام
Route::post('/send', [HomeController::class, 'send'])->name('site.send')->middleware('throttle:5,1');

Route::middleware(['guest:admin,supervisor,student', 'PreventBackHistory'])->group(function () {
    Route::get('/login', [AuthController::class, 'loginView'])->name('login');
    // Route::get('/', [AuthController::class, 'login'])->name('login');
    // حد أقصى 5 محاولات دخول بالدقيقة — حماية من تخمين كلمات السر
    Route::post('/login', [AuthController::class, 'login'])->name('login.check')->middleware('throttle:5,1');

});

Route::middleware(['auth:student,supervisor,admin', 'PreventBackHistory'])->group(function () {

    Route::prefix('/admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // الملف الشخصي
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');

        //====================== start admin data
        Route::get('administrators/data', [AdminController::class, 'getData'])->name('administrators.getData');
        Route::resource('administrators', AdminController::class)->except('create', 'edit', 'show');
        //====================== end admin data

        //====================== start supervisor data
        Route::get('supervisors/data', [SupervisorController::class, 'getData'])->name('supervisors.getData');
        Route::post('supervisors/import', [SupervisorController::class, 'import'])->name('supervisors.import');
        Route::get('supervisors/{id}/groups', [SupervisorController::class, 'groups'])->name('supervisors.groups');
        Route::resource('supervisors', SupervisorController::class)->except('create', 'edit', 'show');
        //====================== end supervisor data

        //====================== start student data
        Route::get('students/data', [StudentController::class, 'getData'])->name('students.getData');
        Route::post('students/import', [StudentController::class, 'import'])->name('students.import');
        Route::resource('students', StudentController::class)->except('create', 'edit', 'show');
        //====================== end student data

        //====================== start specialize data
        Route::get('specialize/data', [SpecializeController::class, 'getData'])->name('specialize.getData');
        Route::resource('specialize', SpecializeController::class)->except('create', 'edit', 'show');

        Route::prefix('specialize/projects/')->name('specialize.projects.')->group(function () {
            Route::get('{specialize_id}/data', [SpecializeProjectController::class, 'getData'])->name('getData');
            Route::get('{specialize_id}', [SpecializeProjectController::class, 'index'])->name('index');
            Route::post('{specialize_id}/store', [SpecializeProjectController::class, 'store'])->name('store');
            Route::put('{specialize_id}/update', [SpecializeProjectController::class, 'update'])->name('update');
            Route::delete('{specialize_id}/delete', [SpecializeProjectController::class, 'destroy'])->name('destroy');
        });

        //====================== end specialize data

        //====================== start semester data
        Route::get('semesters/data', [semesterController::class, 'getData'])->name('semesters.getData');
        Route::post('semesters/{id}/activate', [semesterController::class, 'activate'])->name('semesters.activate');
        Route::resource('semesters', semesterController::class)->except('create', 'edit', 'show');

        //====================== start groups data
        Route::get('groups/index', [GroupsController::class, 'index'])->name('groups.index');
        Route::get('groups/export', [GroupsController::class, 'export'])->name('groups.export');
        Route::get('groups/{id}/show', [GroupsController::class, 'show'])->name('groups.show');
        Route::get('groups/{id}/edit', [GroupsController::class, 'edit'])->name('groups.edit');
        Route::post('groups/update', [GroupsController::class, 'update'])->name('groups.update');
        Route::delete('groups/{id}/delete', [GroupsController::class, 'destroy'])->name('groups.destroy');
        //====================== end groups data

        //====================== start contact data
        Route::get('contacts/index', [ContactController::class, 'index'])->name('contact.index');
        Route::delete('contacts/{id}/delete', [ContactController::class, 'destroy'])->name('contact.destroy');
        //====================== end contact data
//====================== end semester data

    });

    Route::prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::post('/dashboard/project/create', [StudentDashboardController::class, 'createProject'])->name('project.create');
        Route::get('/dashboard/projects/request', [StudentDashboardController::class, 'showNotification'])->name('showNotification');
        Route::get('/projects/explore', [StudentDashboardController::class, 'exploreProjects'])->name('projects.explore');

        // الملف الشخصي
        Route::get('/profile', [StudentProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [StudentProfileController::class, 'update'])->name('profile.update');

        // ملفات المشروع
        Route::post('/projects/{project}/files', [StudentProjectFileController::class, 'store'])->name('files.store');
        Route::delete('/files/{file}', [StudentProjectFileController::class, 'destroy'])->name('files.destroy');

        // تعليقات المشروع
        Route::post('/projects/{project}/comments', [StudentProjectCommentController::class, 'store'])->name('comments.store');
        Route::delete('/comments/{comment}', [StudentProjectCommentController::class, 'destroy'])->name('comments.destroy');
    });

    Route::prefix('supervisor')->name('supervisor.')->group(function () {
        Route::get('/dashboard', [SupervisorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/projects/request', [SupervisorDashboardController::class, 'showNotification'])->name('showNotification');
        Route::post('/dashboard/{project_id}/{notify_id}/replay', [SupervisorDashboardController::class, 'replayProject'])->name('replay.project');
        Route::post('/dashboard/{project_id}/complete', [SupervisorDashboardController::class, 'compoleteProject'])->name('project.complete');

        // أرشيف مشاريعي (يجب أن يسبق مسار {project} حتى لا تُلتقط كلمة archive كمعرّف)
        Route::get('/projects/archive', [ProjectManageController::class, 'archive'])->name('projects.archive');

        // صفحة إدارة المشروع
        Route::get('/projects/{project}', [ProjectManageController::class, 'show'])->name('projects.show');

        // مراحل المشروع (Milestones)
        Route::post('/projects/{project}/milestones', [ProjectManageController::class, 'milestoneStore'])->name('milestones.store');
        Route::post('/milestones/{milestone}/toggle', [ProjectManageController::class, 'milestoneToggle'])->name('milestones.toggle');
        Route::delete('/milestones/{milestone}', [ProjectManageController::class, 'milestoneDestroy'])->name('milestones.destroy');

        // ملفات المشروع
        Route::post('/projects/{project}/files', [ProjectManageController::class, 'fileStore'])->name('files.store');
        Route::delete('/files/{file}', [ProjectManageController::class, 'fileDestroy'])->name('files.destroy');

        // تعليقات المشروع
        Route::post('/projects/{project}/comments', [ProjectManageController::class, 'commentStore'])->name('comments.store');
        Route::delete('/comments/{comment}', [ProjectManageController::class, 'commentDestroy'])->name('comments.destroy');

        // الموعد النهائي والتقييم
        Route::post('/projects/{project}/deadline', [ProjectManageController::class, 'deadlineUpdate'])->name('deadline.update');
        Route::post('/projects/{project}/evaluate', [ProjectManageController::class, 'evaluate'])->name('project.evaluate');

        // الملف الشخصي
        Route::get('/profile', [SupervisorProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [SupervisorProfileController::class, 'update'])->name('profile.update');
    });

    // تنزيل ملفات المشاريع (أدمن/مشرف المشروع/أعضاء الفريق)
    Route::get('/files/{file}/download', [FileController::class, 'download'])->name('files.download');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

});
