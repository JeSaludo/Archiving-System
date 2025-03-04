<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
class User extends Authenticatable implements CanResetPassword
{
    use HasFactory, Notifiable, CanResetPasswordTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'isAdmin',
        'employee_id',
        'otp',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function recentActivities(): HasMany
    {
        return $this->hasMany(RecentActivity::class);
    }

    public function sharedFiles()
    {
        return $this->hasManyThrough(File::class, FileShares::class, 'shared_with_user_id', 'id', 'id', 'file_id');
    }

    public function accessRequests()
    {
        return $this->hasMany(FileAccessRequests::class, 'requested_by_user_id');
    }

}
