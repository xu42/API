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
$current_week = $mydlpu_handle->getCurrentWeek();
$wechat_id = $_GET['wechat'];

$userinfo = $mydlpu_handle->getSimpleUserinfoByWechat($wechat_id);
$username = $userinfo->_id;

$json = $mydlpu_handle->getCurriculumWeeks($username, $semester, '1', $wechat_id);
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
    <link rel="stylesheet" href="../assets/css/amazeui.min.css"/>
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


<h2>周一</h2>
<table class="am-table am-table-bordered am-table-compact am-text-truncate">
    <?php
    for($i=0; $i<6;$i++)
    {
        if(@$curriculum_weeks['data'][$i][0])
        {
            echo "<tr><td>".a($i)."</td>";
            echo "<td>".$curriculum_weeks['data'][$i][0][0]."</td>";
            echo "<td>".$curriculum_weeks['data'][$i][0][2]."</td></tr>";
        }
    }
    ?>
</table>

<h2>周二</h2>
<table class="am-table am-table-bordered am-table-compact am-text-truncate">
    <?php
    for($i=0; $i<6;$i++)
    {
        if(@$curriculum_weeks['data'][$i][1])
        {
            echo "<tr><td>".a($i)."</td>";
            echo "<td>".$curriculum_weeks['data'][$i][1][0]."</td>";
            echo "<td>".$curriculum_weeks['data'][$i][1][2]."</td></tr>";
        }
    }
    ?>
</table>

<h2>周三</h2>
<table class="am-table am-table-bordered am-table-compact am-text-truncate">
    <?php
    for($i=0; $i<6;$i++)
    {
        if(@$curriculum_weeks['data'][$i][2])
        {
            echo "<tr><td>".a($i)."</td>";
            echo "<td>".$curriculum_weeks['data'][$i][2][0]."</td>";
            echo "<td>".$curriculum_weeks['data'][$i][2][2]."</td></tr>";
        }
    }
    ?>
</table>

<h2>周四</h2>
<table class="am-table am-table-bordered am-table-compact am-text-truncate">
    <?php
    for($i=0; $i<6;$i++)
    {
        if(@$curriculum_weeks['data'][$i][3])
        {
            echo "<tr><td>".a($i)."</td>";
            echo "<td>".$curriculum_weeks['data'][$i][3][0]."</td>";
            echo "<td>".$curriculum_weeks['data'][$i][3][2]."</td></tr>";
        }
    }
    ?>
</table>


<h2>周五</h2>
<table class="am-table am-table-bordered am-table-compact am-text-truncate">
    <?php
    for($i=0; $i<6;$i++)
    {
        if(@$curriculum_weeks['data'][$i][4])
        {
            echo "<tr><td>".a($i)."</td>";
            echo "<td>".$curriculum_weeks['data'][$i][4][0]."</td>";
            echo "<td>".$curriculum_weeks['data'][$i][4][2]."</td></tr>";
        }
    }
    ?>
</table>

<h2>周六</h2>
<table class="am-table am-table-bordered am-table-compact am-text-truncate">
    <?php
    for($i=0; $i<6;$i++)
    {
        if(@$curriculum_weeks['data'][$i][5])
        {
            echo "<tr><td>".a($i)."</td>";
            echo "<td>".$curriculum_weeks['data'][$i][5][0]."</td>";
            echo "<td>".$curriculum_weeks['data'][$i][5][2]."</td></tr>";
        }
    }
    ?>
</table>

<h2>周日</h2>
<table class="am-table am-table-bordered am-table-compact am-text-truncate">
    <?php
    for($i=0; $i<6;$i++)
    {
        if(@$curriculum_weeks['data'][$i][6])
        {
            echo "<tr><td>".a($i)."</td>";
            echo "<td>".$curriculum_weeks['data'][$i][6][0]."</td>";
            echo "<td>".$curriculum_weeks['data'][$i][6][2]."</td></tr>";
        }
    }
    ?>
</table>

</body>
</html>