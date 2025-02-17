<?php

declare(strict_types=1);

namespace App\Auth;

use Brackets\AdminAuth\Notifications\ResetPassword;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use HasRoles;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @var array<int, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var array<int, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $appends = [
        'resource_url',
    ];

    public function getResourceUrlAttribute(): string
    {
        return url('/admin/users/' . $this->getKey());
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(app(ResetPassword::class, ['token' => $token]));
    }

    /**
     * @return array<string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'created_at' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'updated_at' => 'date:' . CarbonInterface::DEFAULT_TO_STRING_FORMAT,
            'password' => 'hashed',
        ];
    }
}
