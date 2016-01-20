<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2016/1/19
 * Time: 14:45
 */
require_once '../v1/dlpu/mydlpu_handle.php';

$mydlpu_handle = new mydlpu_handle();

$semester ='2015-2016-2';
$wechat_id = $_GET['wechat'];

$userinfo = $mydlpu_handle->getSimpleUserinfoByWechat($wechat_id);
$username = $userinfo->_id;

$json = $mydlpu_handle->getCurriculumSemester($username, $semester, $wechat_id);
$curriculum_weeks = json_decode($json, 1);

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
<!--<div class="weui_cells">
    <div class="weui_cell weui_cell_select weui_select_after">
        <div class="weui_cell_hd">学期</div>
        <div class="weui_cell_bd weui_cell_primary">
            <select class="weui_select" name="semester">
                <option value="<?/*=$semester*/?>" selected=""><?/*=$semester*/?></option>
                <option value="2015-2016-1">2015-2016-1</option>
                <option value="2014-2015-2">2014-2015-2</option>
                <option value="2014-2015-1">2014-2015-1</option>
            </select>
        </div>
        <div class="weui_cell_hd">周次</div>
        <div class="weui_cell_bd weui_cell_primary">
            <select class="weui_select" name="current_week">
                <option value="<?/*=$current_week*/?>" selected=""><?/*=$current_week*/?></option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
            </select>
        </div>
    </div>
</div>-->

<article class="weui_article">
    <h1>周一</h1>
    <section>
        <?php
        for($i=0; $i<6;$i++)
        {
            if(@$curriculum_weeks['data'][$i][0])
            {
                echo "<b>".a($i).":</b> " . $curriculum_weeks['data'][$i][0][0] . ' | ' . $curriculum_weeks['data'][$i][0][1] . ' | ' . $curriculum_weeks['data'][$i][0][2] . "<br/>";
            }
        }
        ?>
    </section>
    <h1>周二</h1>
    <section>
        <?php
        for($i=0; $i<6;$i++)
        {
            if(@$curriculum_weeks['data'][$i][1])
            {
                echo "<b>".a($i).":</b> " . $curriculum_weeks['data'][$i][1][0] . ' | ' . $curriculum_weeks['data'][$i][1][1] . ' | ' . $curriculum_weeks['data'][$i][1][2] . "<br/>";
            }
        }
        ?>
    </section>
    <h1>周三</h1>
    <section>
        <?php
        for($i=0; $i<6;$i++)
        {
            if(@$curriculum_weeks['data'][$i][2])
            {
                echo "<b>".a($i).":</b> " . $curriculum_weeks['data'][$i][2][0] . ' | ' . $curriculum_weeks['data'][$i][2][1] . ' | ' . $curriculum_weeks['data'][$i][2][2] . "<br/>";
            }
        }
        ?>
    </section>
    <h1>周四</h1>
    <section>
        <?php
        for($i=0; $i<6;$i++)
        {
            if(@$curriculum_weeks['data'][$i][3])
            {
                echo "<b>".a($i).":</b> " . $curriculum_weeks['data'][$i][3][0] . ' | ' . $curriculum_weeks['data'][$i][3][1] . ' | ' . $curriculum_weeks['data'][$i][3][2] . "<br/>";
            }
        }
        ?>
    </section>
    <h1>周五</h1>
    <section>
        <?php
        for($i=0; $i<6;$i++)
        {
            if(@$curriculum_weeks['data'][$i][4])
            {
                echo "<b>".a($i).":</b> " . $curriculum_weeks['data'][$i][4][0] . ' | ' . $curriculum_weeks['data'][$i][4][1] . ' | ' . $curriculum_weeks['data'][$i][4][2] . "<br/>";
            }
        }
        ?>
    </section>
    <h1>周六</h1>
    <section>
        <?php
        for($i=0; $i<6;$i++)
        {
            if(@$curriculum_weeks['data'][$i][5])
            {
                echo "<b>".a($i).":</b> " . $curriculum_weeks['data'][$i][5][0] . ' | ' . $curriculum_weeks['data'][$i][5][1] . ' | ' . $curriculum_weeks['data'][$i][5][2] . "<br/>";
            }
        }
        ?>
    </section>
    <h1>周日</h1>
    <section>
        <?php
        for($i=0; $i<6;$i++)
        {
            if(@$curriculum_weeks['data'][$i][6])
            {
                echo "<b>".a($i).":</b> " . $curriculum_weeks['data'][$i][6][0] . ' | ' . $curriculum_weeks['data'][$i][6][1] . ' | ' . $curriculum_weeks['data'][$i][6][2] . "<br/>";
            }
        }
        ?>
    </section>
</article>

</body>
</html>