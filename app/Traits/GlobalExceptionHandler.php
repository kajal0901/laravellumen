<?php

namespace App\Traits;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait GlobalExceptionHandler
{
    /**
     * Get error data by exception
     *
     * @param Exception $exception
     *
     * @return array
     */
    public function getErrorData(Exception $exception): array
    {
        $exceptionActions = [
            'QueryException' => function (QueryException $exception) {
                $code = $exception->getCode();
                $databaseMessageByCode = [
                    23000 => __('record_already_exists'),
                ];
                return [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => $databaseMessageByCode[$code] ?? 'Database error!',
                    'error_from' => self::UNIVERSE_CUSTOMER,
                ];
            },
            'ModelNotFoundException' => function (ModelNotFoundException $exception) {
                $modelName = last(explode('\\', $exception->getModel()));
                $ids = ' ' . implode(', ', $exception->getIds());
                return [
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => "{$modelName}{$ids} " . __('does_not_exists'),
                    'error_from' => self::UNIVERSE_CUSTOMER,
                ];
            },
            'ValidationException' => function (ValidationException $exception) {
                return [
                    'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' =>
                        collect($exception->errors())
                            ->flatten()
                            ->first() ?? $exception->getMessage(),
                    'errorMessages' => $exception->errors(),
                    'error_from' => self::UNIVERSE_CUSTOMER,
                ];
            },
            'NotFoundHttpException' => function (NotFoundHttpException $exception) {
                return [
                    'code' => $exception->getStatusCode(),
                    'message' => __('http_request_does_not_exist'),
                    'error_from' => self::UNIVERSE_CUSTOMER,
                ];
            },
            'InvalidArgumentException' => function (InvalidArgumentException $exception) {
                return [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => $exception->getMessage(),
                    'error_from' => self::UNIVERSE_CUSTOMER,
                ];
            },
            'OAuthServerException' => function ($exception) {
                $exceptionCode = $exception->getCode();
                $databaseMessageByCode = [
                    6 => __('invalid_credential'),
                    10 => __('invalid_credential'),
                ];
                return [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => $databaseMessageByCode[$exceptionCode],
                    'error_from' => self::UNIVERSE_CUSTOMER,
                ];
            },
            'EmailNotVerifiedException' => function ($exception) {
                return [
                    'code' => Response::HTTP_UNAUTHORIZED,
                    'message' => $exception->getMessage(),
                    'error_from' => self::UNIVERSE_CUSTOMER,
                ];
            },
            'Exception' => function (Exception $exception) {
                $exceptionCode = $exception->getCode() > 0
                    ? $exception->getCode()
                    : Response::HTTP_INTERNAL_SERVER_ERROR;

                return [
                    'code' => $exceptionCode,
                    'message' => $exception->getMessage() ?: __('Internal Server Error'),
                    'error_from' => self::UNIVERSE_CUSTOMER,
                ];
            },
        ];

        $className = (isset($exceptionActions[class_basename($exception)])) ? class_basename($exception) : 'Exception';

        $response = $exceptionActions[$className]($exception);
        if (config('app.env') == 'local') {
            $response = array_merge(
                $response, [
                    'message' => $exception->getMessage() ?? $exception->originalMessage,
                    '_exception' => class_basename($exception),
                    'line' => $exception->getLine(),
                    'file' => $exception->getFile(),
                ]
            );
        }

        return $response;
    }
}
