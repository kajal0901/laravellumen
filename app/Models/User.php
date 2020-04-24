<?php

namespace App\Models;

use App\Events\Logout;
use App\Exceptions\RequiredParamsException;
use App\Mail\ResetPassword;
use App\Mail\WelcomeEmail;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Token;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements CanResetPasswordContract,
    AuthenticatableContract,
    AuthorizableContract,
    MustVerifyEmail
{
    use Authenticatable, Authorizable,HasApiTokens,HasRoles,Notifiable,CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password','remember_token',
    ];

    /**
     * @return User
     * @throws RequiredParamsException
     */
    public static function authUser(): User
    {
        if ($user = Auth::user()) {
            return $user;
        }
        throw new RequiredParamsException(__('Unauthorized'));
    }

    /**
     * The services that belong to users.
     */
    /**
     * @return User
     * @throws Exception
     */
    public static function authLogout(): User
    {
        $user = self::getLoggedInUser();
        $token = $user->token();
        $token->revoke();
        event(new Logout($user));
        return $user;
    }

    /**
     *
     */
    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * {@inheritDoc}
     */
    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * @inheritDoc
     */
    public function sendEmailVerificationNotification()
    {
        Mail::send(new WelcomeEmail($this));
    }

    /**
     * @inheritDoc
     */
    public function getEmailForVerification()
    {

    }

    /**
     * @return AuthenticatableContract
     */
    public static function getLoggedInUser(): AuthenticatableContract
    {
        if ($user = Auth::user()) {
            return $user;
        }
        abort(403, __('unauthorized'));
    }

    /**
     * Get the current access token being used by the user.
     *
     * @return Token|null
     */
    public function token()
    {
        return $this->accessToken;
    }
    /**
     * Notify to user
     *
     * @param $resetPassword
     *
     * @return void
     */
    final public function notify($resetPassword): void
    {
        Mail::send(new ResetPassword($this, $resetPassword));
    }
    /**
     * Save user password
     *
     * @param string $newPassword
     *
     * @return void
     */
    final public function savePassword(string $newPassword): void
    {
        $this->password = hashString($newPassword);
        $this->save();
    }

}

