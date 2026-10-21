<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// 设备专属通道 - 通过设备号订阅
Broadcast::channel('device.{deviceNumber}', function ($user, $deviceNumber) {
    // 这里可以添加权限验证逻辑
    // 例如：检查用户是否有权限查看该设备
    // 如果需要认证，可以使用中间件进行验证
    return true;
});

// 设备列表更新通道 - 管理员使用
Broadcast::channel('device.list', function ($user) {
    // 这里可以添加管理员权限验证
    // 例如：检查用户是否是管理员
    return true;
});
