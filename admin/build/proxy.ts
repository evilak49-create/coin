import type { ProxyOptions } from "vite";

type ProxyItem = [string, string];

type ProxyList = ProxyItem[];

type ProxyTargetList = Record<string, ProxyOptions>;

/**
 * 创建代理，用于解析 .env.development 代理配置
 * @param list
 */
export function createProxy(list: ProxyList = []) {
  const ret: ProxyTargetList = {};
  
  for (const [prefix, target] of list) {
    const httpsRE = /^https:\/\//;
    const isHttps = httpsRE.test(target);

    // https://github.com/http-party/node-http-proxy#options
    // 代理配置说明：
    // 当 target 以 /api 结尾时（如 http://coin.st:20080/api）：
    //   - 请求 /api/admin/auth/login
    //   - rewrite 去掉 /api 前缀 → admin/auth/login，然后添加 /api → /api/admin/auth/login
    //   - 最终：http://coin.st:20080/api/admin/auth/login ✓
    // 
    // 当 target 不以 /api 结尾时（如 http://127.0.0.1:25200）：
    //   - 请求 /api/admin/auth/login
    //   - rewrite 保留完整路径 → /api/admin/auth/login
    //   - 最终：http://127.0.0.1:25200/api/admin/auth/login ✓
    const targetEndsWithApi = target.endsWith('/api');
    
    // 解析 target URL 以获取 hostname 和 port
    const targetUrl = new URL(target);
    const targetHost = targetUrl.hostname;
    const targetPort = targetUrl.port || (targetUrl.protocol === 'https:' ? '443' : '80');
    
    // 如果 hostname 是域名（不是 IP），需要确保 Host 头正确设置
    const isDomain = !/^\d+\.\d+\.\d+\.\d+$/.test(targetHost) && targetHost !== 'localhost' && targetHost !== '127.0.0.1';
    
    // 构建正确的 target URL
    // 如果 target 以 /api 结尾，需要去掉路径部分，因为 rewrite 会处理路径
    // 如果 target 不以 /api 结尾，保留完整 target
    let finalTarget = target;
    if (targetEndsWithApi) {
      // target 以 /api 结尾，去掉路径部分，只保留协议、主机和端口
      finalTarget = `${targetUrl.protocol}//${targetHost}${targetPort ? `:${targetPort}` : ''}`;
    }
    
    ret[prefix] = {
      target: finalTarget,
      changeOrigin: true,
      ws: true,
      // 根据 target 是否以 /api 结尾决定 rewrite 规则
      rewrite: targetEndsWithApi
        ? (path) => {
            // 去掉 /api 前缀，然后添加 /api 前缀（因为 target 的 /api 已经被去掉）
            const newPath = path.replace(new RegExp(`^${prefix}`), "");
            return `/api${newPath}`;
          }
        : (path) => path,
      // 配置代理请求，确保 Host 头正确设置
      configure: (proxy, _options) => {
        proxy.on('proxyReq', (proxyReq, req, res) => {
          // 如果是域名，确保 Host 头设置为原始域名
          if (isDomain) {
            const hostHeader = targetPort === '80' || targetPort === '443' 
              ? targetHost 
              : `${targetHost}:${targetPort}`;
            proxyReq.setHeader('Host', hostHeader);
          }
        });
      },
      // 添加超时选项
      timeout: 30000,
      // https is require secure=false
      ...(isHttps ? { secure: false } : {})
    };
  }
  
  return ret;
}
