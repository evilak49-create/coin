<?php
namespace App\Service;

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Illuminate\Support\Facades\Cache;

class Captcha
{
    /**
     * 获取验证码
     * @param $codeLength
     * @param $width
     * @param $height
     * @return array
     */
    public static function getCaptcha($codeLength,$width,$height) {
        // 包含哪些字符
        $chars = '13456789abcefghjkmnprstuvwxy';
        $builder = new PhraseBuilder($codeLength, $chars);
        $captcha = new CaptchaBuilder(null, $builder);
        $captcha->setIgnoreAllEffects(true);

        // 生成验证码
        $captcha->build($width, $height, $font = null);

        // base64 image
        $image = $captcha->inline();
        //uniqid
        $uniqid = uniqid().mt_rand(1000,9999);
        //value
        $value = $captcha->getPhrase();
        //json
        return ['code' => 0, 'image'=>$image,'captcha_key'=>$uniqid,'value'=>$value];
    }

    /**
     * 检查验证码是否正确
     */
    public static function checkCapt($captcha_key, $captcha_code) {
        //获取验证码
        $value = Cache::get($captcha_key);
        //删除验证码
        Cache::forget($captcha_key);
        //检查验证码是否正确
        if (strtolower($captcha_code) !== strtolower($value)) {
            return false;
        }

        return true;
    }
}
