<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

class Controller extends BaseController
{
    public function httpOk(array $response = []): JsonResponse
    {
        if (!isset($response['code'])) {
            $response['code'] = Response::HTTP_OK;
        }

        if (!isset($response['message'])) {
            $response['message'] = __('success');
        }

        if (!isset($response['data'])) {
            $response['data'] = [];
        }

        return response()->json($response, $response['code']);
    }
    /**
     * @param  array $data
     * @return JsonResponse
     */
    public function httpCreated(array $data): JsonResponse
    {
        $data['code'] = Response::HTTP_CREATED;
        return response()->json($data, Response::HTTP_CREATED);
    }
}
