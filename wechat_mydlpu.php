<?php

require_once 'vendor/autoload.php';

use Overtrue\Wechat\Server;
use Overtrue\Wechat\Message;

define('database_dlpu_userinfo_name', 'dlpu_userinfo');
define('collection_password_name', 'password');


$appId                          = 'wxbb7d2a97fd25ca97';
$secret                         = 'c434b2a5aefce35f44a1f88ffec1d323';
$token                          = 'wechat';
$encodingAESKey                 = '9FfoDHT1URoPvrXlgzrgAkDc4jozKhNMOcSITFpoARI';

$server = new Server($appId, $token, $encodingAESKey);

// 监听所有类型
$server->on('message', function($message) {
    return Message::make('text')->content('In development');
});

// 监听二维码扫描事件
$server->on('event', 'scancode_waitmsg', function($event) {
    return Message::make('text')->content($event['ScanCodeInfo']['ScanResult']);
});

// 监听菜单点击事件
$server->on('event', 'CLICK', function($event) {

    $message_news_binding = Message::make('news')->items(function() use ($event) {
        return array(
            Message::make('news_item')->title('您还没有绑定教务系统'),
            Message::make('news_item')->title('点此绑定')->url('https://api.xu42.cn/wechat/binding.php?wechat='.$event['FromUserName'].'&btn='.$event['EventKey'])->picUrl(''),
        );
    });

    $message_news_curriculum_weeks = Message::make('news')->items(function() use ($event) {
        return array(
            Message::make('news_item')->title('本周课表'),
            Message::make('news_item')->title('点此查看')->url('https://api.xu42.cn/wechat/btn_curriculum_weeks.php?wechat='.$event['FromUserName'])->picUrl(''),
        );
    });

    $message_news_curriculum_semester = Message::make('news')->items(function() use ($event) {
        return array(
            Message::make('news_item')->title('本学期课表'),
            Message::make('news_item')->title('点此查看')->url('https://api.xu42.cn/wechat/btn_curriculum_semester.php?wechat='.$event['FromUserName'])->picUrl(''),
        );
    });

    $message_news_curriculum_today = Message::make('news')->items(function() use ($event) {
        return array(
            Message::make('news_item')->title('今日课表'),
            Message::make('news_item')->title('点此查看')->url('https://api.xu42.cn/wechat/btn_curriculum_today.php?wechat='.$event['FromUserName'])->picUrl(''),
        );
    });

    $message_news_curriculum_tomorrow = Message::make('news')->items(function() use ($event) {
        return array(
            Message::make('news_item')->title('明日课表'),
            Message::make('news_item')->title('点此查看')->url('https://api.xu42.cn/wechat/btn_curriculum_tomorrow.php?wechat='.$event['FromUserName'])->picUrl(''),
        );
    });

    $message_news_score_all = Message::make('news')->items(function() use ($event) {
        return array(
            Message::make('news_item')->title('所有成绩'),
            Message::make('news_item')->title('点此查看')->url('https://api.xu42.cn/wechat/btn_score_all.php?wechat='.$event['FromUserName'])->picUrl(''),
        );
    });

    $message_news_score_semester = Message::make('news')->items(function() use ($event) {
        return array(
            Message::make('news_item')->title('本学期成绩'),
            Message::make('news_item')->title('点此查看')->url('https://api.xu42.cn/wechat/btn_score_semester.php?wechat='.$event['FromUserName'])->picUrl(''),
        );
    });

    $message_news_exam_arrangement = Message::make('news')->items(function() use ($event) {
        return array(
            Message::make('news_item')->title('本学期考试安排'),
            Message::make('news_item')->title('点此查看')->url('https://api.xu42.cn/wechat/btn_exam_arrangement.php?wechat='.$event['FromUserName'])->picUrl(''),
        );
    });

    switch($event['EventKey'])
    {
        case 'btn_curriculum_today':
            if(!getSimpleUserinfoByWechat($event['FromUserName'])) return $message_news_binding;  // 此微信账户 未绑定教务系统, 执行绑定
//            require_once 'v1/dlpu/mydlpu_handle.php';

//            $mydlpu_handle = new  mydlpu_handle();
//            $current_week = $mydlpu_handle->getCurrentWeek();
//            $username = $mydlpu_handle->getSimpleUserinfoByWechat($event['FromUserName']);
//            $json = $mydlpu_handle->getCurriculumWeeks($username, SEMESTER, '1', $event['FromUserName']);
//            $curriculum_weeks = json_decode($json, 1);
//            return Message::make('text')->content($json);
//
//            for($i=0; $i<6;$i++)
//            {
//                if(@$curriculum_weeks['data'][$i][getCurrentDay()])
//                {
//                    $curriculum[] = $mydlpu_handle->translationToHans($i) . " · " . $curriculum_weeks['data'][$i][$mydlpu_handle->getCurrentDay()][2] . " · " . $curriculum_weeks['data'][$i][$mydlpu_handle->getCurrentDay()][0];
//                }
//            }
//
//            return Message::make('news')->items(function() use ($curriculum) {
//                return array(
//                    Message::make('news_item')->title('今日课表'),
//                    Message::make('news_item')->title($curriculum[1])
//                );
//            });

            return $message_news_curriculum_today;
            break;
        case 'btn_curriculum_tomorrow':
            if(!getSimpleUserinfoByWechat($event['FromUserName'])) return $message_news_binding;  // 此微信账户 未绑定教务系统, 执行绑定
            return $message_news_curriculum_tomorrow;
            break;
        case 'btn_curriculum_weeks':
            if(!getSimpleUserinfoByWechat($event['FromUserName'])) return $message_news_binding;  // 此微信账户 未绑定教务系统, 执行绑定
            return $message_news_curriculum_weeks;
            break;
        case 'btn_curriculum_semester':
            if(!getSimpleUserinfoByWechat($event['FromUserName'])) return $message_news_binding;  // 此微信账户 未绑定教务系统, 执行绑定
            return $message_news_curriculum_semester;
            break;
        case 'btn_score_semester':
            if(!getSimpleUserinfoByWechat($event['FromUserName'])) return $message_news_binding;  // 此微信账户 未绑定教务系统, 执行绑定
            return $message_news_score_semester;
            break;
        case 'btn_score_all':
            if(!getSimpleUserinfoByWechat($event['FromUserName'])) return $message_news_binding;  // 此微信账户 未绑定教务系统, 执行绑定
            return $message_news_score_all;
            break;
        case 'btn_exam_arrangement':
            if(!getSimpleUserinfoByWechat($event['FromUserName'])) return $message_news_binding;  // 此微信账户 未绑定教务系统, 执行绑定
            return $message_news_exam_arrangement;
        default:
            return Message::make('text')->content('In development');
            break;
    }

});



$result = $server->serve();
echo $result;

/**
 * 获取简单的用户信息 包括学号、密码
 * @param $wechat_id
 * @return object for存在该条信息（即 微信已与教务系统绑定）, boolean FALSE for不存在
 */
function getSimpleUserinfoByWechat ($wechat_id)
{
    require_once 'v1/dlpu/student_database_tools.php';
    $db = new student_database_tools(database_dlpu_userinfo_name, collection_password_name);
    return $db->getPasswordFromDatabaseByWechatId($wechat_id);
}
