<?php

use Illuminate\Support\Str;

if (! function_exists('formatName')) {
    /**
     * Translate the given message.
     *
     * @param  string|null  $key
     * @param  array  $replace
     * @param  string|null  $locale
     * @return \Illuminate\Contracts\Translation\Translator|string|array|null
     */
    function formatName($name)
    {

        if(Str::length($name) == 2)
        {
            $namearr = array_map(function ($i) use ($name) {
                return mb_substr($name, $i, 1);
            }, range(0, mb_strlen($name) - 1));
            $name =  $namearr[0]."    ".$namearr[1];
        }
        return $name;
    }
}

function lastStr($string,$str1,$str2){

    $last_pos = strrpos($string, $str1);

    if ($last_pos !== false) {
        // 替换最后一个中文分号
        $str = substr_replace($string, $str2, $last_pos, 3); // 中文分号占3个字节
    }

    return $str;

    /*
        $lastPart = strrchr($string, $str1); // 注意：如果你的环境使用的是英文分号，这里应该用英文分号 ';'

        // 如果找到了最后一个中文分号
        if ($lastPart !== false) {
            // 使用 str_replace 替换这部分字符串
            $newString = str_replace($lastPart, $str2, $string);
        } else {
            // 如果没有找到中文分号，则字符串不变
            $newString = $string;
        }

        return $newString;
    */
}


/**
 * 百分比小数合计差额调整算法
 *
 * 当多个百分比值四舍五入后可能导致总和不等于100%
 * 此算法通过调整最大舍入误差项来确保总和为100%
 */

/**
 * 调整百分比数组使其总和为100%
 *
 * @param array $percentages 原始百分比数组(小数形式，如0.3256表示32.56%)
 * @param int $precision 保留小数位数
 * @return array 调整后的百分比数组
 *
 *
 * function example()
{
// 示例1: 三个百分比，总和略大于100%的情况
$example1 = [0.3333, 0.3333, 0.3334]; // 理论上总和为1
$adjusted1 = adjustPercentages($example1, 2);

echo "示例1 - 三个接近相等的值:\n";
echo "原始值: " . implode(", ", array_map(function ($v) {
return number_format($v, 4);
}, $example1)) . "\n";
echo "调整后: " . implode(", ", array_map(function ($v) {
return number_format($v, 2);
}, $adjusted1)) . "\n";
echo "调整后总和: " . number_format(array_sum($adjusted1), 2) . "\n\n";

// 示例2: 多个不同百分比，可能出现四舍五入问题
$example2 = [0.2547, 0.1833, 0.4328, 0.1292];
$adjusted2 = adjustPercentages($example2, 2);

echo "示例2 - 多个不同的值:\n";
echo "原始值: " . implode(", ", array_map(function ($v) {
return number_format($v, 4);
}, $example2)) . "\n";
echo "调整后: " . implode(", ", array_map(function ($v) {
return number_format($v, 2);
}, $adjusted2)) . "\n";
echo "调整后总和: " . number_format(array_sum($adjusted2), 2) . "\n\n";

// 示例3: 极端情况，很多小数值
$example3 = [0.0101, 0.0299, 0.1234, 0.2766, 0.1843, 0.0823, 0.2934];
$adjusted3 = adjustPercentages($example3, 2);

echo "示例3 - 多个小数值:\n";
echo "原始值: " . implode(", ", array_map(function ($v) {
return number_format($v, 4);
}, $example3)) . "\n";
echo "调整后: " . implode(", ", array_map(function ($v) {
return number_format($v, 2);
}, $adjusted3)) . "\n";
echo "调整后总和: " . number_format(array_sum($adjusted3), 2) . "\n";
}

// 运行示例
example();
 *
 */
function adjustPercentages(array $percentages, int $precision = 2): array
{
    // 检查输入数据
    if (empty($percentages)) {
        return [];
    }

    // 计算原始总和
    $originalSum = array_sum($percentages);

    // 如果原始总和不是1(100%)，先进行归一化处理
    if (abs($originalSum - 1) > 0.0000001) {
        foreach ($percentages as &$value) {
            $value = $value / $originalSum;
        }
    }

    // 四舍五入到指定小数位
    $roundedPercentages = array_map(function ($value) use ($precision) {
        return round($value, $precision);
    }, $percentages);

    // 计算四舍五入后的总和
    $roundedSum = array_sum($roundedPercentages);

    // 计算与期望值1(100%)的差距
    $diff = round($roundedSum - 1, $precision);

    // 如果有差距，需要调整
    if ($diff != 0) {
        // 计算每个值的舍入误差
        $errors = [];
        foreach ($percentages as $key => $original) {
            $rounded = $roundedPercentages[$key];
            $errors[$key] = $original - $rounded;
        }

        // 根据差距的正负选择调整方向
        if ($diff > 0) {
            // 总和大于1，需要减少某一项
            // 找出舍入误差最大的项(被向上舍入最多的项)
            asort($errors);
            $adjustKey = key($errors);
            $roundedPercentages[$adjustKey] -= $diff;
        } else {
            // 总和小于1，需要增加某一项
            // 找出舍入误差最小的项(被向下舍入最多的项)
            arsort($errors);
            $adjustKey = key($errors);
            $roundedPercentages[$adjustKey] -= $diff; // 注意这里是减去负数，相当于加上差值
        }
    }

    $roundedPercentages = array_map(function ($value) use ($precision) {
        return  (float)round($value *100, $precision-2);
    }, $roundedPercentages);

    return $roundedPercentages;
}



function getIp2()
{
    // 优先使用 $_SERVER，因为 getenv() 在某些环境下可能无法正确获取HTTP头
    $ip = null;
    
    // 检查各种可能的HTTP头（按优先级排序）
    $headers = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'HTTP_CF_CONNECTING_IP', // Cloudflare
        'HTTP_X_REAL_IP', // Nginx proxy
    ];
    
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];
            break;
        }
    }
    
    // 如果都没有，使用 REMOTE_ADDR
    if (empty($ip)) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    }
    
    if (empty($ip)) {
        return null;
    }
    
    // 如果IP是逗号分隔的列表（多个代理），取第一个（真实客户端IP）
    $ip = explode(',', $ip)[0];
    $ip = trim($ip);
    
    // 验证IP格式
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return $ip;
    }
    
    // 如果是私有IP或保留IP，也返回（可能是内网访问）
    if (filter_var($ip, FILTER_VALIDATE_IP)) {
        return $ip;
    }
    
    return null;
}

/**
 * 从Laravel Request对象获取真实IP（改进版）
 * 优先从HTTP头获取，因为CDN和反向代理会将真实IP放在HTTP头中
 * @param \Illuminate\Http\Request|null $request
 * @return string|null
 */
function getRealIp($request = null)
{
    if ($request instanceof \Illuminate\Http\Request) {
        // 优先检查各种HTTP头（CDN和反向代理通常会将真实IP放在这些头中）
        $headers = [
            'X-Forwarded-For',
            'X-Real-Ip',
            'X-Client-Ip',
            'CF-Connecting-Ip', // Cloudflare
            'Client-Ip',
            'Forwarded-For',
            'Forwarded',
        ];
        
        foreach ($headers as $header) {
            $headerValue = $request->header($header);
            if (!empty($headerValue)) {
                $ip = explode(',', $headerValue)[0];
                $ip = trim($ip);
                // 验证IP格式
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        // 如果HTTP头都没有，尝试从 $_SERVER 获取
        $ip = getIp2();
        if (!empty($ip)) {
            return $ip;
        }
        
        // 最后尝试从Request对象获取（如果配置了TrustProxies）
        $ip = $request->ip();
        if (!empty($ip) && filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
        
        return null;
    }
    
    // 如果没有传入Request对象，使用原来的方法
    return getIp2();
}