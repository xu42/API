<?php

error_reporting(0);
require_once 'mongodb.php';

class yinle{

    public function index($phone)
    {
        if(!$this->checkPhone($phone)) return '手机号码格式错误';
        $user_id = $this->checkPhoneIsExist($phone);
        if(!$user_id) return '该手机号尚未注册印乐' . "\n" . '<a href="http://www.yinle.cc/register.html">点此免费注册印乐</a>';
        // 将手机号和用户id插入数据库
        $db = new mongodb('yinle', 'user');
        $res = $db->update(['_id' => $phone], ['_id' => $phone, 'user_id' => $user_id, 'filter' => 1], ['multi' => false, 'upsert' => true]);
        return "恭喜您, ".$phone. "\n已成功加入自动签到系统";
    }


    /**
     * 检查手机格式
     * @param $phone
     * @return bool
     */
    private function checkPhone ($phone)
    {
        if(strlen($phone) != 11) return FALSE;
        return TRUE;
    }

    /**
     * 检查该手机号是否是印乐会员
     * @param $phone
     */
    private function checkPhoneIsExist ($phone)
    {
        $url = 'http://www.yinle.cc/Ajax/ajaxuser.aspx?oper=havephonesj&phone='.$phone;
        $res_data = $this->myCurl($url);
        $response = json_decode($res_data, true);
        if($response['ret'] == 2) return FALSE;
        return $response['data']['user_id'];
    }

    /**
     * 简易封装get请求
     * @param $url
     * @return mixed
     */
    private function myCurl ($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $res_data = curl_exec($ch);
        return $res_data;
    }

    /**
     * 获取用户id列表
     * @return array
     */
    private function getUseridList ()
    {
        $db = new mongodb('yinle', 'user');
        $user_list = $db->find(['filter' => 1]);
        $user_list = $user_list->toArray();
        return $user_list;
    }

    /**
     * 签到
     * @return array
     */
    public function yinleSign ()
    {
        $list = $this->getUseridList();
        $log_array[] = null;
        for($i=0;$i<count($list);$i++)
        {
            $url = 'http://www.yinle.cc/Ajax/ajaxuser.aspx?oper=usersign&userid=' . $list[$i]->user_id;
            $res_data = $this->myCurl($url);
            $response = json_decode($res_data, true);
            // 记录签到日志
            $db = new mongodb('yinle', 'usersign');
            $log = ['phone' => $list[$i]->_id, 'user_id' => $list[$i]->user_id, 'result' => $response['data'], 'time' => microtime() ];
            $db->insert($log);
            $log_array[] = $log;
            usleep(20000);
        }
        return $log_array;
    }
}