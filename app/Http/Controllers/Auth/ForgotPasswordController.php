<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\Auth\ForgotPasswordRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /**
     * @var ForgotPasswordRepository
     */
    protected $repository;

    /**
     * ForgotPasswordController constructor.
     *
     * @param ForgotPasswordRepository $repository
     */
    public function __construct(ForgotPasswordRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     * @throws Exception
     */
    public function sendResetLinkEmail(Request $request): JsonResponse
    {
       // dd($request->all());
       // $input = $this->validate($request, config('validation-rules.forgot-password'));
        $data = $this->repository->process($request->all());
        return response()->json($data, $data['code']);
    }
}
