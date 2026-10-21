<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Service\Captcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

//验证码控制器
class VerificationCodesController extends Controller
{

    /**
     * 图形验证码
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function captcha()
    {
        $captArr = Captcha::getCaptcha(4,146,40);

        //缓存验证码 5 分钟过期。
        $expiredAt = now()->addMinutes(5);
        Cache::put($captArr['captcha_key'], $captArr['value'], $expiredAt);

        $ret = [
            'image'=>$captArr['image'],
            'captcha_key'=>$captArr['captcha_key'],
        ];

        return $this->success($ret);
    }
}
