<?php

require 'vendor/autoload.php';

use Overtrue\Wechat\Server;
use Overtrue\Wechat\Message;


$appId          = 'wxbb7d2a97fd25ca97';
$secret         = 'c434b2a5aefce35f44a1f88ffec1d323';
$token          = 'wechat';
$encodingAESKey = '9FfoDHT1URoPvrXlgzrgAkDc4jozKhNMOcSITFpoARI';

$server = new Server($appId, $token, $encodingAESKey);

// 监听所有类型
$server->on('message', function($message) {
    return Message::make('text')->content($message->FromUserName);
});





$result = $server->serve();
echo $result;