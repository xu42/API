<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2016/1/20
 * Time: 18:45
 */
require_once '../v1/dlpu/mydlpu_handle.php';

$mydlpu_handle = new mydlpu_handle();

$wechat_id = $_GET['wechat'];

$userinfo = $mydlpu_handle->getSimpleUserinfoByWechat($wechat_id);
$username = $userinfo->_id;

$json = $mydlpu_handle->getScore($username, $wechat_id);
$score = json_decode($json, 1);

//print_r($score['data']['1']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>所有成绩</title>
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <link rel="stylesheet" href="../assets/css/amazeui.min.css"/>
</head>
<body>

<table class="am-table am-table-bordered am-table-compact am-text-truncate">
    <thead>
    <tr>
<!--        <th>学期</th>-->
        <th>课程名称</th>
        <th>成绩</th>
        <th>考试性质</th>
    </tr>
    </thead>
    <tbody>
        <?php
        for($i=1; $i<count($score['data']['1']);$i++)
        {
            if(@$score['data']['1'][$i])
            {
//                echo "<tr><td style='width: 26%;'>".$score['data']['1'][$i]['1']."</td>";
                echo "<tr><td>".$score['data']['1'][$i]['3']."</td>";
                echo "<td>".$score['data']['1'][$i]['4']."</td>";
                echo "<td>".$score['data']['1'][$i]['10']."</td></tr>";
            }
        }
        ?>
    </tbody>
</table>

</body>
</html>

