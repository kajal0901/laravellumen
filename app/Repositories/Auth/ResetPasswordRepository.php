<?php

namespace App\Repositories\Auth;

use App\Exceptions\RequiredParamsException;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;

class ResetPasswordRepository
{
    /**
     * @var string
     */
    protected $broker = 'users';

    /**
     * Process reset password request
     * TODO : Need to refactor
     * @param array $input
     *
     * @return array
     * @throws  Exception
     */
    public function process(array $input): array
    {
        $input['password_confirmation'] = $input['password'];
        $broker = $this->getBroker();
        $infoFromToken = explode('/', base64_decode($input['token']));
        if (count($infoFromToken) < 2) {
            throw new RequiredParamsException(
                __('invalid_token')
            );
        }
        $input['email'] = $infoFromToken[0];
        $input['token'] = $infoFromToken[1];
        $response = Password::broker($broker)->reset($input, function (
            User $user,
            string $password
        ) {
            $user->savePassword($password);
            return Password::PASSWORD_RESET;
        });
        switch ($response) {
            case Password::PASSWORD_RESET:
                return [
                    'code' => Response::HTTP_OK,
                    'message' => __('new_password_updated'),
                ];
            case Password::INVALID_TOKEN:
                return ['code' => Response::HTTP_BAD_REQUEST, 'message' => __('invalid_token')];
            default:
                return ['code' => Response::HTTP_BAD_REQUEST, 'message' => $response];
        }
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return string|null
     */
    public function getBroker(): ?string
    {
        return property_exists($this, 'broker') ? $this->broker : null;
    }
}
