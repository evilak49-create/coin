/**
 * 使用TON官方SDK生成钱包地址
 * 需要先安装: npm install @ton/ton @ton/crypto
 */

// 检查是否安装了TON SDK
try {
    const { mnemonicToPrivateKey } = require('@ton/crypto');
    const ton = require('@ton/ton');
    
    async function generateTonAddress(mnemonicWords) {
        // 将助记词字符串转换为数组
        const mnemonic = mnemonicWords.split(' ');
        
        // 1. 从助记词生成私钥
        // 使用BIP44路径 m/44'/607'/0' 派生私钥
        // TON SDK的mnemonicToPrivateKey使用默认路径，我们需要手动实现
        const { mnemonicToSeed, getED25519MasterKeyFromSeed, deriveED25519HardenedKey, keyPairFromSeed } = require('@ton/crypto');
        
        // 生成种子
        const seed = await mnemonicToSeed(mnemonic);
        
        // 获取主密钥（返回64字节：32字节私钥 + 32字节链码）
        const masterKey = getED25519MasterKeyFromSeed(seed);
        
        // 手动实现BIP44路径 m/44'/607'/0'
        // masterKey是64字节数组：[privateKey(32), chainCode(32)]
        let currentKey = masterKey;
        
        // m/44' (硬化派生)
        currentKey = deriveED25519HardenedKey(currentKey.slice(0, 32), currentKey.slice(32, 64), 44);
        // m/44'/607' (硬化派生)
        currentKey = deriveED25519HardenedKey(currentKey.slice(0, 32), currentKey.slice(32, 64), 607);
        // m/44'/607'/0' (硬化派生)
        currentKey = deriveED25519HardenedKey(currentKey.slice(0, 32), currentKey.slice(32, 64), 0);
        
        // 从派生密钥生成密钥对
        const keyPair = keyPairFromSeed(Buffer.from(currentKey.slice(0, 32)));
        
        console.log('私钥(Hex):', Buffer.from(keyPair.secretKey).toString('hex'));
        console.log('公钥(Hex):', Buffer.from(keyPair.publicKey).toString('hex'));
        
        // 2. 创建钱包v3合约
        // TON SDK中有WalletContractV3R1和WalletContractV3R2
        // 使用V3R1（标准v3钱包）
        const WalletContractV3R1 = ton.WalletContractV3R1;
        
        const wallet = WalletContractV3R1.create({
            workchain: 0,
            publicKey: keyPair.publicKey,
        });
        
        // 3. 获取钱包地址
        const address = wallet.address;
        
        // 4. 转换为不可弹回地址
        const nonBounceableAddress = address.toString({
            urlSafe: true,
            bounceable: false,
            testOnly: false,
        });
        
        // 5. 转换为可弹回地址
        const bounceableAddress = address.toString({
            urlSafe: true,
            bounceable: true,
            testOnly: false,
        });
        
        console.log('\n生成的地址:');
        console.log('不可弹回地址:', nonBounceableAddress);
        console.log('可弹回地址:  ', bounceableAddress);
        
        // 6. 获取代码hash（用于PHP实现）
        let codeHashHex = null;
        try {
            const code = WalletContractV3R1.code;
            // 代码是一个Cell对象，需要计算其hash
            if (code && typeof code.hash === 'function') {
                const codeHash = code.hash();
                codeHashHex = codeHash.toString('hex');
                console.log('\n钱包v3代码hash:', codeHashHex);
            } else {
                // 尝试其他方式获取代码hash
                const { Cell } = require('@ton/core');
                if (code instanceof Cell) {
                    codeHashHex = code.hash().toString('hex');
                    console.log('\n钱包v3代码hash:', codeHashHex);
                }
            }
        } catch (e) {
            console.log('\n无法获取代码hash:', e.message);
            console.log('可以使用钱包地址来验证地址生成是否正确');
        }
        
        const result = {
            privateKey: Buffer.from(keyPair.secretKey).toString('hex'),
            publicKey: Buffer.from(keyPair.publicKey).toString('hex'),
            nonBounceableAddress: nonBounceableAddress,
            bounceableAddress: bounceableAddress,
        };
        
        if (codeHashHex) {
            result.codeHash = codeHashHex;
        }
        
        return result;
    }
    
    // 从命令行参数获取助记词
    const mnemonic = process.argv[2] || "session deer fatigue glimpse call run brain drastic inquiry amateur copper hill";
    
    generateTonAddress(mnemonic)
        .then(result => {
            console.log('\n=== 完整结果 ===');
            console.log(JSON.stringify(result, null, 2));
        })
        .catch(error => {
            console.error('错误:', error.message);
            process.exit(1);
        });
    
} catch (error) {
    console.error('错误: TON SDK未安装');
    console.error('请先安装: npm install @ton/ton @ton/crypto');
    console.error('\n或者使用以下方法:');
    console.error('1. 安装Node.js和npm');
    console.error('2. 运行: npm install @ton/ton @ton/crypto');
    console.error('3. 运行: node generate-ton-address.js "your mnemonic words"');
    process.exit(1);
}

