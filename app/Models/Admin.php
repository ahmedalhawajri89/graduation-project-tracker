<?php

namespace App\Models;

use App\Models\Concerns\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasAvatar, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
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

    /** الرابط يحمل الدور — انظر Student::sendPasswordResetNotification */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotify($token, 'admin'));
    }

    ################# relations

    public function students()
    {
        return $this->hasMany(Student::class, 'admin_id', 'id');
        #relation is one to many
    }
    public function supervisors()
    {
        return $this->hasMany(Supervisor::class, 'admin_id', 'id');
        #relation is one to many

    }
    ################# end relations

}
