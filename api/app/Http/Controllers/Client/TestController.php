<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\ProjectArchive;
use App\Models\Property;
use App\Service\MediaGroup;
use App\Service\RedStar;
use App\Service\Zip;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TestController extends Controller
{

    /**
     *
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function test()
    {

        echo 123;
        die();


        return $this->success($response);
    }





}
