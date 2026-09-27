<?php

namespace App\Models;

use App\Models\Concerns\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    use HasApiTokens, HasAvatar, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'university_id',
        'email',
        'password',
        'phone',
        'specialize_id',
        'admin_id',
        'gender',
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

    /**
     * الطلاب المتاحون للانضمام إلى فريق.
     *
     * كان التعريف مكتوباً في ثلاثة مواضع (لوحة الأدمن، لوحة الطالب،
     * نقطة البحث) — وأي إصلاح يُنسى في واحدة. وقد وقع ذلك فعلاً:
     * \u200EwhereHas('project')\u200E يحترم النطاق العام للحذف الناعم، فمشروعٌ
     * محذوف لا يُحتسب، فيظهر طالبه متاحاً — ويصير في فريقين إن
     * استُرجع مشروعه الأول.
     */
    public function scopeAvailableForTeam($query, $specializeId, $exceptStudentId = null)
    {
        return $query
            ->where('specialize_id', $specializeId)
            ->when($exceptStudentId, fn ($q) => $q->where('id', '!=', $exceptStudentId))
            ->whereDoesntHave('groups', function ($q) {
                $q->whereHas('project', function ($q) {
                    $q->withTrashed()->where('status', '!=', 'reject');
                });
            });
    }

    /** بحث المنتقي: بالاسم أو بالرقم الجامعي */
    public function scopeMatching($query, ?string $term)
    {
        $term = trim((string) $term);

        return $query->when($term !== '', function ($q) use ($term) {
            $q->where(function ($w) use ($term) {
                $w->where('name', 'like', "%{$term}%")
                    ->orWhere('university_id', 'like', "%{$term}%");
            });
        });
    }

    /** الرابط يحمل الدور: النظام ثلاثة حُرّاس، ولا يعرف نموذج إعادة
     *  التعيين أي وسيط كلمات مرور يستعمل بدونه */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotify($token, 'student'));
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

    public function groups()
    {
        return $this->hasMany(Group::class, 'student_id', 'id')
            ->latest();
    }
    #relation is one to many

    ################# end relations

}
