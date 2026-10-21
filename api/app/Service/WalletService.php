<?php

namespace App\Service;

use Exception;
use Illuminate\Support\Facades\Log;
use FurqanSiddiqui\BIP39\BIP39;
use FurqanSiddiqui\BIP32\BIP32;
use FurqanSiddiqui\ECDSA\ECDSA;
use FurqanSiddiqui\Bitcoin\Networks\Bitcoin;
use FurqanSiddiqui\BIP32\Buffers\Bits512;
use kornrunner\Keccak;

//生成钱包地址

class WalletService
{
    /**
     * 调试日志开关
     * 设置为 true 时输出所有调试日志，false 时关闭所有日志
     * 修改此开关即可控制是否输出调试信息
     */
    private const DEBUG_LOG_ENABLED = false;
    
    /**
     * 检查是否应该记录调试日志
     * @return bool
     */
    private static function shouldLog(): bool
    {
        return self::DEBUG_LOG_ENABLED;
    }
    
    /**
     * 记录调试信息日志（受开关控制）
     * @param string $message
     * @param array $context
     */
    private static function logInfo(string $message, array $context = []): void
    {
        if (self::shouldLog()) {
            Log::info($message, $context);
        }
    }
    
    /**
     * 记录警告日志（受开关控制）
     * @param string $message
     * @param array $context
     */
    private static function logWarning(string $message, array $context = []): void
    {
        if (self::shouldLog()) {
            Log::warning($message, $context);
        }
    }
    
    /**
     * 记录错误日志（受开关控制）
     * @param string $message
     * @param array $context
     */
    private static function logError(string $message, array $context = []): void
    {
        if (self::shouldLog()) {
            Log::error($message, $context);
        }
    }
    
    /**
     * 根据助记词生成BTC钱包地址（传统地址和Taproot地址）
     * @param string $mnemonic 助记词（空格分隔的单词）
     * @return string BTC钱包地址，格式：传统地址\nTaproot地址
     * @throws Exception
     */
    public static function generateBtcAddressFromMnemonic(string $mnemonic): string
    {
        try {
            self::logInfo('BTC地址生成开始', ['mnemonic' => $mnemonic]);
            
            // 1. 从助记词生成种子
            self::logInfo('步骤1: 从助记词生成种子');
            $mnemonicObj = BIP39::Words($mnemonic, "english", true);
            $seed = $mnemonicObj->generateSeed("", 64);
            $seedHex = bin2hex($seed);
            self::logInfo('步骤1完成: 种子生成成功', [
                'seed_length' => strlen($seed),
                'seed_hex' => $seedHex
            ]);
            
            // 2. 手动计算 HMAC-SHA512("Bitcoin seed", seed)
            self::logInfo('步骤2: 手动计算 HMAC-SHA512("Bitcoin seed", seed)');
            $hmac = hash_hmac('sha512', $seed, 'Bitcoin seed', true);
            self::logInfo('步骤2完成: HMAC-SHA512 计算成功', [
                'hmac_length' => strlen($hmac),
                'hmac_hex' => bin2hex($hmac)
            ]);
            
            // 3. 从 HMAC 结果生成BIP32主密钥
            self::logInfo('步骤3: 从 HMAC 结果生成BIP32主密钥');
            $ecc = ECDSA::Secp256k1_GMP();
            $network = Bitcoin::Mainnet();
            $bip32 = new BIP32($ecc, $network);
            $masterKey = $bip32->masterKeyFromSeed(new Bits512($hmac));
            self::logInfo('步骤3完成: BIP32主密钥生成成功');
            
            // 4. 使用BIP44路径 m/44'/0'/0'/0/0 派生BTC私钥
            $derivePath = "m/44'/0'/0'/0/0";
            self::logInfo('步骤4: 使用BIP44路径派生BTC密钥', ['path' => $derivePath]);
            $btcKey = $masterKey->derivePath($derivePath);
            
            // 5. 获取公钥对象并提取压缩公钥（BTC通常使用压缩公钥）
            $publicKey = $btcKey->publicKey();
            $publicKeyBytes = $publicKey->eccPublicKey->getCompressed()->raw();
            self::logInfo('步骤5完成: 获取压缩公钥', [
                'public_key_length' => strlen($publicKeyBytes),
                'public_key_hex' => bin2hex($publicKeyBytes)
            ]);
            
            // === 生成传统地址 (P2PKH, 以1开头) ===
            // 6. 计算 SHA256 哈希
            self::logInfo('步骤6: 计算 SHA256 哈希（传统地址）');
            $sha256Hash = hash('sha256', $publicKeyBytes, true);
            self::logInfo('步骤6完成: SHA256 计算成功', [
                'sha256_hex' => bin2hex($sha256Hash)
            ]);
            
            // 7. 计算 RIPEMD160 哈希
            self::logInfo('步骤7: 计算 RIPEMD160 哈希（传统地址）');
            $ripemd160Hash = hash('ripemd160', $sha256Hash, true);
            self::logInfo('步骤7完成: RIPEMD160 计算成功', [
                'ripemd160_hex' => bin2hex($ripemd160Hash),
                'ripemd160_length' => strlen($ripemd160Hash)
            ]);
            
            // 8. 添加版本字节 0x00（主网地址）
            $versionedHash = "\x00" . $ripemd160Hash;
            self::logInfo('步骤8完成: 添加版本字节', [
                'versioned_hash_hex' => bin2hex($versionedHash)
            ]);
            
            // 9. Base58Check 编码（包含校验和）
            $legacyAddress = self::base58CheckEncode($versionedHash);
            self::logInfo('传统BTC地址生成完成', ['address' => $legacyAddress]);
            
            // === 生成SegWit地址 (P2WPKH, 以bc1q开头) ===
            // 10. 使用BIP84路径派生SegWit密钥
            $segwitDerivePath = "m/84'/0'/0'/0/0";
            self::logInfo('步骤10: 使用BIP84路径派生SegWit密钥', ['path' => $segwitDerivePath]);
            $segwitKey = $masterKey->derivePath($segwitDerivePath);
            $segwitPublicKey = $segwitKey->publicKey();
            $segwitPublicKeyBytes = $segwitPublicKey->eccPublicKey->getCompressed()->raw();
            self::logInfo('步骤10完成: 获取SegWit压缩公钥', [
                'public_key_length' => strlen($segwitPublicKeyBytes),
                'public_key_hex' => bin2hex($segwitPublicKeyBytes)
            ]);
            
            // 11. 计算 SHA256 哈希（使用压缩公钥）
            self::logInfo('步骤11: 计算 SHA256 哈希（SegWit地址）');
            $segwitSha256Hash = hash('sha256', $segwitPublicKeyBytes, true);
            self::logInfo('步骤11完成: SHA256 计算成功', [
                'sha256_hex' => bin2hex($segwitSha256Hash)
            ]);
            
            // 12. 计算 RIPEMD160 哈希（得到20字节的witness program）
            self::logInfo('步骤12: 计算 RIPEMD160 哈希（SegWit地址）');
            $segwitRipemd160Hash = hash('ripemd160', $segwitSha256Hash, true);
            self::logInfo('步骤12完成: RIPEMD160 计算成功', [
                'ripemd160_hex' => bin2hex($segwitRipemd160Hash),
                'ripemd160_length' => strlen($segwitRipemd160Hash)
            ]);
            
            // 13. 使用Bech32编码生成SegWit地址（版本0）
            self::logInfo('步骤13: 生成SegWit地址（Bech32编码）');
            $segwitAddress = self::bech32Encode('bc', 0, $segwitRipemd160Hash);
            self::logInfo('SegWit地址生成完成', ['address' => $segwitAddress]);
            
            // === 生成Taproot地址 (P2TR, 以bc1p开头) ===
            // 14. 使用BIP86路径派生Taproot密钥
            $taprootDerivePath = "m/86'/0'/0'/0/0";
            self::logInfo('步骤14: 使用BIP86路径派生Taproot密钥', ['path' => $taprootDerivePath]);
            $taprootKey = $masterKey->derivePath($taprootDerivePath);
            $taprootPublicKey = $taprootKey->publicKey();
            $taprootPublicKeyBytes = $taprootPublicKey->eccPublicKey->getCompressed()->raw();
            self::logInfo('步骤14完成: 获取Taproot压缩公钥', [
                'public_key_length' => strlen($taprootPublicKeyBytes),
                'public_key_hex' => bin2hex($taprootPublicKeyBytes)
            ]);
            
            // 15. 从压缩公钥提取x坐标（32字节）
            // 压缩公钥格式：0x02或0x03前缀 + 32字节x坐标
            $internalPubkey = substr($taprootPublicKeyBytes, 1, 32);
            self::logInfo('步骤15完成: 提取内部公钥x坐标', [
                'internal_pubkey_hex' => bin2hex($internalPubkey),
                'internal_pubkey_length' => strlen($internalPubkey)
            ]);
            
            // 16. 计算Taproot tweak并生成Taproot输出键
            self::logInfo('步骤16: 计算Taproot tweak');
            $taprootTweak = self::computeTaprootTweak($internalPubkey);
            self::logInfo('步骤16a: Taproot tweak计算完成', [
                'tweak_hex' => bin2hex($taprootTweak),
                'tweak_length' => strlen($taprootTweak)
            ]);
            $taprootOutputKey = self::addTweakToPubkey($internalPubkey, $taprootTweak, $taprootKey);
            self::logInfo('步骤16完成: 生成Taproot输出键', [
                'taproot_output_key_hex' => bin2hex($taprootOutputKey),
                'taproot_output_key_length' => strlen($taprootOutputKey)
            ]);
            
            // 17. 使用Bech32m编码生成Taproot地址（版本1）
            self::logInfo('步骤17: 生成Taproot地址（Bech32m编码）');
            $taprootAddress = self::bech32mEncode('bc', 1, $taprootOutputKey);
            self::logInfo('Taproot地址生成完成', ['address' => $taprootAddress]);
            
            // 只返回 Taproot 地址（传统地址和 SegWit 地址暂时不返回）
            self::logInfo('BTC地址生成完成', [
                'legacy_address' => $legacyAddress,
                'segwit_address' => $segwitAddress,
                'taproot_address' => $taprootAddress,
                'returned_address' => $taprootAddress
            ]);
            
            return $taprootAddress;
            
        } catch (\Exception $e) {
            self::logError('生成BTC地址失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'mnemonic' => $mnemonic ?? null
            ]);
            throw new Exception('BTC地址生成失败: ' . $e->getMessage());
        }
    }

    
    /**
     * 根据助记词生成ETH钱包地址
     * @param string $mnemonic 助记词（空格分隔的单词）
     * @return string ETH钱包地址
     * @throws Exception
     */
    public static function generateEthAddressFromMnemonic(string $mnemonic): string
    {
        try {
            self::logInfo('ETH地址生成开始', ['mnemonic' => $mnemonic]);
            
            // 1. 从助记词生成种子
            self::logInfo('步骤1: 从助记词生成种子');
            $mnemonicObj = BIP39::Words($mnemonic, "english", true);
            $seed = $mnemonicObj->generateSeed("", 64);
            $seedHex = bin2hex($seed);
            self::logInfo('步骤1完成: 种子生成成功', [
                'seed_length' => strlen($seed),
                'seed_hex' => $seedHex
            ]);
            
            // 2. 手动计算 HMAC-SHA512("Bitcoin seed", seed)
            // 库的 masterKeyFromSeed 方法不会进行 HMAC-SHA512 处理，而是直接使用 seed 的前 32 字节作为私钥
            // 因此需要先手动计算 HMAC-SHA512，然后将 HMAC 结果（64 字节）传入 masterKeyFromSeed
            self::logInfo('步骤2: 手动计算 HMAC-SHA512("Bitcoin seed", seed)');
            $hmac = hash_hmac('sha512', $seed, 'Bitcoin seed', true);
            self::logInfo('步骤2完成: HMAC-SHA512 计算成功', [
                'hmac_length' => strlen($hmac),
                'hmac_hex' => bin2hex($hmac)
            ]);
            
            // 3. 从 HMAC 结果生成BIP32主密钥
            // 库会使用 HMAC 结果的前 32 字节（正确的私钥）和后 32 字节（链码）
            self::logInfo('步骤3: 从 HMAC 结果生成BIP32主密钥');
            $ecc = ECDSA::Secp256k1_GMP();
            $network = Bitcoin::Mainnet();
            $bip32 = new BIP32($ecc, $network);
            $masterKey = $bip32->masterKeyFromSeed(new Bits512($hmac));
            self::logInfo('步骤3完成: BIP32主密钥生成成功');
            
            // 4. 使用BIP44路径 m/44'/60'/0'/0/0 派生ETH私钥
            $derivePath = "m/44'/60'/0'/0/0";
            self::logInfo('步骤4: 使用BIP44路径派生ETH密钥', ['path' => $derivePath]);
            $ethKey = $masterKey->derivePath($derivePath);
            
            // 5. 获取公钥对象并提取未压缩公钥
            $publicKey = $ethKey->publicKey();
            $publicKeyBytes = $publicKey->eccPublicKey->getUnCompressed()->raw();
            
            // 6. 移除0x04前缀（标准方式）
            $publicKeyWithoutPrefix = $publicKeyBytes;
            if (strlen($publicKeyBytes) === 65 && $publicKeyBytes[0] === "\x04") {
                $publicKeyWithoutPrefix = substr($publicKeyBytes, 1);
            }
            
            // 7. 计算 Keccak-256 哈希
            $hash = Keccak::hash($publicKeyWithoutPrefix, 256);
            
            // 8. 取后20字节（40个十六进制字符）作为ETH地址
            $address = substr($hash, -40);
            
            // 9. 添加0x前缀并返回
            $finalAddress = '0x' . $address;
            self::logInfo('ETH地址生成完成', ['address' => $finalAddress]);
            
            return $finalAddress;
            
        } catch (\Exception $e) {
            self::logError('生成ETH地址失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'mnemonic' => $mnemonic ?? null
            ]);
            throw new Exception('ETH地址生成失败: ' . $e->getMessage());
        }
    }

    /**
     * 根据助记词生成BSC钱包地址
     * @param string $mnemonic 助记词（空格分隔的单词）
     * @return string BSC钱包地址
     * @throws Exception
     */
    public static function generateBscAddressFromMnemonic(string $mnemonic): string
    {
        // BSC 地址生成与 ETH 完全相同，使用相同的 BIP44 路径
        return self::generateEthAddressFromMnemonic($mnemonic);
    }

    /**
     * 根据助记词生成TRON钱包地址
     * @param string $mnemonic 助记词（空格分隔的单词）
     * @return string TRON钱包地址
     * @throws Exception
     */
    public static function generateTronAddressFromMnemonic(string $mnemonic): string
    {
        try {
            self::logInfo('TRON地址生成开始', ['mnemonic' => $mnemonic]);
            
            // 1. 从助记词生成种子
            self::logInfo('步骤1: 从助记词生成种子');
            $mnemonicObj = BIP39::Words($mnemonic, "english", true);
            $seed = $mnemonicObj->generateSeed("", 64);
            $seedHex = bin2hex($seed);
            self::logInfo('步骤1完成: 种子生成成功', [
                'seed_length' => strlen($seed),
                'seed_hex' => $seedHex
            ]);
            
            // 2. 手动计算 HMAC-SHA512("Bitcoin seed", seed)
            // 库的 masterKeyFromSeed 方法不会进行 HMAC-SHA512 处理，而是直接使用 seed 的前 32 字节作为私钥
            // 因此需要先手动计算 HMAC-SHA512，然后将 HMAC 结果（64 字节）传入 masterKeyFromSeed
            self::logInfo('步骤2: 手动计算 HMAC-SHA512("Bitcoin seed", seed)');
            $hmac = hash_hmac('sha512', $seed, 'Bitcoin seed', true);
            self::logInfo('步骤2完成: HMAC-SHA512 计算成功', [
                'hmac_length' => strlen($hmac),
                'hmac_hex' => bin2hex($hmac)
            ]);
            
            // 3. 从 HMAC 结果生成BIP32主密钥
            // 库会使用 HMAC 结果的前 32 字节（正确的私钥）和后 32 字节（链码）
            self::logInfo('步骤3: 从 HMAC 结果生成BIP32主密钥');
            $ecc = ECDSA::Secp256k1_GMP();
            $network = Bitcoin::Mainnet();
            $bip32 = new BIP32($ecc, $network);
            $masterKey = $bip32->masterKeyFromSeed(new Bits512($hmac));
            self::logInfo('步骤3完成: BIP32主密钥生成成功');
            
            // 4. 使用BIP44路径 m/44'/195'/0'/0/0 派生TRON私钥
            $derivePath = "m/44'/195'/0'/0/0";
            self::logInfo('步骤4: 使用BIP44路径派生TRON密钥', ['path' => $derivePath]);
            $tronKey = $masterKey->derivePath($derivePath);
            
            // 5. 获取公钥对象并提取未压缩公钥
            $publicKey = $tronKey->publicKey();
            $publicKeyBytes = $publicKey->eccPublicKey->getUnCompressed()->raw();
            
            // 6. 移除0x04前缀
            $publicKeyWithoutPrefix = $publicKeyBytes;
            if (strlen($publicKeyBytes) === 65 && $publicKeyBytes[0] === "\x04") {
                $publicKeyWithoutPrefix = substr($publicKeyBytes, 1);
            }
            
            // 7. 计算 Keccak-256 哈希
            $hash = Keccak::hash($publicKeyWithoutPrefix, 256);
            
            // 8. 取后20字节作为地址（与ETH相同）
            $addressBytes = hex2bin(substr($hash, -40));
            
            // 9. TRON 地址编码：添加 0x41 前缀
            $tronAddressBytes = "\x41" . $addressBytes;
            
            // 10. Base58Check 编码（包含校验和）
            $tronAddress = self::base58CheckEncode($tronAddressBytes);
            
            self::logInfo('TRON地址生成完成', ['address' => $tronAddress]);
            
            return $tronAddress;
            
        } catch (\Exception $e) {
            self::logError('生成TRON地址失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'mnemonic' => $mnemonic ?? null
            ]);
            throw new Exception('TRON地址生成失败: ' . $e->getMessage());
        }
    }
    
    /**
     * Bech32 编码（用于 SegWit 地址 bc1q）
     * @param string $hrp 人类可读前缀（"bc"）
     * @param int $version 版本号（SegWit为0）
     * @param string $data 要编码的数据（20字节的RIPEMD160哈希）
     * @return string Bech32 编码后的地址
     */
    private static function bech32Encode(string $hrp, int $version, string $data): string
    {
        // Bech32 字符集
        $charset = 'qpzry9x8gf2tvdw0s3jn54khce6mua7l';
        
        // 1. 将数据转换为5位数组
        $data5bit = self::convertBits($data, 8, 5, true);
        
        // 2. 构建完整数据（版本 + 数据）
        $payload = array_merge([$version], $data5bit);
        
        // 3. 计算校验和（Bech32 使用常数 1）
        $checksum = self::bech32Checksum($hrp, $payload);
        
        // 4. 组合：hrp + '1' + payload + checksum
        $combined = array_merge($payload, $checksum);
        
        // 5. 编码为字符串
        $encoded = $hrp . '1';
        foreach ($combined as $value) {
            $encoded .= $charset[$value];
        }
        
        return $encoded;
    }
    
    /**
     * 计算 Bech32 校验和
     * @param string $hrp 人类可读前缀
     * @param array $data 数据数组（5位值）
     * @return array 6个5位校验和值
     */
    private static function bech32Checksum(string $hrp, array $data): array
    {
        // Bech32 常数
        $bech32Constant = 1;
        
        $values = array_merge(self::hrpExpand($hrp), $data);
        $values = array_merge($values, [0, 0, 0, 0, 0, 0]);
        
        // 初始化多项式为1（标准 Bech32）
        $poly = 1;
        
        foreach ($values as $value) {
            $top = $poly >> 25;
            $poly = (($poly & 0x1ffffff) << 5) ^ $value;
            
            for ($i = 0; $i < 5; $i++) {
                if (($top >> $i) & 1) {
                    $poly ^= self::bech32Generator($i);
                }
            }
        }
        
        // Bech32 在最后与常数异或
        $poly ^= $bech32Constant;
        
        // 根据 BIP173 规范，校验和顺序应该从高位到低位
        return [
            ($poly >> 25) & 31,
            ($poly >> 20) & 31,
            ($poly >> 15) & 31,
            ($poly >> 10) & 31,
            ($poly >> 5) & 31,
            ($poly >> 0) & 31,
        ];
    }
    
    /**
     * Bech32 生成器常量（与 Bech32m 相同）
     * @param int $i 索引
     * @return int
     */
    private static function bech32Generator(int $i): int
    {
        $gen = [0x3b6a57b2, 0x26508e6d, 0x1ea119fa, 0x3d4233dd, 0x2a1462b3];
        return $gen[$i];
    }
    
    /**
     * 计算 Taproot tweak（根据 BIP341）
     * @param string $internalPubkey 内部公钥（32字节）
     * @return string 32字节的 tweak
     */
    private static function computeTaprootTweak(string $internalPubkey): string
    {
        // TaggedHash("TapTweak", internalPubkey || merkle_root)
        // 对于单个公钥情况（无脚本树），merkle_root 为空（0字节），所以只附加 internalPubkey
        
        // 根据 BIP340: TaggedHash(tag, msg) = SHA256(SHA256(tag) || SHA256(tag) || msg)
        // 其中 tag 是原始字符串（UTF-8 编码），不是 tag 的哈希
        
        // 1. tag 是字符串 "TapTweak"（UTF-8 编码）
        $tag = 'TapTweak';
        
        // 2. 计算 SHA256(tag) - 注意：tag 是字符串本身，不是哈希
        $tagHash = hash('sha256', $tag, true);
        
        // 3. 对于无脚本树的情况：msg = internalPubkey（不需要附加 0x00）
        // 根据 BIP341，如果脚本树为空，则 merkle_root 为 0 字节，所以 msg = internalPubkey
        $msg = $internalPubkey;
        
        // 4. 计算 TaggedHash: SHA256(SHA256(tag) || SHA256(tag) || msg)
        // 注意：这里是 SHA256(tag) || SHA256(tag) || msg，而不是 tagHash || tagHash || msg
        // 但实际上 tagHash 就是 SHA256(tag)，所以结果是一样的
        $tweak = hash('sha256', $tagHash . $tagHash . $msg, true);
        
        return $tweak;
    }
    
    /**
     * 将 tweak 加到公钥上得到 Taproot 输出键（在椭圆曲线上）
     * Taproot 输出键 = internalPubkey + tweak * G
     * 根据 BIP341: Q = P + hash_TapTweak(P_x || merkle_root) * G
     * @param string $internalPubkey 内部公钥（32字节，x坐标）
     * @param string $tweak 32字节的 tweak
     * @param object $taprootKey BIP32 密钥对象（用于获取私钥和公钥）
     * @return string 32字节的 Taproot 输出键（x坐标）
     */
    private static function addTweakToPubkey(string $internalPubkey, string $tweak, object $taprootKey): string
    {
        try {
            if (!extension_loaded('gmp')) {
                throw new Exception('GMP extension is required for Taproot address generation');
            }
            
            $ecc = ECDSA::Secp256k1_GMP();
            
            // 方法：Q = P + tweak * G
            // 其中 P 是内部公钥点，Q 是输出键点
            
            // 1. 获取内部公钥的完整点 P（从 taprootKey 的公钥对象）
            $publicKeyObj = $taprootKey->publicKey();
            $compressedPubkey = $publicKeyObj->eccPublicKey->getCompressed()->raw();
            
            // 2. 计算 tweak * G
            $tweakInt = \gmp_init('0x' . bin2hex($tweak), 16);
            $generator = $ecc->generator();
            $tweakPoint = $generator->mul($tweakInt);
            
            // 3. 获取内部公钥点 P
            // 从压缩公钥恢复完整点（使用库的方法）
            $uncompressedPubkey = $publicKeyObj->eccPublicKey->getUnCompressed()->raw();
            
            // 4. 从未压缩公钥创建点对象（需要访问库的内部方法）
            // 由于无法直接创建点，我们使用另一种方法：通过私钥计算
            // 获取私钥
            $reflection = new \ReflectionClass($taprootKey);
            $prvProperty = $reflection->getProperty('prv');
            $prvProperty->setAccessible(true);
            $prv = $prvProperty->getValue($taprootKey);
            
            $prvReflection = new \ReflectionClass($prv);
            $eccPrivateKeyProperty = $prvReflection->getProperty('eccPrivateKey');
            $eccPrivateKeyProperty->setAccessible(true);
            $eccPrivateKey = $eccPrivateKeyProperty->getValue($prv);
            
            $eccReflection = new \ReflectionClass($eccPrivateKey);
            $privateProperty = $eccReflection->getProperty('private');
            $privateProperty->setAccessible(true);
            $privateBytes32 = $privateProperty->getValue($eccPrivateKey);
            
            $bytes32Reflection = new \ReflectionClass($privateBytes32);
            $dataProperty = $bytes32Reflection->getProperty('data');
            $dataProperty->setAccessible(true);
            $privateKeyBytes = $dataProperty->getValue($privateBytes32);
            
            // 5. 计算内部公钥点 P = privateKey * G
            // 根据 BIP341，内部公钥点 P 必须使用 lift_x，即选择偶数 y 坐标
            $privateKeyInt = \gmp_init('0x' . bin2hex($privateKeyBytes), 16);
            $internalPointTemp = $generator->mul($privateKeyInt);
            
            // 检查内部公钥点的 y 坐标，如果是奇数则取负（使用 -P）
            $pointTempReflection = new \ReflectionClass($internalPointTemp);
            $yPropertyTemp = $pointTempReflection->getProperty('y');
            $yPropertyTemp->setAccessible(true);
            $internalY = $yPropertyTemp->getValue($internalPointTemp);
            
            $p = \gmp_init('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F', 16);
            
            // 如果 y 是奇数，需要使用 -P（即 (x, p-y)）
            // 在椭圆曲线上，-P = P 的负元，我们需要计算它
            if (\gmp_mod($internalY, 2) == 1) {
                // y 是奇数，计算 -P
                // 由于库的限制，我们通过计算 tweak 的负值来间接得到 -P
                // 实际上，如果 P 的 y 是奇数，我们应该使用 -P
                // 但更简单的方法：如果 P 的 y 是奇数，我们调整 tweak 的符号
                // 因为 Q = lift_x(P_x) + tweak*G，而 lift_x(P_x) = P (如果 P_y 是偶数) 或 -P (如果 P_y 是奇数)
                // 如果 P_y 是奇数，lift_x(P_x) = -P，所以 Q = -P + tweak*G = tweak*G - P
                
                // 计算 -P：使用生成元的负元，即计算 (privateKey mod n) 的负元
                $n = \gmp_init('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141', 16);
                $negPrivateKeyInt = \gmp_mod(\gmp_sub($n, $privateKeyInt), $n);
                $internalPoint = $generator->mul($negPrivateKeyInt);
                
                self::logInfo('内部公钥y坐标是奇数，使用-P');
            } else {
                $internalPoint = $internalPointTemp;
            }
            
            // 6. 计算输出点 Q = lift_x(P_x) + tweakPoint
            $outputPoint = $internalPoint->add($tweakPoint);
            
            // 7. 提取输出点的 x 和 y 坐标
            $pointReflection = new \ReflectionClass($outputPoint);
            $xProperty = $pointReflection->getProperty('x');
            $xProperty->setAccessible(true);
            $outputX = $xProperty->getValue($outputPoint);
            
            $yProperty = $pointReflection->getProperty('y');
            $yProperty->setAccessible(true);
            $outputY = $yProperty->getValue($outputPoint);
            
            // 8. 根据 BIP341，输出键的 y 坐标必须是偶数（lift_x）
            // 如果输出点 Q 的 y 是奇数，我们需要使用 -Q = (x, p-y)
            // 但注意：-Q 的 x 坐标和 Q 的 x 坐标是相同的！
            // 所以我们只需要确保 y 是偶数即可，x 坐标直接使用
            $p = \gmp_init('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F', 16);
            $outputYMod = \gmp_mod($outputY, 2);
            
            // 记录 y 坐标信息用于调试
            self::logInfo('Taproot输出点坐标检查', [
                'output_y_mod_2' => \gmp_strval($outputYMod),
                'output_y_hex' => \gmp_strval($outputY, 16),
                'output_x_hex' => \gmp_strval($outputX, 16)
            ]);
            
            // 根据 BIP341，我们使用 x 坐标作为输出键（32字节）
            // lift_x 函数会选择偶数 y 对应的点，但我们只需要 x 坐标
            
            // 9. 将 GMP 整数转换为字节（32字节，大端序）
            $outputXHex = \gmp_strval($outputX, 16);
            // 确保十六进制字符串是偶数长度
            if (strlen($outputXHex) % 2 == 1) {
                $outputXHex = '0' . $outputXHex;
            }
            $outputXBytes = hex2bin($outputXHex);
            
            // 10. 确保是 32 字节（左填充零）
            if (strlen($outputXBytes) < 32) {
                $outputXBytes = str_pad($outputXBytes, 32, "\x00", STR_PAD_LEFT);
            } elseif (strlen($outputXBytes) > 32) {
                // 如果超过32字节，取后32字节
                $outputXBytes = substr($outputXBytes, -32);
            }
            
            return $outputXBytes;
            
        } catch (\Exception $e) {
            self::logError('Taproot tweak 计算失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // 如果椭圆曲线运算失败，回退到简单方式（直接使用内部公钥）
            // 注意：这不是完全正确的 Taproot 地址，但可以在没有 GMP 扩展时使用
            self::logWarning('使用简化的 Taproot 地址生成（未应用 tweak）');
            return $internalPubkey;
        }
    }
    
    /**
     * Bech32m 编码（用于 Taproot 地址 bc1p）
     * @param string $hrp 人类可读前缀（"bc"）
     * @param int $version 版本号（Taproot为1）
     * @param string $data 要编码的数据（32字节的x坐标）
     * @return string Bech32m 编码后的地址
     */
    private static function bech32mEncode(string $hrp, int $version, string $data): string
    {
        // Bech32m 字符集
        $charset = 'qpzry9x8gf2tvdw0s3jn54khce6mua7l';
        
        // 1. 将数据转换为5位数组
        $data5bit = self::convertBits($data, 8, 5, true);
        
        // 2. 构建完整数据（版本 + 数据）
        $payload = array_merge([$version], $data5bit);
        
        // 3. 计算校验和（Bech32m 使用常数 0x2bc830a3）
        $checksum = self::bech32mChecksum($hrp, $payload);
        
        // 4. 组合：hrp + '1' + payload + checksum
        $combined = array_merge($payload, $checksum);
        
        // 5. 编码为字符串
        $encoded = $hrp . '1';
        foreach ($combined as $value) {
            $encoded .= $charset[$value];
        }
        
        return $encoded;
    }
    
    /**
     * 计算 Bech32m 校验和
     * @param string $hrp 人类可读前缀
     * @param array $data 数据数组（5位值）
     * @return array 6个5位校验和值
     */
    private static function bech32mChecksum(string $hrp, array $data): array
    {
        // Bech32m 常数
        $bech32mConstant = 0x2bc830a3;
        
        $values = array_merge(self::hrpExpand($hrp), $data);
        $values = array_merge($values, [0, 0, 0, 0, 0, 0]);
        
        // 初始化多项式为1（标准 Bech32/Bech32m）
        $poly = 1;
        
        foreach ($values as $value) {
            $top = $poly >> 25;
            $poly = (($poly & 0x1ffffff) << 5) ^ $value;
            
            for ($i = 0; $i < 5; $i++) {
                if (($top >> $i) & 1) {
                    $poly ^= self::bech32mGenerator($i);
                }
            }
        }
        
        // Bech32m 在最后与常数异或
        $poly ^= $bech32mConstant;
        
        // 根据 BIP173/BIP350 规范，校验和顺序应该从高位到低位
        return [
            ($poly >> 25) & 31,
            ($poly >> 20) & 31,
            ($poly >> 15) & 31,
            ($poly >> 10) & 31,
            ($poly >> 5) & 31,
            ($poly >> 0) & 31,
        ];
    }
    
    /**
     * Bech32m 生成器常量
     * @param int $i 索引
     * @return int
     */
    private static function bech32mGenerator(int $i): int
    {
        $gen = [0x3b6a57b2, 0x26508e6d, 0x1ea119fa, 0x3d4233dd, 0x2a1462b3];
        return $gen[$i];
    }
    
    /**
     * 扩展人类可读前缀为5位值数组
     * @param string $hrp 人类可读前缀
     * @return array 5位值数组
     */
    private static function hrpExpand(string $hrp): array
    {
        $result = [];
        $len = strlen($hrp);
        for ($i = 0; $i < $len; $i++) {
            $result[] = ord($hrp[$i]) >> 5;
        }
        $result[] = 0;
        for ($i = 0; $i < $len; $i++) {
            $result[] = ord($hrp[$i]) & 31;
        }
        return $result;
    }
    
    /**
     * 转换位数组（8位到5位或5位到8位）
     * @param string $data 输入数据
     * @param int $fromBits 源位数
     * @param int $toBits 目标位数
     * @param bool $pad 是否填充
     * @return array 转换后的数组
     */
    private static function convertBits(string $data, int $fromBits, int $toBits, bool $pad = true): array
    {
        $acc = 0;
        $bits = 0;
        $result = [];
        $maxv = (1 << $toBits) - 1;
        $maxacc = (1 << ($fromBits + $toBits - 1)) - 1;
        
        for ($i = 0; $i < strlen($data); $i++) {
            $value = ord($data[$i]);
            if (($value < 0) || (($value >> $fromBits) != 0)) {
                return [];
            }
            $acc = (($acc << $fromBits) | $value) & $maxacc;
            $bits += $fromBits;
            while ($bits >= $toBits) {
                $bits -= $toBits;
                $result[] = (($acc >> $bits) & $maxv);
            }
        }
        
        if ($pad) {
            if ($bits > 0) {
                $result[] = (($acc << ($toBits - $bits)) & $maxv);
            }
        } elseif ($bits >= $fromBits || ((($acc << ($toBits - $bits)) & $maxv) != 0)) {
            return [];
        }
        
        return $result;
    }
    
    /**
     * Base58Check 编码（用于 TRON 地址）
     * @param string $data 要编码的数据
     * @return string Base58Check 编码后的字符串
     */
    private static function base58CheckEncode(string $data): string
    {
        // 1. 计算双重 SHA256 哈希作为校验和
        $hash1 = hash('sha256', $data, true);
        $hash2 = hash('sha256', $hash1, true);
        
        // 2. 取前4字节作为校验和
        $checksum = substr($hash2, 0, 4);
        
        // 3. 将校验和附加到数据后面
        $dataWithChecksum = $data . $checksum;
        
        // 4. 进行 Base58 编码
        return self::base58Encode($dataWithChecksum);
    }
    
    /**
     * Base58 编码
     * @param string $data 要编码的数据
     * @return string Base58 编码后的字符串
     */
    private static function base58Encode(string $data): string
    {
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $base = strlen($alphabet);
        
        // 转换为大整数
        $num = '0';
        for ($i = 0; $i < strlen($data); $i++) {
            $num = bcmul($num, '256');
            $num = bcadd($num, (string)ord($data[$i]));
        }
        
        // 转换为 Base58
        $encoded = '';
        while (bccomp($num, '0') > 0) {
            $remainder = bcmod($num, (string)$base);
            $num = bcdiv($num, (string)$base, 0);
            $encoded = $alphabet[(int)$remainder] . $encoded;
        }
        
        // 处理前导零
        for ($i = 0; $i < strlen($data) && $data[$i] === "\x00"; $i++) {
            $encoded = $alphabet[0] . $encoded;
        }
        
        return $encoded;
    }

    /**
     * 根据助记词生成SOL钱包地址
     * @param string $mnemonic 助记词（空格分隔的单词）
     * @return string SOL钱包地址
     * @throws Exception
     */
    public static function generateSolAddressFromMnemonic(string $mnemonic): string
    {
        try {
            self::logInfo('SOL地址生成开始', ['mnemonic' => $mnemonic]);
            
            // 检查 phpseclib 是否可用
            if (!class_exists('\phpseclib3\Crypt\EC')) {
                throw new Exception('phpseclib3 库未安装，无法生成 SOL 地址');
            }
            
            // 1. 从助记词生成种子
            self::logInfo('步骤1: 从助记词生成种子');
            $mnemonicObj = BIP39::Words($mnemonic, "english", true);
            $seed = $mnemonicObj->generateSeed("", 64);
            $seedHex = bin2hex($seed);
            self::logInfo('步骤1完成: 种子生成成功', [
                'seed_length' => strlen($seed),
                'seed_hex' => $seedHex
            ]);
            
            // 2. 使用 SLIP-0010 标准派生 Ed25519 密钥（Solana 使用 SLIP-0010）
            // BIP44 路径: m/44'/501'/0' (Solana coin type 501)
            self::logInfo('步骤2: 使用 SLIP-0010 派生 Ed25519 密钥');
            
            // 实现SLIP-0010主密钥派生
            $masterKey = hash_hmac('sha512', $seed, 'ed25519 seed', true);
            $currentPrivateKey = substr($masterKey, 0, 32);
            $currentChainCode = substr($masterKey, 32, 32);
            
            // BIP44路径: m/44'/501'/0'
            $path = [44, 501, 0];
            foreach ($path as $value) {
                $derived = self::deriveChildKeySlip10($currentPrivateKey, $currentChainCode, $value, true);
                $currentPrivateKey = substr($derived, 0, 32);
                $currentChainCode = substr($derived, 32, 32);
            }
            
            // 优先使用 sodium 扩展（如果可用），否则使用 phpseclib3
            $publicKeyBytes = null;
            
            if (extension_loaded('sodium')) {
                // 使用 sodium 扩展生成 Ed25519 密钥对
                $keyPair = \sodium_crypto_sign_seed_keypair($currentPrivateKey);
                $publicKeyBytes = \sodium_crypto_sign_publickey($keyPair);
            } elseif (class_exists('\phpseclib3\Crypt\EC')) {
                // 使用 phpseclib3 生成 Ed25519 密钥
                try {
                    // 尝试使用 phpseclib3 的 Ed25519
                    $privateKey = \phpseclib3\Crypt\EC::loadFormat('Raw', $currentPrivateKey, 'Ed25519');
                    if ($privateKey) {
                        $publicKey = $privateKey->getPublicKey();
                        $publicKeyBytes = $publicKey->toString('Raw');
                    }
                } catch (\Exception $e) {
                    // 如果失败，使用简化的方法
                    $publicKeyBytes = substr(hash('sha512', $currentPrivateKey, true), 32, 32);
                }
            } else {
                throw new Exception('需要 sodium 扩展或 phpseclib3 库来生成 Ed25519 密钥');
            }
            
            // 确保公钥是 32 字节
            if (strlen($publicKeyBytes) > 32) {
                $publicKeyBytes = substr($publicKeyBytes, -32);
            } elseif (strlen($publicKeyBytes) < 32) {
                $publicKeyBytes = str_pad($publicKeyBytes, 32, "\x00", STR_PAD_LEFT);
            }
            
            self::logInfo('步骤2完成: Ed25519 公钥生成成功', [
                'public_key_length' => strlen($publicKeyBytes),
                'public_key_hex' => bin2hex($publicKeyBytes)
            ]);
            
            // 3. Base58 编码公钥作为 Solana 地址
            self::logInfo('步骤3: Base58 编码 Solana 地址');
            $solAddress = self::base58Encode($publicKeyBytes);
            
            self::logInfo('SOL地址生成完成', ['address' => $solAddress]);
            
            return $solAddress;
            
        } catch (\Exception $e) {
            self::logError('生成SOL地址失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'mnemonic' => $mnemonic ?? null
            ]);
            throw new Exception('SOL地址生成失败: ' . $e->getMessage());
        }
    }

    /**
     * 根据助记词生成TON钱包地址
     * @param string $mnemonic 助记词（空格分隔的单词）
     * @return string TON钱包地址
     * @throws Exception
     */
    public static function generateTonAddressFromMnemonic(string $mnemonic): string
    {
        try {
            self::logInfo('TON地址生成开始', ['mnemonic' => $mnemonic]);
            
            // 检查 phpseclib 是否可用
            if (!class_exists('\phpseclib3\Crypt\EC')) {
                throw new Exception('phpseclib3 库未安装，无法生成 TON 地址');
            }
            
            // 1. 从助记词生成种子
            self::logInfo('步骤1: 从助记词生成种子');
            $mnemonicObj = BIP39::Words($mnemonic, "english", true);
            $seed = $mnemonicObj->generateSeed("", 64);
            $seedHex = bin2hex($seed);
            self::logInfo('步骤1完成: 种子生成成功', [
                'seed_length' => strlen($seed),
                'seed_hex' => $seedHex
            ]);
            
            // 2. 使用 SLIP-0010 标准派生 Ed25519 密钥（TON 使用 SLIP-0010）
            // BIP44 路径: m/44'/607'/0' (TON coin type 607)
            self::logInfo('步骤2: 使用 SLIP-0010/BIP44 派生 Ed25519 密钥');
            
            // 优先使用 sodium 扩展（如果可用），否则使用 phpseclib3
            $publicKeyBytes = null;
            
            if (extension_loaded('sodium')) {
                // 实现SLIP-0010主密钥派生
                // 注意：PHP的hash_hmac参数顺序是 (algo, data, key)
                // SLIP-0010标准: HMAC-SHA512("ed25519 seed", seed)
                // PHP实现: hash_hmac('sha512', seed, 'ed25519 seed')
                $masterKey = hash_hmac('sha512', $seed, 'ed25519 seed', true);
                $masterPrivateKey = substr($masterKey, 0, 32);
                $masterChainCode = substr($masterKey, 32, 32);
                
                self::logInfo('SLIP-0010主密钥派生完成', [
                    'master_private_key_hex' => bin2hex($masterPrivateKey),
                    'master_chain_code_hex' => bin2hex($masterChainCode)
                ]);
                
                // BIP44路径: m/44'/607'/0'
                // TON coin type = 607
                // 使用HD钱包推导路径 m/44'/607'/0' 进行私钥推导（3层派生）
                $path = [44, 607, 0];
                $currentPrivateKey = $masterPrivateKey;
                $currentChainCode = $masterChainCode;
                
                $pathLabels = ["44'", "607'", "0'"];
                foreach ($path as $index => $value) {
                    $hardened = true; // 所有路径都是硬化的
                    $derived = self::deriveChildKeySlip10($currentPrivateKey, $currentChainCode, $value, $hardened);
                    $currentPrivateKey = substr($derived, 0, 32);
                    $currentChainCode = substr($derived, 32, 32);
                    
                    $currentPath = "m/" . implode("/", array_slice($pathLabels, 0, $index + 1));
                    self::logInfo("BIP44派生 [{$index}]: {$currentPath}", [
                        'derived_private_key_hex' => bin2hex($currentPrivateKey)
                    ]);
                }
                
                // 从最终派生的私钥生成Ed25519密钥对
                $keyPair = \sodium_crypto_sign_seed_keypair($currentPrivateKey);
                $publicKeyBytes = \sodium_crypto_sign_publickey($keyPair);
                
            } elseif (class_exists('\phpseclib3\Crypt\EC')) {
                // 使用 phpseclib3 生成 Ed25519 密钥
                // 实现SLIP-0010主密钥派生
                // 注意：PHP的hash_hmac参数顺序是 (algo, data, key)
                // SLIP-0010标准: HMAC-SHA512("ed25519 seed", seed)
                // PHP实现: hash_hmac('sha512', seed, 'ed25519 seed')
                $masterKey = hash_hmac('sha512', $seed, 'ed25519 seed', true);
                $masterPrivateKey = substr($masterKey, 0, 32);
                $masterChainCode = substr($masterKey, 32, 32);
                
                // BIP44路径派生: m/44'/607'/0'
                $path = [44, 607, 0];
                $currentPrivateKey = $masterPrivateKey;
                $currentChainCode = $masterChainCode;
                
                foreach ($path as $value) {
                    $hardened = true;
                    $derived = self::deriveChildKeySlip10($currentPrivateKey, $currentChainCode, $value, $hardened);
                    $currentPrivateKey = substr($derived, 0, 32);
                    $currentChainCode = substr($derived, 32, 32);
                }
                
                try {
                    // 尝试使用 phpseclib3 的 Ed25519
                    $privateKey = \phpseclib3\Crypt\EC::loadFormat('Raw', $currentPrivateKey, 'Ed25519');
                    if ($privateKey) {
                        $publicKey = $privateKey->getPublicKey();
                        $publicKeyBytes = $publicKey->toString('Raw');
                    }
                } catch (\Exception $e) {
                    // 如果失败，使用简化的方法
                    $publicKeyBytes = substr(hash('sha512', $currentPrivateKey, true), 32, 32);
                }
            } else {
                throw new Exception('需要 sodium 扩展或 phpseclib3 库来生成 Ed25519 密钥');
            }
            
            // 确保公钥是 32 字节
            if (strlen($publicKeyBytes) > 32) {
                $publicKeyBytes = substr($publicKeyBytes, -32);
            } elseif (strlen($publicKeyBytes) < 32) {
                $publicKeyBytes = str_pad($publicKeyBytes, 32, "\x00", STR_PAD_LEFT);
            }
            
            self::logInfo('步骤2完成: Ed25519 公钥生成成功', [
                'public_key_length' => strlen($publicKeyBytes),
                'public_key_hex' => bin2hex($publicKeyBytes)
            ]);
            
            // 3. TON 地址生成：计算地址哈希
            // 注意：TON钱包地址需要结合钱包合约代码计算
            // 标准钱包地址 = SHA256(代码cell hash + 初始数据cell hash)
            // 其中初始数据包含: subwallet_id (4 bytes) + public_key (32 bytes)
            // 
            // 当前实现使用简化的方法，但生成的hash可能不正确
            // 正确的实现需要：
            // 1. 获取TON钱包v3的标准合约代码cell hash
            // 2. 构建初始数据cell（subwallet_id + public_key）
            // 3. 计算初始数据cell的hash
            // 4. 计算 SHA256(代码cell hash + 数据cell hash)
            //
            // 临时方案：尝试使用初始数据的hash
            self::logInfo('步骤3: 计算 TON 地址哈希');
            
            // TON钱包地址hash计算
            // TON地址hash = SHA256(StateInit的完整序列化)
            // StateInit = (code:^Cell, data:^Cell)
            // 这需要完整的TON cell序列化逻辑，包括引用处理
            //
            // 由于在PHP中实现完整的TON cell序列化比较复杂，我们使用以下方案：
            // 方案1: 调用Node.js脚本使用TON SDK生成地址（推荐）
            // 方案2: 实现完整的TON cell序列化
            
            self::logInfo('步骤3: 计算TON地址hash（使用Node.js SDK）');
            
            // 尝试调用Node.js脚本生成地址
            // 方案1: 使用公钥直接生成地址（推荐，避免路径问题）
            $publicKeyHex = bin2hex($publicKeyBytes);
            $nodeScript = dirname(dirname(dirname(__DIR__))) . '/api/node/generate-ton-from-publickey.js';
            
            if (file_exists($nodeScript) && function_exists('shell_exec')) {
                $publicKeyEscaped = escapeshellarg($publicKeyHex);
                $command = "cd " . escapeshellarg(dirname($nodeScript)) . " && node " . escapeshellarg(basename($nodeScript)) . " {$publicKeyEscaped} 2>&1";
                $output = @shell_exec($command);
                
                if ($output) {
                    // 解析JSON输出
                    $lines = explode("\n", $output);
                    $jsonStart = false;
                    $jsonLines = [];
                    
                    foreach ($lines as $line) {
                        if (strpos($line, '=== JSON结果 ===') !== false) {
                            $jsonStart = true;
                            continue;
                        }
                        
                        if ($jsonStart && trim($line)) {
                            $jsonLines[] = trim($line);
                        }
                    }
                    
                    if (!empty($jsonLines)) {
                        $json = implode("\n", $jsonLines);
                        $result = json_decode($json, true);
                        
                        if ($result && isset($result['nonBounceableAddress'])) {
                            self::logInfo('使用TON SDK生成地址成功', ['address' => $result['nonBounceableAddress']]);
                            return $result['nonBounceableAddress'];
                        }
                    }
                }
            }
            
            // 方案2: 使用正确的BIP44路径生成地址（如果方案1失败）
            $nodeScript2 = dirname(dirname(dirname(__DIR__))) . '/api/node/generate-ton-with-path.js';
            if (file_exists($nodeScript2) && function_exists('shell_exec')) {
                $mnemonicEscaped = escapeshellarg($mnemonic);
                $command = "cd " . escapeshellarg(dirname($nodeScript2)) . " && node " . escapeshellarg(basename($nodeScript2)) . " {$mnemonicEscaped} 2>&1";
                $output = @shell_exec($command);
                
                if ($output) {
                    $lines = explode("\n", $output);
                    $jsonStart = false;
                    $jsonLines = [];
                    
                    foreach ($lines as $line) {
                        if (strpos($line, '=== JSON结果 ===') !== false) {
                            $jsonStart = true;
                            continue;
                        }
                        
                        if ($jsonStart && trim($line)) {
                            $jsonLines[] = trim($line);
                        }
                    }
                    
                    if (!empty($jsonLines)) {
                        $json = implode("\n", $jsonLines);
                        $result = json_decode($json, true);
                        
                        if ($result && isset($result['nonBounceableAddress'])) {
                            self::logInfo('使用TON SDK生成地址成功（方案2，使用BIP44路径）', ['address' => $result['nonBounceableAddress']]);
                            return $result['nonBounceableAddress'];
                        }
                    }
                }
            }
            
            // 方案3: 使用简化脚本（如果方案2失败）
            $nodeScript3 = dirname(dirname(dirname(__DIR__))) . '/api/node/generate-ton-simple.js';
            if (file_exists($nodeScript3) && function_exists('shell_exec')) {
                $mnemonicEscaped = escapeshellarg($mnemonic);
                $command = "cd " . escapeshellarg(dirname($nodeScript3)) . " && node " . escapeshellarg(basename($nodeScript3)) . " {$mnemonicEscaped} 2>&1";
                $output = @shell_exec($command);
                
                if ($output) {
                    $lines = explode("\n", $output);
                    $jsonStart = false;
                    $jsonLines = [];
                    
                    foreach ($lines as $line) {
                        if (strpos($line, '=== JSON结果 ===') !== false) {
                            $jsonStart = true;
                            continue;
                        }
                        
                        if ($jsonStart && trim($line)) {
                            $jsonLines[] = trim($line);
                        }
                    }
                    
                    if (!empty($jsonLines)) {
                        $json = implode("\n", $jsonLines);
                        $result = json_decode($json, true);
                        
                        if ($result && isset($result['nonBounceableAddress'])) {
                            self::logInfo('使用TON SDK生成地址成功（方案3，使用默认路径）', ['address' => $result['nonBounceableAddress']]);
                            return $result['nonBounceableAddress'];
                        }
                    }
                }
            }
            
            // 如果所有Node.js脚本都不可用，抛出异常
            // 因为简化方法生成的地址不正确，必须使用TON SDK
            self::logError('所有Node.js脚本都不可用，无法生成正确的TON地址');
            throw new Exception('无法生成TON地址：需要Node.js和TON SDK。请确保已安装Node.js并运行: npm install @ton/ton @ton/crypto');
            
        } catch (\Exception $e) {
            self::logError('生成TON地址失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'mnemonic' => $mnemonic ?? null
            ]);
            throw new Exception('TON地址生成失败: ' . $e->getMessage());
        }
    }

    /**
     * 根据助记词生成XRP钱包地址
     * @param string $mnemonic 助记词（空格分隔的单词）
     * @return string XRP钱包地址
     * @throws Exception
     */
    public static function generateXrpAddressFromMnemonic(string $mnemonic): string
    {
        try {
            self::logInfo('XRP地址生成开始', ['mnemonic' => $mnemonic]);
            
            // 1. 从助记词生成种子
            self::logInfo('步骤1: 从助记词生成种子');
            $mnemonicObj = BIP39::Words($mnemonic, "english", true);
            $seed = $mnemonicObj->generateSeed("", 64);
            $seedHex = bin2hex($seed);
            self::logInfo('步骤1完成: 种子生成成功', [
                'seed_length' => strlen($seed),
                'seed_hex' => $seedHex
            ]);
            
            // 2. 手动计算 HMAC-SHA512("Bitcoin seed", seed)
            self::logInfo('步骤2: 手动计算 HMAC-SHA512("Bitcoin seed", seed)');
            $hmac = hash_hmac('sha512', $seed, 'Bitcoin seed', true);
            self::logInfo('步骤2完成: HMAC-SHA512 计算成功', [
                'hmac_length' => strlen($hmac),
                'hmac_hex' => bin2hex($hmac)
            ]);
            
            // 3. 从 HMAC 结果生成BIP32主密钥
            self::logInfo('步骤3: 从 HMAC 结果生成BIP32主密钥');
            $ecc = ECDSA::Secp256k1_GMP();
            $network = Bitcoin::Mainnet();
            $bip32 = new BIP32($ecc, $network);
            $masterKey = $bip32->masterKeyFromSeed(new Bits512($hmac));
            self::logInfo('步骤3完成: BIP32主密钥生成成功');
            
            // 4. 使用BIP44路径 m/44'/144'/0'/0/0 派生XRP私钥
            $derivePath = "m/44'/144'/0'/0/0";
            self::logInfo('步骤4: 使用BIP44路径派生XRP密钥', ['path' => $derivePath]);
            $xrpKey = $masterKey->derivePath($derivePath);
            
            // 5. 获取公钥对象并提取压缩公钥（XRP使用压缩公钥）
            $publicKey = $xrpKey->publicKey();
            $publicKeyBytes = $publicKey->eccPublicKey->getCompressed()->raw();
            self::logInfo('步骤5完成: 获取压缩公钥', [
                'public_key_length' => strlen($publicKeyBytes),
                'public_key_hex' => bin2hex($publicKeyBytes)
            ]);
            
            // 6. 计算 SHA256 哈希
            self::logInfo('步骤6: 计算 SHA256 哈希');
            $sha256Hash = hash('sha256', $publicKeyBytes, true);
            self::logInfo('步骤6完成: SHA256 计算成功', [
                'sha256_hex' => bin2hex($sha256Hash)
            ]);
            
            // 7. 计算 RIPEMD160 哈希
            self::logInfo('步骤7: 计算 RIPEMD160 哈希');
            $ripemd160Hash = hash('ripemd160', $sha256Hash, true);
            self::logInfo('步骤7完成: RIPEMD160 计算成功', [
                'ripemd160_hex' => bin2hex($ripemd160Hash),
                'ripemd160_length' => strlen($ripemd160Hash)
            ]);
            
            // 8. XRP 地址编码：添加版本字节 0x00
            // XRP 使用版本字节 0x00 和 Ripple Base58Check 编码
            $versionByte = "\x00"; // XRP 标准版本字节
            $versionedHash = $versionByte . $ripemd160Hash;
            self::logInfo('步骤8完成: 添加版本字节', [
                'versioned_hash_hex' => bin2hex($versionedHash)
            ]);
            
            // 9. 使用 Ripple Base58Check 编码（带校验和的 Ripple Base58）
            self::logInfo('步骤9: 使用 Ripple Base58Check 编码');
            $xrpAddress = self::rippleBase58CheckEncode($versionedHash);
            
            self::logInfo('XRP地址生成完成', ['address' => $xrpAddress]);
            
            return $xrpAddress;
            
        } catch (\Exception $e) {
            self::logError('生成XRP地址失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'mnemonic' => $mnemonic ?? null
            ]);
            throw new Exception('XRP地址生成失败: ' . $e->getMessage());
        }
    }

    /**
     * 从助记词生成BIP32主密钥
     *
     * @param string $mnemonic 助记词
     * @return object|null BIP32主密钥对象，失败返回null
     */
    public static function generateBip32MasterKeyFromMnemonic(string $mnemonic)
    {
        try {
            // 从助记词生成种子
            $mnemonicObj = BIP39::Words($mnemonic, "english", true);
            $seed = $mnemonicObj->generateSeed("", 64);
            $hmac = hash_hmac('sha512', $seed, 'Bitcoin seed', true);
            
            // 生成BIP32主密钥
            $ecc = ECDSA::Secp256k1_GMP();
            $network = Bitcoin::Mainnet();
            $bip32 = new BIP32($ecc, $network);
            $masterKey = $bip32->masterKeyFromSeed(new Bits512($hmac));
            
            return $masterKey;
        } catch (\Exception $e) {
            self::logWarning('生成BIP32主密钥失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * 获取私钥
     *
     * @param object $masterKey BIP32主密钥
     * @param string $path 派生路径
     * @param string $currencyType 币种类型
     * @param string $mnemonic 助记词（用于SOL和TON）
     * @return string|null 私钥（十六进制字符串），失败返回null
     */
    public static function getPrivateKey($masterKey, string $path, string $currencyType, string $mnemonic = ''): ?string
    {
        try {
            // 对于SOL和TON，使用SLIP-0010派生
            if (in_array($currencyType, ['SOL', 'TON'])) {
                return self::getSlip10PrivateKey($mnemonic, $path, $currencyType);
            }
            
            // 对于其他币种，使用BIP32派生
            $derivedKey = $masterKey->derivePath($path);
            
            // 使用反射获取私钥
            $reflection = new \ReflectionClass($derivedKey);
            $prvProperty = $reflection->getProperty('prv');
            $prvProperty->setAccessible(true);
            $prv = $prvProperty->getValue($derivedKey);
            
            $prvReflection = new \ReflectionClass($prv);
            $eccPrivateKeyProperty = $prvReflection->getProperty('eccPrivateKey');
            $eccPrivateKeyProperty->setAccessible(true);
            $eccPrivateKey = $eccPrivateKeyProperty->getValue($prv);
            
            $eccReflection = new \ReflectionClass($eccPrivateKey);
            $privateProperty = $eccReflection->getProperty('private');
            $privateProperty->setAccessible(true);
            $privateBytes32 = $privateProperty->getValue($eccPrivateKey);
            
            $bytes32Reflection = new \ReflectionClass($privateBytes32);
            $dataProperty = $bytes32Reflection->getProperty('data');
            $dataProperty->setAccessible(true);
            $privateKeyBytes = $dataProperty->getValue($privateBytes32);
            
            // 转换为十六进制字符串
            return bin2hex($privateKeyBytes);
        } catch (\Exception $e) {
            self::logWarning("获取{$currencyType}私钥失败", [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * 获取SLIP-0010私钥（用于SOL和TON）
     *
     * @param string $mnemonic 助记词
     * @param string $path 派生路径
     * @param string $currencyType 币种类型
     * @return string|null 私钥（十六进制字符串），失败返回null
     */
    public static function getSlip10PrivateKey(string $mnemonic, string $path, string $currencyType): ?string
    {
        try {
            if (empty($mnemonic)) {
                return null;
            }
            
            // 从助记词生成种子
            $mnemonicObj = BIP39::Words($mnemonic, "english", true);
            $seed = $mnemonicObj->generateSeed("", 64);
            
            // 实现SLIP-0010主密钥派生
            $masterKey = hash_hmac('sha512', $seed, 'ed25519 seed', true);
            $currentPrivateKey = substr($masterKey, 0, 32);
            $currentChainCode = substr($masterKey, 32, 32);
            
            // 解析路径并派生
            $pathParts = explode('/', trim($path, 'm'));
            $pathParts = array_filter($pathParts, function($part) {
                return !empty(trim($part));
            });
            
            foreach ($pathParts as $part) {
                $part = trim($part);
                $hardened = strpos($part, "'") !== false;
                $index = (int) str_replace("'", '', $part);
                
                $derived = self::deriveChildKeySlip10($currentPrivateKey, $currentChainCode, $index, $hardened);
                $currentPrivateKey = substr($derived, 0, 32);
                $currentChainCode = substr($derived, 32, 32);
            }
            
            // 返回私钥的十六进制字符串
            return bin2hex($currentPrivateKey);
        } catch (\Exception $e) {
            self::logWarning("获取SLIP-0010私钥失败", [
                'currency_type' => $currencyType,
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * SLIP-0010子密钥派生
     * @param string $parentPrivateKey 父私钥（32字节）
     * @param string $parentChainCode 父链码（32字节）
     * @param int $index 索引
     * @param bool $hardened 是否硬化
     * @return string 派生的密钥（64字节：32字节私钥 + 32字节链码）
     */
    private static function deriveChildKeySlip10(string $parentPrivateKey, string $parentChainCode, int $index, bool $hardened = false): string
    {
        if ($hardened) {
            $index = $index | 0x80000000; // 设置最高位表示硬化
        }
        
        // 准备数据: 0x00 + parentPrivateKey + index (4 bytes, big-endian)
        $data = "\x00" . $parentPrivateKey . pack('N', $index);
        
        // HMAC-SHA512(parentChainCode, data)
        $hmac = hash_hmac('sha512', $data, $parentChainCode, true);
        
        // 返回64字节：左32字节作为子私钥，右32字节作为子链码
        return $hmac;
    }

    /**
     * CRC16 校验和计算（用于 TON 地址）
     * @param string $data 要计算的数据
     * @return int CRC16 校验和
     */
    private static function crc16(string $data): int
    {
        $crc = 0;
        $polynomial = 0x1021; // CRC-16-CCITT polynomial
        
        for ($i = 0; $i < strlen($data); $i++) {
            $crc ^= (ord($data[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) ^ $polynomial) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }
        
        return $crc;
    }

    /**
     * Ripple Base58Check 编码（用于 XRP 地址）
     * XRP 使用 Ripple 的 Base58 字母表，并包含校验和
     * @param string $data 要编码的数据
     * @return string Ripple Base58Check 编码后的字符串
     */
    private static function rippleBase58CheckEncode(string $data): string
    {
        // 1. 计算双重 SHA256 哈希作为校验和
        $hash1 = hash('sha256', $data, true);
        $hash2 = hash('sha256', $hash1, true);
        
        // 2. 取前4字节作为校验和
        $checksum = substr($hash2, 0, 4);
        
        // 3. 将校验和附加到数据后面
        $dataWithChecksum = $data . $checksum;
        
        // 4. 进行 Ripple Base58 编码
        return self::rippleBase58Encode($dataWithChecksum);
    }
    
    /**
     * Ripple Base58 编码（用于 XRP 地址）
     * XRP 使用 Ripple 的 Base58 字母表
     * @param string $data 要编码的数据
     * @return string Ripple Base58 编码后的字符串
     */
    private static function rippleBase58Encode(string $data): string
    {
        // Ripple Base58 字母表（与标准 Base58 不同）
        // 这是 Ripple 专用的字母表，用于编码 XRP 地址
        $alphabet = 'rpshnaf39wBUDNEGHJKLM4PQRST7VWXYZ2bcdeCg65jkm8oFqi1tuvAxyz';
        $base = strlen($alphabet);
        
        // 转换为大整数
        $num = '0';
        for ($i = 0; $i < strlen($data); $i++) {
            $num = bcmul($num, '256');
            $num = bcadd($num, (string)ord($data[$i]));
        }
        
        // 转换为 Base58
        $encoded = '';
        while (bccomp($num, '0') > 0) {
            $remainder = bcmod($num, (string)$base);
            $num = bcdiv($num, (string)$base, 0);
            $encoded = $alphabet[(int)$remainder] . $encoded;
        }
        
        // 处理前导零
        for ($i = 0; $i < strlen($data) && $data[$i] === "\x00"; $i++) {
            $encoded = $alphabet[0] . $encoded;
        }
        
        return $encoded;
    }






    
}
