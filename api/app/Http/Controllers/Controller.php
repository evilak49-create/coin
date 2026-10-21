<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 成功响应
     * @param $data
     * @param int $httpCode
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function success($data = null, $httpCode = Response::HTTP_OK)
    {
        return response()->json([
            'code' => 0,
            'msg' => '',
            'data' => $data,
            'now' => Carbon::now()->timestamp
        ])->setStatusCode($httpCode);
        /*
            ->withHeaders([
                'Cache-Control' => 'no-store',
                'Strict-Transport-Security' => 'max-age=31536000',
                'X-XSS-Protection' => '1',
                'X-Frame-Options' => 'SAMEORIGIN',
                'Content-Security-Policy' => "script-src 'self' 'unsafe-inline' 'unsafe-eval' blob: data: ;",
                'X-Content-Type-Options' => 'nosniff '
            ]);
            */
    }

    public function fail($code, $msg = '')
    {
        return response()->json([
            'code' => $code,
            'msg' => $msg ?: trans('error.'.$code),
            'now' => Carbon::now()->timestamp
        ])->setStatusCode(Response::HTTP_OK);
    }

    protected function paging($page, $limit): int
    {
        $page = $page - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        return $page;
    }

    public function errorResponse($statusCode, $message = null, $code = 0)
    {
        throw new HttpException($statusCode, $message, null, [], $code);
    }

    public function paginate($list)
    {
        $paginate['current_page'] = $list->currentPage();
        $paginate['last_page'] = $list->lastPage();
        $paginate['per_page'] = (int) $list->perPage();
        $paginate['total'] = $list->total();

        return $paginate;
    }
}
