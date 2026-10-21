/**
 * 从公钥生成TON地址（不涉及密钥派生）
 * 使用方法: node generate-ton-from-publickey.js <public_key_hex>
 */

const ton = require('@ton/ton');

function generateTonAddressFromPublicKey(publicKeyHex) {
    const publicKey = Buffer.from(publicKeyHex, 'hex');
    
    if (publicKey.length !== 32) {
        throw new Error('公钥必须是32字节');
    }
    
    // 创建钱包v4
    const wallet = ton.WalletContractV4.create({
        workchain: 0,
        publicKey: publicKey,
    });
    
    // 获取地址
    const address = wallet.address;
    const nonBounceable = address.toString({ urlSafe: true, bounceable: false, testOnly: false });
    const bounceable = address.toString({ urlSafe: true, bounceable: true, testOnly: false });
    
    console.log('公钥(Hex):', publicKeyHex);
    console.log('\n生成的地址:');
    console.log('不可弹回:', nonBounceable);
    console.log('可弹回:  ', bounceable);
    
    return {
        publicKey: publicKeyHex,
        nonBounceableAddress: nonBounceable,
        bounceableAddress: bounceable,
    };
}

const publicKeyHex = process.argv[2];

if (!publicKeyHex) {
    console.error('使用方法: node generate-ton-from-publickey.js <public_key_hex>');
    process.exit(1);
}

try {
    const result = generateTonAddressFromPublicKey(publicKeyHex);
    console.log('\n=== JSON结果 ===');
    console.log(JSON.stringify(result, null, 2));
} catch (error) {
    console.error('错误:', error.message);
    console.error(error.stack);
    process.exit(1);
}

