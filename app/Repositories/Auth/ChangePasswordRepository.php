<?php

namespace App\Repositories\Auth;

use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class ChangePasswordRepository
{
    /**
     * Send a reset link to the given user.
     *
     * @param array $input
     *
     * @return array
     */
    public function process(array $input): array
    {
        $oldPassword = $input['old_password'];
        $newPassword = $input['new_password'];

        $user = User::getLoggedInUser();

        if (!matchPassword($oldPassword, $user->getAuthPassword())) {
            return [
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => __('password_not_match_with_old_password'),
            ];
        }

        //Current password and new password is same
        if ($this->isSame($oldPassword, $newPassword)) {
            return [
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => __('password_not_match_with_new_password'),
            ];
        }

        $user->savePassword($newPassword);

        return [
            'code' => Response::HTTP_OK,
            'message' => __('password_changed_success'),
            'data' => null,
        ];
    }

    /**
     * @param string $prevPassword
     * @param string $newPassword
     *
     * @return bool
     */
    public function isSame(
        string $prevPassword,
        string $newPassword
    ): bool
    {
        return strcmp($prevPassword, $newPassword) === 0;
    }
}
