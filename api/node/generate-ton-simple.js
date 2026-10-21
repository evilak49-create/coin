/**
 * 简化版本：使用TON SDK生成地址
 */

const { mnemonicToPrivateKey } = require('@ton/crypto');
const ton = require('@ton/ton');

async function generateAddress(mnemonicWords) {
    const mnemonic = mnemonicWords.split(' ');
    
    // 使用TON SDK默认方法生成私钥
    const keyPair = await mnemonicToPrivateKey(mnemonic);
    
    console.log('私钥(Hex):', Buffer.from(keyPair.secretKey).toString('hex'));
    console.log('公钥(Hex):', Buffer.from(keyPair.publicKey).toString('hex'));
    
    // 创建钱包v4
    const wallet = ton.WalletContractV4.create({
        workchain: 0,
        publicKey: keyPair.publicKey,
    });
    
    const address = wallet.address;
    const nonBounceable = address.toString({ urlSafe: true, bounceable: false, testOnly: false });
    const bounceable = address.toString({ urlSafe: true, bounceable: true, testOnly: false });
    
    console.log('\n生成的地址:');
    console.log('不可弹回:', nonBounceable);
    console.log('可弹回:  ', bounceable);
    
    // 尝试获取代码hash
    try {
        const { Cell } = require('@ton/core');
        const code = ton.WalletContractV3R1.code;
        if (code instanceof Cell) {
            const codeHash = code.hash();
            console.log('\n代码hash:', codeHash.toString('hex'));
        }
    } catch (e) {
        console.log('\n无法获取代码hash');
    }
    
    return {
        privateKey: Buffer.from(keyPair.secretKey).toString('hex'),
        publicKey: Buffer.from(keyPair.publicKey).toString('hex'),
        nonBounceableAddress: nonBounceable,
        bounceableAddress: bounceable,
    };
}

const mnemonic = process.argv[2] || "session deer fatigue glimpse call run brain drastic inquiry amateur copper hill";

generateAddress(mnemonic)
    .then(result => {
        console.log('\n=== JSON结果 ===');
        console.log(JSON.stringify(result, null, 2));
    })
    .catch(error => {
        console.error('错误:', error.message);
        console.error(error.stack);
    });

