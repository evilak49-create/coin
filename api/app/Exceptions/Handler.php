<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
//        return parent::render($request, $exception);
        if ($exception instanceof ApiException) {
            $code = $exception->getCode();
            Log::error('错误信息', ['msg' => $exception->getMessage(), 'exception' => $exception]);
            return response()->json([
                'code' => $code ?? 0,
//                'msg'  => getMsg($code) ?? (config('app.debug') ? $exception->getMessage() : '失败'),
                'msg'  => trans('error.'.$code) ?? (config('app.debug') ? $exception->getMessage() : '失败'),
                'data' => null
            ]);
        }
        if (Str::lower($request->segment(1)) === 'api') {
            if ($exception instanceof ValidationException) {
                return response()->json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'msg' => $exception->validator->errors()->first(),
                ], Response::HTTP_BAD_REQUEST);
            } elseif ($exception instanceof AuthenticationException) {
                return response()->json([
                    'code' => Response::HTTP_UNAUTHORIZED,
                    'msg' => $exception->getMessage(),
                ], Response::HTTP_UNAUTHORIZED);
            } elseif ($exception instanceof AuthorizationException) {
                return response()->json([
                    'code' => Response::HTTP_FORBIDDEN,
                    'msg' => $exception->getMessage(),
                ], Response::HTTP_UNAUTHORIZED);
            } elseif ($exception instanceof HttpException) {
                return response()->json([
                    'code' => $exception->getCode(),
                    'msg' => $exception->getMessage(),
                ], $exception->getStatusCode());
            } elseif ($exception instanceof UnauthorizedHttpException) {
                return response()->json([
                    'code' => Response::HTTP_UNAUTHORIZED,
                    'msg' => 'Please login again',
                ], Response::HTTP_OK);
            } elseif ($exception instanceof TokenBlacklistedException) {
                return response()->json([
                    'code' => Response::HTTP_UNAUTHORIZED,
                    'msg' => 'Please login again',
                ], Response::HTTP_OK);
            } elseif ($exception instanceof NotFoundHttpException) {
                return response()->json([
                    'code' => Response::HTTP_NOT_FOUND,
                    'msg' => 'Not Found'
                ], Response::HTTP_NOT_FOUND);
            } elseif ($exception instanceof ModelNotFoundException) {
                return response()->json([
                    'code' => Response::HTTP_NOT_FOUND,
                    'msg' => 'No query results'
                ], Response::HTTP_NOT_FOUND);
            } else {
                return parent::render($request, $exception);
            }
        } else {
            return parent::render($request, $exception);
        }
    }
}
