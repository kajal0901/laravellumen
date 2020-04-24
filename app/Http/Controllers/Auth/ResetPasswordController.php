<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\Auth\ResetPasswordRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends Controller
{
    /**
     * @var ResetPasswordRepository
     */
    protected $repository;

    /**
     * ForgotPasswordController constructor.
     *
     * @param ResetPasswordRepository $repository
     */
    public function __construct(ResetPasswordRepository $repository)
    {

        $this->repository = $repository;
    }

    /**
     * Reset password
     * save new password
     *
     * @param Request $request
     * @param string  $token
     *
     * @return JsonResponse
     * @throws ValidationException|Exception
     */
    public function postReset(
        Request $request,
        string $token
    ): JsonResponse {

        $request->merge(['token' => $token]);
        $input = $this->validate($request, config('validation-rules.password-reset'));
        $data = $this->repository->process($input);
        return response()->json($data, $data['code']);
    }
}
