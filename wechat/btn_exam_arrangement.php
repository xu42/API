<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2016/1/22
 * Time: 15:41
 */
require_once '../v1/dlpu/mydlpu_handle.php';

$mydlpu_handle = new mydlpu_handle();

$semester ='2015-2016-1';
$wechat_id = $_GET['wechat'];

$userinfo = $mydlpu_handle->getSimpleUserinfoByWechat($wechat_id);
$username = $userinfo->_id;

$json = $mydlpu_handle->getExamArrangement($username, $semester, $wechat_id);
$exam_arrangement = json_decode($json, 1);

//var_dump($exam_arrangement['data']['1']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>期末考试安排</title>
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <link rel="stylesheet" href="../assets/css/amazeui.min.css"/>
</head>
<body>

<table class="am-table am-table-bordered am-table-compact">
    <thead>
        <tr style="text-align: center">
            <th>课程名称</th>
            <th>考试时间</th>
            <th>考场</th>
        </tr>
    </thead>
    <tbody>
        <?php
        for($i=1; $i<count($exam_arrangement['data']);$i++)
        {
            if(@$exam_arrangement['data'][$i])
            {
                echo "<tr><td>".$exam_arrangement['data'][$i]['3']."</td>";
                echo "<td>".$exam_arrangement['data'][$i]['4']."</td>";
                echo "<td>".$exam_arrangement['data'][$i]['5']."</td></tr>";
            }
        }
        ?>
    </tbody>
</table>

</body>
</html>

