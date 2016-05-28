<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2016/1/19
 * Time: 11:16
 */
require_once '../v1/dlpu/mydlpu_handle.php';

$res = ['messages' => TRUE, 'data' => ''];

if(!is_numeric($_POST['username']) or strlen($_POST['username']) != 10) {
    $res = ['messages' => FALSE, 'data' => '学号格式错误'];
}
if(is_null($_POST['password'])) {
    $res = ['messages' => FALSE, 'data' => '密码不存在'];
}
if(is_null($_POST['wechat'])) {
    $res = ['messages' => FALSE, 'data' => '未获取到微信帐号'];
}

if($res['messages']) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $wechat = $_POST['wechat'];
    $btn = $_POST['btn'];
    $url = 'https://api.xu42.cn/wechat/'.$btn.'.php?wechat='.$wechat;

    $mydlpu_handle = new mydlpu_handle();
    $json = $mydlpu_handle->bindingWechatWithUsername($username, $password, $wechat);
    $res = json_decode($json, 1);
}

?>

<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <link rel="stylesheet" href="../assets/css/weui.min.css"/>
</head>
<body>
<div class="weui_msg">
    <div class="weui_icon_area"><i class="<?= ($res['messages'] == 'OK') ? "weui_icon_success" : "weui_icon_warn" ?> weui_icon_msg"></i></div>
    <div class="weui_text_area">
        <h2 class="weui_msg_title"><?= $res['data']?></h2>
        <p class="weui_msg_desc"><?= ($res['messages'] == 'OK') ? "您的微信已与教务系统绑定成功,可使用一键查询成绩和课表等服务" : NULL ?></p>
    </div>
    <div class="weui_opr_area">
        <p class="weui_btn_area">
            <?php if($res['messages'] == 'OK') { ?>
<!--                <button onclick="location.href='--><?//=$url ?>//'" class="weui_btn weui_btn_primary">确定</button>
                <button onclick="WeixinJSBridge.call('closeWindow');" class="weui_btn weui_btn_primary">确定</button>
            <?php }else{ ?>
                <button onclick="window.history.back(-1);" class="weui_btn weui_btn_warn">返回</button>
            <?php }?>
        </p>
    </div>
</div>
<script>
    function onBridgeReady(){
        WeixinJSBridge.call('hideOptionMenu');
    }

    if (typeof WeixinJSBridge == "undefined"){
        if( document.addEventListener ){
            document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
        }else if (document.attachEvent){
            document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
            document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
        }
    }else{
        onBridgeReady();
    }
</script>
</body>
</html>