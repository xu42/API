<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2015/12/21
 * Time: 14:46
 */

/**
 * Class student_information
 * 获取学生的个人信息
 */
class student_information {

    /**
     * @var $url string 系统登录页面地址
     * @var $cookie string 已成功登陆的用户的cookie信息
     */
    public $url = 'http://210.30.62.8:8080/jsxsd/';
    public $cookie = '';

    public function __construct($cookie)
    {
        $this->cookie = $cookie;
    }


    /**
     * 获取用户信息
     * 目前可获取 姓名/学号
     * @param $cookie 向服务器请求的Cookie
     * @return array 用户信息
     */
    public function getInfo()
    {
        $url = $this->url . 'framework/main.jsp';
        $res_data = $this->myCurl($url, $this->cookie);
        $user_msg = $this->reUserMsg($res_data);
        return $user_msg;
    }


    /**
     * 一个简单的封装CURL网络请求的函数
     * @param $url 请求地址
     * @param $cookie 发送的Cookie
     * @param $post_data 发送的数据
     * @return mixed 服务器响应 网页源代码
     */
    private function myCurl($url, $cookie, $post_data = '')
    {
        $headers = array('Content-Length:'.strlen($post_data), 'Referer:'.$this->url, 'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
        $res_data = curl_exec($ch);
        return $res_data;
    }


    /**
     * @param $res_data 网页源代码
     * @return array 用户信息
     */
    private function reUserMsg($res_data)
    {
        preg_match('/姓名：(.*?)\</', $res_data, $name);
        preg_match('/学号：(.*?)\</', $res_data, $sno);
        $user_msg[] = $name[1];
        $user_msg[] = $sno[1];
        return $user_msg;
    }

}