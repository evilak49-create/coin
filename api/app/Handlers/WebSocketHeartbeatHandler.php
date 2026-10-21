<?php

namespace App\Handlers;

use App\WebSocket\WebSocketHandler;
use Hhxsv5\LaravelS\Swoole\Events\WorkerStartInterface;
use Swoole\Http\Server;

class WebSocketHeartbeatHandler implements WorkerStartInterface
{
    /**
     * 构造函数（接口要求）
     */
    public function __construct()
    {
        // 无参构造函数
    }

    /**
     * Worker 启动时设置心跳定时器（接口要求的方法名）
     *
     * @param Server $server
     * @param int $workerId
     * @return void
     */
    public function handle(Server $server, $workerId)
    {
        // 在每个 Worker 进程中设置定时器（因为连接分布在不同 Worker 中）
        // 从配置文件读取心跳间隔时间（毫秒），默认30秒
        $interval = config('laravels.websocket.heartbeat_interval', 30000);
        
        \Swoole\Timer::tick($interval, function () use ($server, $workerId) {
            $this->sendHeartbeat($server, $workerId);
        });
        
        \Log::info("WebSocket 心跳定时器已启动", [
            'worker_id' => $workerId,
            'interval' => $interval,
            'interval_seconds' => $interval / 1000
        ]);
    }

    /**
     * 向所有连接的客户端发送心跳
     *
     * @param Server $server
     * @return void
     */
    protected function sendHeartbeat(Server $server, $workerId)
    {
        if (!$server instanceof \Swoole\WebSocket\Server) {
            \Log::debug("发送心跳: Server 不是 WebSocket Server");
            return;
        }

        // 只获取当前 Worker 进程中的连接（因为不同 Worker 的 fd 不能跨进程访问）
        $connections = WebSocketHandler::getConnections();
        
        if (empty($connections)) {
            \Log::debug("发送心跳: Worker {$workerId} 没有连接");
            return;
        }

        $heartbeatCount = 0;
        $invalidFds = [];
        
        foreach ($connections as $fd => $connection) {
            try {
                // 发送心跳消息（使用 Pusher 协议的 ping 事件）
                $message = json_encode([
                    'event' => 'pusher:ping',
                    'data' => []
                ]);
                
                // 尝试推送，如果失败说明连接已断开
                $result = $server->push($fd, $message);
                if ($result === false) {
                    // 推送失败，连接可能已断开
                    $invalidFds[] = $fd;
                    continue;
                }
                
                $heartbeatCount++;
            } catch (\Exception $e) {
                \Log::warning("发送心跳失败", [
                    'fd' => $fd,
                    'worker_id' => $workerId,
                    'error' => $e->getMessage()
                ]);
                // 记录无效连接
                $invalidFds[] = $fd;
            }
        }

        // 清理无效连接
        foreach ($invalidFds as $fd) {
            WebSocketHandler::removeConnection($fd);
        }
        
        if (!empty($invalidFds)) {
            \Log::info("清理无效连接", [
                'worker_id' => $workerId,
                'invalid_fds' => $invalidFds,
                'count' => count($invalidFds)
            ]);
        }

        if ($heartbeatCount > 0) {
            \Log::debug("发送心跳消息", [
                'worker_id' => $workerId,
                'count' => $heartbeatCount,
                'total_connections' => count($connections)
            ]);
        }
    }
}

