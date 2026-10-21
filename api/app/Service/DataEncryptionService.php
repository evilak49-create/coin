<?php

namespace App\Service;

use Illuminate\Support\Facades\Log;

/**
 * 数据加密服务类
 * 使用 AES-256-GCM 算法进行数据加密/解密
 * 
 * 数据格式：
 * 客户端发送：base64(IV(12字节) + encrypted_data + tag(16字节))
 * 或者：{"data": "base64_encrypted", "iv": "base64_iv", "tag": "base64_tag", "timestamp": 1234567890}
 */
class DataEncryptionService
{
    /**
     * 加密算法
     */
    private const CIPHER = 'aes-256-gcm';

    /**
     * IV长度（GCM模式推荐12字节）
     */
    private const IV_LENGTH = 12;

    /**
     * 认证标签长度（GCM模式固定16字节）
     */
    private const TAG_LENGTH = 16;


    /**
     * 获取约定的盐值（客户端和服务端约定相同）
     * 
     * 约定方式：
     * 1. 优先使用配置的盐值
     * 2. 如果没有配置，使用APP_KEY（客户端和服务端都知道）
     * 3. 如果都没有，使用固定默认值（不推荐，仅用于开发）
     * 
     * @return string
     */
    private static function getServerSalt(): string
    {
        // 优先使用配置的盐值
        $salt = env('DATA_ENCRYPT_SALT');
        
        if (empty($salt)) {
            // 使用APP_KEY作为盐值（客户端和服务端都知道）
            $salt = env('APP_KEY', 'default-encrypt-salt-2024');
        }

        return $salt;
    }

    /**
     * 根据设备号派生加密密钥（约定算法）
     * 客户端和服务端使用相同的算法，无需密钥交换
     * 
     * 约定：
     * 1. 使用设备号 + 固定盐值派生密钥
     * 2. 盐值可以是固定的字符串（客户端和服务端都知道）
     * 3. 使用 PBKDF2-SHA256 算法
     * 
     * @param string $deviceNumber 设备号
     * @return string 32字节的加密密钥
     */
    private static function deriveKeyFromDevice(string $deviceNumber): string
    {
        // 固定盐值（客户端和服务端约定相同）
        // 可以从配置读取，如果没有配置则使用默认值
        $salt = env('DATA_ENCRYPT_SALT', 'default_encrypt_salt_2024');
        
        // 固定信息字符串（客户端和服务端约定相同）
        $info = 'data_encrypt_v1';
        
        // 使用PBKDF2-SHA256派生密钥（客户端和服务端使用相同算法）
        // 迭代次数：10000（客户端和服务端约定相同）
        $key = hash_pbkdf2('sha256', $deviceNumber, $salt . $info, 10000, 32, true);
        
        return $key;
    }

    /**
     * 获取加密密钥（约定固定密钥方式）
     * 
     * 约定方式：
     * 1. 客户端和服务端约定相同的固定密钥
     * 2. 密钥可以从配置读取，或使用APP_KEY派生
     * 3. 客户端不需要存储密钥，只需要知道约定的密钥值
     * 
     * @return string
     * @throws \Exception
     */
    private static function getEncryptionKey(): string
    {
        // 优先使用配置的固定密钥
        $key = env('DATA_ENCRYPT_KEY');
        
        if (empty($key)) {
            // 如果没有配置，使用APP_KEY派生（客户端和服务端都知道APP_KEY）
            $appKey = env('APP_KEY');
            if (!empty($appKey)) {
                // 从APP_KEY派生32字节密钥
                $key = substr(hash('sha256', $appKey . 'data_encrypt_v1', true), 0, 32);
            } else {
                throw new \Exception('DATA_ENCRYPT_KEY 或 APP_KEY 未配置');
            }
        }

        // 确保密钥长度为32字节（256位）
        if (strlen($key) !== 32) {
            // 如果密钥不是32字节，进行填充或截取
            if (strlen($key) < 32) {
                $key = str_pad($key, 32, '0', STR_PAD_RIGHT);
            } else {
                $key = substr($key, 0, 32);
            }
        }

        return $key;
    }

    /**
     * 生成随机IV
     * 
     * @return string
     */
    private static function generateIV(): string
    {
        return random_bytes(self::IV_LENGTH);
    }

    /**
     * 加密数据
     * 
     * @param string $plaintext 明文数据
     * @return string base64编码的加密数据（格式：base64(IV + encrypted_data + tag)）
     */
    public static function encrypt(string $plaintext): string
    {
        try {
            $key = self::getEncryptionKey();
            $iv = self::generateIV();

            // 执行加密
            $tag = '';
            $encrypted = openssl_encrypt(
                $plaintext,
                self::CIPHER,
                $key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag,
                '',
                self::TAG_LENGTH
            );

            if ($encrypted === false) {
                throw new \Exception('加密失败：' . openssl_error_string());
            }

            // 组合：IV + 加密数据 + 认证标签
            $combined = $iv . $encrypted . $tag;

            // Base64编码
            return base64_encode($combined);
        } catch (\Exception $e) {
            Log::error('数据加密失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('数据加密失败：' . $e->getMessage());
        }
    }

    /**
     * 解密数据
     * 
     * @param string $encryptedData base64编码的加密数据
     * @return string 解密后的明文数据
     * @throws \Exception
     */
    public static function decrypt(string $encryptedData): string
    {
        try {
            $key = self::getEncryptionKey();

            // Base64解码
            $combined = base64_decode($encryptedData, true);
            if ($combined === false) {
                throw new \Exception('Base64解码失败');
            }

            // 检查数据长度
            $minLength = self::IV_LENGTH + self::TAG_LENGTH;
            if (strlen($combined) < $minLength) {
                throw new \Exception('加密数据长度不足');
            }

            // 提取IV、加密数据和标签
            $iv = substr($combined, 0, self::IV_LENGTH);
            $tag = substr($combined, -self::TAG_LENGTH);
            $encrypted = substr($combined, self::IV_LENGTH, -self::TAG_LENGTH);

            // 执行解密
            $plaintext = openssl_decrypt(
                $encrypted,
                self::CIPHER,
                $key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag
            );

            if ($plaintext === false) {
                throw new \Exception('解密失败：数据可能被篡改或密钥错误');
            }

            return $plaintext;
        } catch (\Exception $e) {
            Log::error('数据解密失败', [
                'error' => $e->getMessage(),
                'data_length' => strlen($encryptedData)
            ]);
            throw new \Exception('数据解密失败：' . $e->getMessage());
        }
    }

    /**
     * 解密并解析JSON数据
     * 
     * @param string $encryptedData base64编码的加密数据
     * @return array 解析后的JSON数组
     * @throws \Exception
     */
    public static function decryptJson(string $encryptedData): array
    {
        // 解密
        $plaintext = self::decrypt($encryptedData);

        // 解析JSON
        $data = json_decode($plaintext, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSON格式错误：' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * 智能解密：基于约定的固定密钥方案
     * 
     * 约定方式（最简单）：
     * 1. 客户端和服务端约定相同的固定密钥
     * 2. 客户端使用固定密钥加密数据（设备号在加密数据中）
     * 3. 服务端使用相同密钥解密
     * 
     * 优点：
     * - 实现最简单
     * - 不需要密钥交换接口
     * - 不需要设备号派生
     * - 设备号在加密数据中，不在URL中
     * - 不需要时间戳
     * 
     * @param string $data 加密数据或base64数据
     * @return array 解析后的数据
     */
    public static function decryptOrDecodeBase64(string $data): array
    {
        // 方案1：尝试使用固定密钥解密（约定方式）
        try {
            return self::decryptJson($data);
        } catch (\Exception $e) {
            Log::info('固定密钥解密失败，尝试base64解码', [
                'error' => $e->getMessage()
            ]);
        }

        // 方案2：尝试base64解码（兼容最旧版本）
        try {
            $decoded = base64_decode($data, true);
            if ($decoded === false) {
                throw new \Exception('Base64解码失败');
            }

            $jsonData = json_decode($decoded, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON格式错误：' . json_last_error_msg());
            }

            return $jsonData;
        } catch (\Exception $e) {
            throw new \Exception('数据格式错误：既不是有效的加密数据，也不是有效的base64数据。' . $e->getMessage());
        }
    }





    /*
    * 自定义加密函数（基于 Base64 并打乱）
    // 测试：
    $original = "Hello Android Client!";
    $encrypted = custom_encrypt($original);
    $decrypted = custom_decrypt($encrypted);

    echo "原文: $original\n";
    echo "加密后: $encrypted\n";
    echo "解密后: $decrypted\n";
    */
    public static function custom_encrypt($text) {
        $key = config('app.data_encrypt_key');
        
        // 检查密钥是否为空
        if (empty($key)) {
            throw new \Exception('data_encrypt_key 未配置');
        }
        
        $keyLength = strlen($key);
        if ($keyLength === 0) {
            throw new \Exception('data_encrypt_key 不能为空');
        }
       
        // 1. 原始文本加密成 Base64
       $base64 = base64_encode($text);
   
       // 2. 用 key 进行简单异或混淆
       $xor = '';
       for ($i = 0; $i < strlen($base64); $i++) {
           $xor .= chr(ord($base64[$i]) ^ ord($key[$i % $keyLength]));
       }
   
       // 3. 再次 base64 编码以保证可传输性
       $encoded = base64_encode($xor);
   
       // 4. 自定义打乱规则（顺序可自定义）
       $encoded = strtr($encoded, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/', 
                                            'QWELZXCPASDFVBfdsamNMpoiuytrewqlRTYUIOGHJKkjhgnbvcxz9870654321+/');
   
       return $encoded;
   }
   
   /*
    * 自定义解密函数（逆过程）
    */
    public static function custom_decrypt($encoded, $checkbase64 = true) {
        $key = config('app.data_encrypt_key');
        
        // 检查密钥是否为空
        if (empty($key)) {
            throw new \Exception('data_encrypt_key 未配置');
        }
        
        // 保存原始值，用于方案2的回退
        $originalEncoded = $encoded;

        // 方案1：尝试使用固定密钥解密（约定方式）
        try {
            $keyLength = strlen($key);
            if ($keyLength === 0) {
                throw new \Exception('data_encrypt_key 不能为空');
            }
        
            // 1. 恢复 Base64 原顺序
            $encoded = strtr($encoded, 
                                'QWELZXCPASDFVBfdsamNMpoiuytrewqlRTYUIOGHJKkjhgnbvcxz9870654321+/', 
                                'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/');
            // 2. Base64 解码一次
            $xor = base64_decode($encoded, true);
            if ($xor === false) {
                throw new \Exception('Base64解码失败');
            }
            // 3. 再 XOR 解密
            $base64 = '';
            for ($i = 0; $i < strlen($xor); $i++) {
                $base64 .= chr(ord($xor[$i]) ^ ord($key[$i % $keyLength]));
            }
        
            $decoded = base64_decode($base64, true);
            
            if ($decoded === false) {
                throw new \Exception('Base64解码失败');
            }

            $jsonData = json_decode($decoded, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON格式错误：' . json_last_error_msg());
            }
            return $jsonData;
        } catch (\Exception $e) {
            //Log::info('固定密钥解密失败，尝试base64解码', [
            //    'error' => $e->getMessage()
            ///]);
        }


        // 方案2：尝试base64解码（兼容最旧版本）
        if($checkbase64){
            try {
                // 使用原始值进行base64解码
                $decoded = base64_decode($originalEncoded, true);
                if ($decoded === false) {
                    throw new \Exception('Base64解码失败');
                }
                $jsonData = json_decode($decoded, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('JSON格式错误：' . json_last_error_msg());
                }
    
                return $jsonData;
            } catch (\Exception $e) {
                throw new \Exception('数据格式错误：既不是有效的加密数据，也不是有效的base64数据。' . $e->getMessage());
            }
        }
        
        throw new \Exception('数据解密失败：无法识别数据格式');
   }






}

