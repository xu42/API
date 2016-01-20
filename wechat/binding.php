<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <link rel="stylesheet" href="../assets/css/weui.min.css"/>
</head>
<body>
<form action="binding_deal.php" method="post">
<div class="weui_cells_title">绑定教务系统</div>
    <div class="weui_cells weui_cells_form">
        <div class="weui_cell">
            <div class="weui_cell_hd"><label class="weui_label">学号</label></div>
            <div class="weui_cell_bd weui_cell_primary">
                <input class="weui_input" type="number" name="username" placeholder="请输入学号" required />
            </div>
        </div>
        <div class="weui_cell">
            <div class="weui_cell_hd"><label class="weui_label">密码</label></div>
            <div class="weui_cell_bd weui_cell_primary">
                <input class="weui_input" type="password" name="password" placeholder="请输入密码" required />
            </div>
        </div>
        <input type="hidden" value="<?=$_GET['wechat']?>" name="wechat" required />
        <input type="hidden" value="<?=$_GET['btn']?>" name="btn" />
        <button class="weui_btn weui_btn_primary" type="submit">提交</button>
    </div>
</form>

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