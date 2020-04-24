<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\Auth\LoginRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    /**
     * @var LoginRepository
     */
    protected $loginRepository;



    /**
     * LoginController constructor.
     *
     * @param LoginRepository     $loginRepository

     */
    public function __construct(LoginRepository $loginRepository)
    {
        // set the repository
        $this->loginRepository = $loginRepository;
    }

    /**
     * Handle the incoming login request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     * @throws Exception
     */
    public function login(Request $request): JsonResponse
    {

        $input = $this->validate($request, config('validation-rules.login'));

        $oUser = $this->loginRepository->process($input);

        return response()->json($oUser, $oUser['code']);
    }




    /**
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {

        try {
            User::authLogout();
            return response()->json(
                [
                    'code' => Response::HTTP_OK,
                    'message' => ('Logged out'),
                    'data' => null,
                ],
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json(
                [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => ('Already logged out'),
                    'data' => null,
                ],
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
