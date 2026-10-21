#!/bin/bash
# 安装TON SDK的脚本

echo "=================================="
echo "安装TON SDK"
echo "=================================="

cd /wwwroot/suizhi/coin

# 检查Node.js
if ! command -v node &> /dev/null; then
    echo "错误: Node.js未安装"
    exit 1
fi

echo "Node.js版本: $(node --version)"
echo "npm版本: $(npm --version)"

# 初始化package.json（如果不存在）
if [ ! -f package.json ]; then
    echo "初始化package.json..."
    npm init -y
fi

# 安装TON SDK
echo "正在安装TON SDK..."
npm install @ton/ton @ton/crypto

if [ $? -eq 0 ]; then
    echo "✓ TON SDK安装成功"
    echo ""
    echo "现在可以使用以下命令生成地址:"
    echo "  node generate-ton-address.js \"your mnemonic words\""
    echo "  或"
    echo "  php generate-ton-address-php.php \"your mnemonic words\""
else
    echo "× TON SDK安装失败"
    exit 1
fi

