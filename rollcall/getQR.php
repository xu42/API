<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>大连工业大学二维码签到系统</title>
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <link rel="stylesheet" href="../assets/css/amazeui.min.css"/>
</head>
<body>
<h1 class="am-text-center">获取二维码</h1>
<hr/>

<form class="am-form am-form-horizontal">
    <div class="am-form-group">
        <label for="teacher_job_number" class="am-u-sm-2 am-form-label">工号</label>
        <div class="am-u-sm-10">
            <input type="number" id="teacher_job_number" placeholder="请输入工号" required>
        </div>
    </div>
    <div class="am-form-group">
        <label for="teacher_password" class="am-u-sm-2 am-form-label">密码</label>
        <div class="am-u-sm-10">
            <input type="password" id="teacher_password" placeholder="请输入密码" required>
        </div>
    </div>
    <div class="am-form-group">
        <label for="teacher_current_room_number" class="am-u-sm-2 am-form-label">教室</label>
        <div class="am-u-sm-10">
            <input type="text" id="teacher_current_room_number" placeholder="当前教室编号" required>
        </div>
    </div>
    <div class="am-form-group">
        <label for="teacher_current_session" class="am-u-sm-2 am-form-label">当前节次</label>
        <div class="am-u-sm-10">
            <select id="teacher_current_session">
                <option value="1">第1-2节</option>
                <option value="2">第3-4节</option>
                <option value="3">第5-6节</option>
                <option value="4">第7-8节</option>
                <option value="5">第9-10节</option>
                <option value="6">第11-12节</option>
            </select>
        </div>
    </div>

    <button id="btn_get" type="button" class="am-btn am-btn-primary am-btn-xl am-center">提交</button>
</form>

<div id="qrcode" align="center"></div>

<script src="https://upcdn.b0.upaiyun.com/libs/jquery/jquery-2.0.3.min.js"></script>
<script src="../assets/js/jquery.qrcode.min.js"></script>
<script>
    $(document).ready(function(){
        $("#btn_get").click(function(){

            $("#qrcode").html("<h2 class='am-text-center'>正在加载中...</h2>");

            var teacher_job_number_val          = $("#teacher_job_number").val();
            var teacher_password_val            = $("#teacher_password").val();
            var teacher_current_room_number_val = $("#teacher_current_room_number").val();
            var teacher_current_session_val     = $("#teacher_current_session").val();

            var req = $.ajax({
                url:"https://api.xu42.cn/v1/dlpu/rollcall_getqr",
                method:"POST",
                data:{teacher_job_number: teacher_job_number_val, teacher_password: teacher_password_val, teacher_current_room_number: teacher_current_room_number_val, teacher_current_session: teacher_current_session_val}
            });

            req.done(function(res){
                $("form").hide();

                if(res.messages == 'error'){
                    $("#qrcode").html("<h2 class='am-text-center'>" + res.data + "</h2>");
                }

                if(res.messages == 'OK'){
                    $("#qrcode").html("");
                    $("#qrcode").qrcode({
                        render: "table", //table方式
                        width: 400, //宽度
                        height: 400, //高度
                        text: res.data //任意内容
                    });
                }
            });

            req.fail(function(res){
               $("#qrcode").html("<h2 class='am-text-center'>请求失败，请稍后重试</h2>");
            });

        });
    });
</script>
</body>
</html>