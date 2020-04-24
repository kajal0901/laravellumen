<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\Auth\ChangePasswordRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ChangePasswordController extends Controller
{
    /**
     * @var ChangePasswordRepository
     */
    protected $repository;

    /**
     * ChangePasswordController constructor.
     *
     * @param ChangePasswordRepository $repository
     */
    public function __construct(ChangePasswordRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function changePassword(Request $request): JsonResponse
    {

        $input = $this->validate($request, config('validation-rules.change-password'));

        $data = $this->repository->process($input);

        return $this->httpOk($data);
    }
}
