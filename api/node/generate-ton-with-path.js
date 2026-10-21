/**
 * 使用正确的BIP44路径 m/44'/607'/0' 生成TON地址
 */

const { mnemonicToSeed, deriveEd25519Path, keyPairFromSeed } = require('@ton/crypto');
const ton = require('@ton/ton');

async function generateTonAddressWithPath(mnemonicWords) {
    const mnemonic = mnemonicWords.split(' ');
    
    // 1. 生成种子（BIP39标准，使用空passphrase）
    const seed = await mnemonicToSeed(mnemonic, '');
    
    // 2. 使用BIP44路径 m/44'/607'/0' 派生私钥
    // deriveEd25519Path接受seed和路径数组，路径数组会自动处理硬化派生
    const path = [44, 607, 0];
    const derivedKey = await deriveEd25519Path(seed, path);
    
    // derivedKey是64字节Buffer：[privateKey(32), chainCode(32)]
    const privateKey = derivedKey.slice(0, 32);
    
    // 4. 从派生私钥生成密钥对
    const keyPair = keyPairFromSeed(privateKey);
    
    console.log('私钥(Hex):', keyPair.secretKey.toString('hex'));
    console.log('公钥(Hex):', keyPair.publicKey.toString('hex'));
    
    // 5. 创建钱包v4
    const wallet = ton.WalletContractV4.create({
        workchain: 0,
        publicKey: keyPair.publicKey,
    });
    
    // 6. 获取地址
    const address = wallet.address;
    const nonBounceable = address.toString({ urlSafe: true, bounceable: false, testOnly: false });
    const bounceable = address.toString({ urlSafe: true, bounceable: true, testOnly: false });
    
    console.log('\n生成的地址:');
    console.log('不可弹回:', nonBounceable);
    console.log('可弹回:  ', bounceable);
    
    return {
        privateKey: keyPair.secretKey.toString('hex'),
        publicKey: keyPair.publicKey.toString('hex'),
        nonBounceableAddress: nonBounceable,
        bounceableAddress: bounceable,
    };
}

const mnemonic = process.argv[2] || "session deer fatigue glimpse call run brain drastic inquiry amateur copper hill";

generateTonAddressWithPath(mnemonic)
    .then(result => {
        console.log('\n=== JSON结果 ===');
        console.log(JSON.stringify(result, null, 2));
    })
    .catch(error => {
        console.error('错误:', error.message);
        console.error(error.stack);
    });

