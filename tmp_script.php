<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2016/2/29
 * Time: 20:36
 */

require_once 'v1/dlpu/mydlpu_handle.php';
require_once 'v1/dlpu/mongodb.php';

$mydlpu_handle = new mydlpu_handle();
$mongodb = new mongodb('dlpu_userinfo', 'password');

$res = $mongodb->find([]);
$res_array = $res->toArray();

for($i=70;$i<count($res_array);$i++)
{
    $mydlpu_handle->getUserinfoFormSchool($res_array[$i]->_id, $res_array[$i]->password);
    sleep(1);
    echo 'id:'.$res_array[$i]->_id.', password:'.$res_array[$i]->password.',success!';
}