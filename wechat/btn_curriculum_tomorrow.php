<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2016/1/20
 * Time: 9:20
 */
require_once '../v1/dlpu/mydlpu_handle.php';

$mydlpu_handle = new mydlpu_handle();

$semester ='2015-2016-2';
$current_week = $mydlpu_handle->getCurrentWeek();
$wechat_id = $_GET['wechat'];

$userinfo = $mydlpu_handle->getSimpleUserinfoByWechat($wechat_id);
$username = $userinfo->_id;

$json = $mydlpu_handle->getCurriculumWeeks($username, $semester, '1', $wechat_id);
$curriculum_weeks = json_decode($json, 1);

function getTomorrowDay ()
{
    $weekarray = [0, 1, 2, 3, 4, 5, 6];
    return $weekarray[date('w')];
}

function a ($number)
{
    switch ($number)
    {
        case '0':
            $res = '1-2节';
            break;

        case '1':
            $res = '3-4节';
            break;

        case '2':
            $res = '5-6节';
            break;

        case '3':
            $res = '7-8节';
            break;

        case '4':
            $res = '9-10节';
            break;

        case '5':
            $res = '11-12节';
            break;
        default:
            $res = NULL;
            break;
    }
    return $res;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <link rel="stylesheet" href="../assets/css/weui.min.css"/>
</head>
<body>

<article class="weui_article">
    <h1>明日课表</h1>
    <section>
        <?php
        for($i=0; $i<6;$i++)
        {
            if(@$curriculum_weeks['data'][$i][getTomorrowDay()])
            {
                echo "<b>".a($i).":</b> " . $curriculum_weeks['data'][$i][getTomorrowDay()][0] . ' | '  . $curriculum_weeks['data'][$i][getTomorrowDay()][2] . "<br/>";
            }
        }
        ?>
    </section>
</article>

</body>
</html>
