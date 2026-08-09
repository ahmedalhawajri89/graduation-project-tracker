<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Supervisor extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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

    ################# end relations

}
