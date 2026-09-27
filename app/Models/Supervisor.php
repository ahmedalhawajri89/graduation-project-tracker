<?php

namespace App\Models;

use App\Models\Concerns\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Supervisor extends Authenticatable
{
    use HasApiTokens, HasAvatar, HasFactory, Notifiable;

    protected $fillable = [
        'university_id',
        'name',
        'email',
        'password',
        'phone',
        'specialize_id',
        'admin_id',
        'gender',
        'max_group',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /** الرابط يحمل الدور — انظر Student::sendPasswordResetNotification */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotify($token, 'supervisor'));
    }

    ################# relations

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
        #relation is one to many

    }
    public function specialize()
    {
        return $this->belongsTo(Specialize::class, 'specialize_id', 'id')
            ->withDefault([
                'name' => '',
            ]);
        #relation is one to many

    }
    public function projects()
    {
        return $this->hasMany(Project::class, 'supervisor_id', 'id');
        #relation is one to many

    }
    public function projectsAccept()
    {
        return $this->hasMany(Project::class, 'supervisor_id', 'id')
            ->whereIn('status', ['accept', 'complete']);
        #relation is one to many

    }

    /**
     * طلبات الإشراف المعلّقة — من حالة المشروع لا من الإشعار.
     *
     * كانت الصفحة تقرؤها من إشعارات غير مقروءة: إشعار يُعلَّم مقروءاً
     * لأي سبب يُخفي الطلب وزرّيه والمشروع ما زال معلّقاً، فلا يُقبل ولا يُرفض.
     */
    public function pendingRequests()
    {
        return $this->hasMany(Project::class, 'supervisor_id', 'id')
            ->where('status', 'request');
    }

    /** خطة المراحل — في فصل بعينه */
    public function stages()
    {
        return $this->hasMany(SupervisorStage::class)
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->orderBy('id');
    }

    ################# end relations

    /** مقاعد الفصل الحالي المتبقية — سالبة إن تجاوز الحدّ (يعيّنه الأدمن فوقه أحياناً) */
    public function seatsLeft(): int
    {
        $taken = $this->projectsAccept()->where('semester_id', Semester::current()?->id)->count();

        return (int) $this->max_group - $taken;
    }

    /**
     * عبء الإشراف: عدد مجموعات المشرف القائمة في فصل بعينه.
     *
     * استعلام مرتبط بالصفّ، يُستعمل في \u200EwhereRaw\u200E للتصفية على العبء —
     * وهي مقارنة بعمود (\u200Emax_group\u200E) لا بثابت، فلا تصلح لها \u200EwithCount\u200E
     * وحدها. مُعرَّف هنا مرّة واحدة ليستعمله المتحكّم والتصدير معاً،
     * فلا ينحرف تعريف «العبء» بين شاشة وكشف.
     *
     * \u200Edeleted_at is null\u200E ضرورية: المشاريع صار لها حذف ناعم، والصفّ
     * المحذوف كان سيُحسب عبئاً على مشرف لم يعد يشرف عليه.
     */
    public static function loadExpression(): string
    {
        return '(select count(*) from projects
                 where projects.supervisor_id = supervisors.id
                   and projects.deleted_at is null
                   and projects.status in ("accept", "complete")
                   and projects.semester_id = ?)';
    }

    /** \u200Efree\u200E دون الحدّ، \u200Efull\u200E عنده، \u200Eover\u200E فوقه */
    public static function loadOperator(string $bucket): string
    {
        return match ($bucket) {
            'full' => '=',
            'over' => '>',
            default => '<',
        };
    }
}
