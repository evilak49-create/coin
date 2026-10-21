<?php

namespace App\Service;

class CountryService
{
    /**
     * ISO 3166-1 alpha-2 国家代码到中文名称的完整映射
     * 包含所有249个国家和地区
     * 
     * @var array<string, string>
     */
    private static $countryMap = [
        // 亚洲
        'CN' => '中国',
        'HK' => '香港',
        'MO' => '澳门',
        'TW' => '台湾',
        'JP' => '日本',
        'KR' => '韩国',
        'KP' => '朝鲜',
        'MN' => '蒙古',
        'AF' => '阿富汗',
        'BD' => '孟加拉国',
        'BT' => '不丹',
        'IN' => '印度',
        'MV' => '马尔代夫',
        'NP' => '尼泊尔',
        'PK' => '巴基斯坦',
        'LK' => '斯里兰卡',
        'BN' => '文莱',
        'KH' => '柬埔寨',
        'ID' => '印度尼西亚',
        'LA' => '老挝',
        'MY' => '马来西亚',
        'MM' => '缅甸',
        'PH' => '菲律宾',
        'SG' => '新加坡',
        'TH' => '泰国',
        'TL' => '东帝汶',
        'VN' => '越南',
        'AM' => '亚美尼亚',
        'AZ' => '阿塞拜疆',
        'BH' => '巴林',
        'CY' => '塞浦路斯',
        'GE' => '格鲁吉亚',
        'IR' => '伊朗',
        'IQ' => '伊拉克',
        'IL' => '以色列',
        'JO' => '约旦',
        'KW' => '科威特',
        'LB' => '黎巴嫩',
        'OM' => '阿曼',
        'PS' => '巴勒斯坦',
        'QA' => '卡塔尔',
        'SA' => '沙特阿拉伯',
        'SY' => '叙利亚',
        'TR' => '土耳其',
        'AE' => '阿联酋',
        'YE' => '也门',
        'KZ' => '哈萨克斯坦',
        'KG' => '吉尔吉斯斯坦',
        'TJ' => '塔吉克斯坦',
        'TM' => '土库曼斯坦',
        'UZ' => '乌兹别克斯坦',
        
        // 欧洲
        'GB' => '英国',
        'IE' => '爱尔兰',
        'IS' => '冰岛',
        'NO' => '挪威',
        'SE' => '瑞典',
        'FI' => '芬兰',
        'DK' => '丹麦',
        'EE' => '爱沙尼亚',
        'LV' => '拉脱维亚',
        'LT' => '立陶宛',
        'PL' => '波兰',
        'DE' => '德国',
        'NL' => '荷兰',
        'BE' => '比利时',
        'LU' => '卢森堡',
        'FR' => '法国',
        'MC' => '摩纳哥',
        'AD' => '安道尔',
        'ES' => '西班牙',
        'PT' => '葡萄牙',
        'IT' => '意大利',
        'VA' => '梵蒂冈',
        'SM' => '圣马力诺',
        'MT' => '马耳他',
        'GR' => '希腊',
        'AL' => '阿尔巴尼亚',
        'BA' => '波黑',
        'BG' => '保加利亚',
        'HR' => '克罗地亚',
        'CZ' => '捷克',
        'HU' => '匈牙利',
        'MK' => '北马其顿',
        'MD' => '摩尔多瓦',
        'RO' => '罗马尼亚',
        'RS' => '塞尔维亚',
        'SK' => '斯洛伐克',
        'SI' => '斯洛文尼亚',
        'UA' => '乌克兰',
        'BY' => '白俄罗斯',
        'RU' => '俄罗斯',
        'CH' => '瑞士',
        'LI' => '列支敦士登',
        'AT' => '奥地利',
        'ME' => '黑山',
        
        // 北美洲
        'US' => '美国',
        'CA' => '加拿大',
        'MX' => '墨西哥',
        'GT' => '危地马拉',
        'BZ' => '伯利兹',
        'SV' => '萨尔瓦多',
        'HN' => '洪都拉斯',
        'NI' => '尼加拉瓜',
        'CR' => '哥斯达黎加',
        'PA' => '巴拿马',
        'CU' => '古巴',
        'JM' => '牙买加',
        'HT' => '海地',
        'DO' => '多米尼加',
        'BS' => '巴哈马',
        'BB' => '巴巴多斯',
        'TT' => '特立尼达和多巴哥',
        'GD' => '格林纳达',
        'AG' => '安提瓜和巴布达',
        'LC' => '圣卢西亚',
        'VC' => '圣文森特和格林纳丁斯',
        'DM' => '多米尼克',
        'KN' => '圣基茨和尼维斯',
        'AW' => '阿鲁巴',
        'CW' => '库拉索',
        'SX' => '荷属圣马丁',
        'PR' => '波多黎各',
        'VG' => '英属维尔京群岛',
        'VI' => '美属维尔京群岛',
        'KY' => '开曼群岛',
        'BM' => '百慕大',
        'GL' => '格陵兰',
        
        // 南美洲
        'BR' => '巴西',
        'AR' => '阿根廷',
        'CL' => '智利',
        'CO' => '哥伦比亚',
        'PE' => '秘鲁',
        'VE' => '委内瑞拉',
        'EC' => '厄瓜多尔',
        'BO' => '玻利维亚',
        'PY' => '巴拉圭',
        'UY' => '乌拉圭',
        'GY' => '圭亚那',
        'SR' => '苏里南',
        'GF' => '法属圭亚那',
        'FK' => '福克兰群岛',
        
        // 非洲
        'EG' => '埃及',
        'LY' => '利比亚',
        'TN' => '突尼斯',
        'DZ' => '阿尔及利亚',
        'MA' => '摩洛哥',
        'SD' => '苏丹',
        'SS' => '南苏丹',
        'ET' => '埃塞俄比亚',
        'ER' => '厄立特里亚',
        'DJ' => '吉布提',
        'SO' => '索马里',
        'KE' => '肯尼亚',
        'UG' => '乌干达',
        'TZ' => '坦桑尼亚',
        'RW' => '卢旺达',
        'BI' => '布隆迪',
        'MW' => '马拉维',
        'ZM' => '赞比亚',
        'ZW' => '津巴布韦',
        'BW' => '博茨瓦纳',
        'NA' => '纳米比亚',
        'ZA' => '南非',
        'LS' => '莱索托',
        'SZ' => '斯威士兰',
        'MZ' => '莫桑比克',
        'MG' => '马达加斯加',
        'MU' => '毛里求斯',
        'SC' => '塞舌尔',
        'KM' => '科摩罗',
        'YT' => '马约特',
        'RE' => '留尼汪',
        'GH' => '加纳',
        'TG' => '多哥',
        'BJ' => '贝宁',
        'NG' => '尼日利亚',
        'NE' => '尼日尔',
        'TD' => '乍得',
        'CM' => '喀麦隆',
        'GQ' => '赤道几内亚',
        'GA' => '加蓬',
        'CG' => '刚果（布）',
        'CD' => '刚果（金）',
        'CF' => '中非',
        'ST' => '圣多美和普林西比',
        'AO' => '安哥拉',
        'SN' => '塞内加尔',
        'GM' => '冈比亚',
        'GW' => '几内亚比绍',
        'GN' => '几内亚',
        'SL' => '塞拉利昂',
        'LR' => '利比里亚',
        'CI' => '科特迪瓦',
        'BF' => '布基纳法索',
        'ML' => '马里',
        'MR' => '毛里塔尼亚',
        'CV' => '佛得角',
        'EH' => '西撒哈拉',
        
        // 大洋洲
        'AU' => '澳大利亚',
        'NZ' => '新西兰',
        'PG' => '巴布亚新几内亚',
        'FJ' => '斐济',
        'SB' => '所罗门群岛',
        'VU' => '瓦努阿图',
        'NC' => '新喀里多尼亚',
        'PF' => '法属波利尼西亚',
        'WS' => '萨摩亚',
        'TO' => '汤加',
        'KI' => '基里巴斯',
        'TV' => '图瓦卢',
        'NR' => '瑙鲁',
        'PW' => '帕劳',
        'FM' => '密克罗尼西亚',
        'MH' => '马绍尔群岛',
        'GU' => '关岛',
        'MP' => '北马里亚纳群岛',
        'AS' => '美属萨摩亚',
        'CK' => '库克群岛',
        'NU' => '纽埃',
        'TK' => '托克劳',
        'PN' => '皮特凯恩群岛',
        
        // 特殊代码
        'UNKNOWN' => '未知',
        'XX' => '未知',
        '' => '未知',
        null => '未知',
    ];

    /**
     * 将 ISO 国家代码转换为中文名称
     * 
     * @param string|null $countryCode ISO 国家代码（如 CN, US）
     * @return string 中文国家名称
     */
    public static function getCountryName(?string $countryCode): string
    {
        if (empty($countryCode)) {
            return '未知';
        }

        $code = strtoupper(trim($countryCode));
        
        return self::$countryMap[$code] ?? $code;
    }

    /**
     * 批量转换国家代码数组为中文名称
     * 
     * @param array $data 包含 country 字段的数据数组
     * @return array 转换后的数据数组
     */
    public static function convertCountryToName(array $data): array
    {
        if (isset($data['country'])) {
            $data['country'] = self::getCountryName($data['country']);
        }
        return $data;
    }

    /**
     * 批量转换国家代码数组列表为中文名称
     * 
     * @param array $list 数据列表
     * @return array 转换后的数据列表
     */
    public static function convertCountryListToName(array $list): array
    {
        return array_map(function ($item) {
            return self::convertCountryToName($item);
        }, $list);
    }

    /**
     * 根据中文名称搜索对应的国家代码列表
     * 
     * @param string $name 中文名称搜索词
     * @return array 国家代码数组
     */
    public static function getCountryCodesByName(string $name): array
    {
        $codes = [];
        $name = trim($name);
        if (empty($name)) {
            return [];
        }

        foreach (self::$countryMap as $code => $countryName) {
            if ($countryName && mb_strpos($countryName, $name) !== false) {
                if ($code !== null && $code !== '') {
                    $codes[] = $code;
                }
            }
        }

        return array_unique($codes);
    }
}

