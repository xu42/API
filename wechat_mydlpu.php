<?php
error_reporting(0);
require_once 'vendor/autoload.php';
require_once 'v1/dlpu/mydlpu_handle.php';
use Overtrue\Wechat\Server;
use Overtrue\Wechat\Message;

$appId                          = 'wxbb7d2a97fd25ca97';
$secret                         = 'c434b2a5aefce35f44a1f88ffec1d323';
$token                          = 'wechat';
$encodingAESKey                 = '9FfoDHT1URoPvrXlgzrgAkDc4jozKhNMOcSITFpoARI';

$server = new Server($appId, $token, $encodingAESKey);

// 监听关注事件
$server->on('event', 'subscribe', function($event) {
    return Message::make('text')->content('专为大连工大学子定制，简洁高效的查询成绩、课表和考试安排等信息');
});

// 监听所有类型
//$server->on('message', function($message) {
//    return Message::make('text')->content('In development');
//});

// 监听图片
//$server->on('message', 'image', function($message) {
//    return Message::make('text')->content($message['MediaId']);
//});

// 监听文本内容
$server->on('message', 'text', function($message) {
    if(substr($message['Content'],0,3) == 'cet' or substr($message['Content'],0,3) == 'CET')
    {
//        return Message::make('text')->content("2016年2月26日上午9时发布");
        preg_match_all('/\d+/', $message['Content'], $number);
        $name = substr($message['Content'],3, -15);
        $mydlpu_handle = new mydlpu_handle();
        $cet_score = $mydlpu_handle->getCETScore($name, $number[0][0]);
        $cet_score = json_decode($cet_score, 1);
        if($cet_score['messages'] == 'error') return Message::make('text')->content($cet_score['data']);
        if($cet_score['messages'] == 'success')
        {
            $res = $cet_score['data']['0'] . ' : '. $cet_score['data']['1'] . "\n" . $cet_score['data']['10'] . ' : ' . $cet_score['data']['11']. "\n" . $cet_score['data']['12'] . ' : ' . $cet_score['data']['13']. "\n" . $cet_score['data']['14'] . ' : ' . $cet_score['data']['15']. "\n" . $cet_score['data']['16'] . ' : ' . $cet_score['data']['17'];
            return Message::make('text')->content($res);
        }
    }

    // 我的课程 网络安全
    if($message['Content'] == '小飞飞')
    {
        return Message::make('text')->content('<a href="http://xu42.file.alimmdn.com/1460876500439.pdf">利用恶意代码入侵服务器</a>');
    }

    // 我的课程 网络安全
    if($message['Content'] == '入侵软件')
    {
        return Message::make('text')->content('<a href="http://share.weiyun.com/805e1ce8fc20716e59437b44532b690e">利用恶意代码入侵服务器 - 软件下载</a>');
    }

    // 印乐签到脚本
    if(substr($message['Content'],0,6) == '印乐')
    {
        require_once 'v1/dlpu/yinle_sign.php';
        $phone = substr($message['Content'],6);
        $yinle = new yinle();
        $res = $yinle->index($phone);
        return Message::make('text')->content($res);
    }
});

// 监听二维码扫描事件
$server->on('event', 'scancode_waitmsg', function($event) {
    if(!getSimpleUserinfoByWechat($event['FromUserName'])) return Message::make('news')->items(function() use ($event) {
        return array(
            Message::make('news_item')->title('您还没有绑定教务系统'),
            Message::make('news_item')->title('点此绑定')->url('https://api.xu42.cn/wechat/binding.php?wechat='.$event['FromUserName'].'&btn='.$event['EventKey'])->picUrl(''),
        );
    });  // 此微信账户 未绑定教务系统, 执行绑定
    $mydlpu_handle = new mydlpu_handle();

    $qrcode_base64 = substr($event['ScanCodeInfo']['ScanResult'], 3);   // 所扫的码
    $username = $mydlpu_handle->getUsernameByWechat($event['FromUserName']);    // 学号
    $qrcode_req = base64_decode($qrcode_base64) . '|' . $username . '|' . time();   // 学生签到发送的数据
    $res_json = $mydlpu_handle->getStudentRollcallResult('wec'.base64_encode($qrcode_req));  // 加入3个无关字符，获取扫描结果

    //return Message::make('text')->content('wec'.base64_encode($qrcode_req));
    //return Message::make('text')->content("扫码得到的信息:".$qrcode_base64."\n"."学号信息:".$username."\n"."学生签到发送的数据:".$qrcode_req."\n"."入3个无关字符，获取扫描结果:".$res_json."\n");
    $response = '二维码错误, 签到失败';
    $res = json_decode($res_json, TRUE);
    if($res['data'] && $res['messages'] == 'OK'){
        $response = '学号:' . $res['data']['7'] . "\n" . '教室:' . $res['data']['1'] . "\n\n" . '签到成功';
    }
    if($res['data'] && $res['messages'] == 'error'){
        $response = $res['data'];
    }
    return Message::make('text')->content($response);
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

    /**
     * 构造微信课表图文
     * @param $curriculum_array
     * @return mixed
     */
    function message_news_curriculum ($curriculum_array)
    {
        if(strlen($curriculum_array[1]) == 0) return Message::make('text')->content('无课哟');

        return Message::make('news')->items(function() use ($curriculum_array) {
            return array(
                Message::make('news_item')->title($curriculum_array[0])->description($curriculum_array[1])
            );
        });
    }


    $mydlpu_handle = new mydlpu_handle();
    $current_semester ='2015-2016-2';
    $previous_semester ='2015-2016-1';
    $current_week = $mydlpu_handle->getCurrentWeek();
    $userinfo = $mydlpu_handle->getSimpleUserinfoByWechat($event['FromUserName']);
    $username = $userinfo->_id;

    $curriculum_weeks = json_decode($mydlpu_handle->getCurriculumWeeks($username, $current_semester, $current_week, $event['FromUserName']), 1);
    $score_semester = json_decode($mydlpu_handle->getScore($username, $event['FromUserName'], $current_semester), 1);
    $exam_arrangement = json_decode($mydlpu_handle->getExamArrangement($username, $current_semester, $event['FromUserName']), 1);

    switch($event['EventKey'])
    {
        case 'btn_curriculum_today':
            if(!getSimpleUserinfoByWechat($event['FromUserName'])) return $message_news_binding;  // 此微信账户 未绑定教务系统, 执行绑定

            $today = '';
            for($i=0; $i<6;$i++)
            {
                if(@$curriculum_weeks['data'][$i][$mydlpu_handle->getCurrentDay()])
                {
                    $today .= $mydlpu_handle->translationToHans($i) . "   " . $curriculum_weeks['data'][$i][$mydlpu_handle->getCurrentDay()][2] . "   " . $curriculum_weeks['data'][$i][$mydlpu_handle->getCurrentDay()][0] . "\n";
                }
            }
            return message_news_curriculum(['今日课表', $today]);
            break;
        case 'btn_curriculum_tomorrow':
            if(!getSimpleUserinfoByWechat($event['FromUserName'])) return $message_news_binding;  // 此微信账户 未绑定教务系统, 执行绑定
            $tomorrow = '';
            for($i=0; $i<6;$i++)
            {
                if(@$curriculum_weeks['data'][$i][$mydlpu_handle->getTomorrowDay()])
                {
                    $tomorrow .= $mydlpu_handle->translationToHans($i) . "   " . $curriculum_weeks['data'][$i][$mydlpu_handle->getTomorrowDay()][2] . "   " . $curriculum_weeks['data'][$i][$mydlpu_handle->getTomorrowDay()][0] . "\n";
                }
            }
            return message_news_curriculum(['明日课表', $tomorrow]);
            break;
        case 'btn_curriculum_weeks':
            if(!getSimpleUserinfoByWechat($event['FromUserName'])) return $message_news_binding;  // 此微信账户 未绑定教务系统, 执行绑定
            return $message_news_curriculum_weeks;
            break;
        case 'btn_curriculum_semester':
            if(!getSimpleUserinfoByWechat($event['FromUserName'])) return $message_news_binding;  // 此微信账户 未绑定教务系统, 执行绑定
            return $message_news_curriculum_semester;
            break;
        case 'btn_xiaoli':
            return Message::make('image')->media_id('SjpYQYO6pktZABBZ86eoZid9z0aQZfDDfGmKqnEg-sLwcoDUR4imPbb2WXSAVt5u');
        case 'btn_score_semester':
            if(!getSimpleUserinfoByWechat($event['FromUserName'])) return $message_news_binding;  // 此微信账户 未绑定教务系统, 执行绑定
            $score = '同学: ' . $username . "\n" . "本学期的成绩如下\n\n";

            for($i=1; $i<count($score_semester['data']['1']);$i++)
            {
                if(@$score_semester['data']['1'][$i])
                {
                    $course_title = $course_title_short = $score_semester['data']['1'][$i]['3'];
                    if(strlen($course_title) > 27){
                        $course_title_short = substr($course_title, 0, 27) . '...';
                    }
                    $score .= $course_title_short . " : " . $score_semester['data']['1'][$i]['4'] . "\n";
                }
            }
            return Message::make('text')->content($score);
            break;
        case 'btn_score_all':
            if(!getSimpleUserinfoByWechat($event['FromUserName'])) return $message_news_binding;  // 此微信账户 未绑定教务系统, 执行绑定
            return $message_news_score_all;
            break;
        case 'btn_exam_arrangement':
            if(!getSimpleUserinfoByWechat($event['FromUserName'])) return $message_news_binding;  // 此微信账户 未绑定教务系统, 执行绑定
            $exam = '';
            for($i=1; $i<count($exam_arrangement['data']);$i++)
            {
                if(@$exam_arrangement['data'][$i])
                {
                    $exam .= $exam_arrangement['data'][$i]['3'] .' '. $exam_arrangement['data'][$i]['5'] .' '. $exam_arrangement['data'][$i]['4'] . "\n";
                }
            }
            return message_news_curriculum(['考试安排', $exam]);
//            return $message_news_exam_arrangement;
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
