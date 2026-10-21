/**
 * 获取TON钱包v3的代码hash
 */

const ton = require('@ton/ton');
const { Cell } = require('@ton/core');

(async function() {
    try {
        // 创建一个临时钱包来获取代码
        const { mnemonicToPrivateKey } = require('@ton/crypto');
        const keyPair = await mnemonicToPrivateKey(['test']);
        
        const wallet = ton.WalletContractV3R1.create({
            workchain: 0,
            publicKey: keyPair.publicKey,
        });
        
        // 获取StateInit，其中包含代码
        const stateInit = wallet.init;
        
        if (stateInit && stateInit.code) {
            const code = stateInit.code;
            if (code instanceof Cell) {
                const codeHash = code.hash();
                console.log('TON钱包v3代码hash:', codeHash.toString('hex'));
                console.log('代码hash长度:', codeHash.length, '字节');
            } else {
                console.log('代码类型:', typeof code);
            }
        } else {
            console.log('无法获取StateInit或代码');
            console.log('StateInit:', stateInit);
        }
        
        // 尝试直接访问code属性
        try {
            const codeDirect = ton.WalletContractV3R1.code;
            console.log('\n直接访问code:', typeof codeDirect);
            if (codeDirect) {
                console.log('code对象:', Object.keys(codeDirect));
            }
        } catch (e) {
            console.log('\n无法直接访问code属性');
        }
        
    } catch (error) {
        console.error('错误:', error.message);
        console.error(error.stack);
    }
})();
