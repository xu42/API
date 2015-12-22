<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2015/12/22
 * Time: 14:22
 */

class student_information {

    /**
     * @var  $url_host string       教务处学生系统的HOST 加端口号(可以是域名)
     * @var  $url_login string      系统登录页面地址
     * @var $cookie string          已成功登陆的用户的cookie信息
     */
    public $url_host = 'http://210.30.62.8:8080';
    public $url_login = 'http://210.30.62.8:8080/jsxsd/';
    public $cookie = '';

    /**
     * student_learning_record_card constructor.
     */
    public function __construct($cookie)
    {
        $this->cookie = $cookie;
    }


    /**
     * 获取详细的用户信息
     * @return array 用户信息
     */
    public function getInfo()
    {
        $url = $this->url_login . 'grxx/xsxx';
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
    protected function myCurl($url, $cookie, $post_data = '')
    {
        $headers = array('Content-Length:'.strlen($post_data), 'Referer:'.$this->url_login, 'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36');
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
    public function reUserMsg($res_data)
    {
        preg_match_all('/>(.+?)<\/td>/', $res_data, $match_data_table);
        preg_match_all('/img alt="照片" src="(.+?)"/', $res_data, $match_data_photo);

        foreach($match_data_table[1] as $value){ //去除 数组元素中的 &nbsp;
            $match_data_table_trim[] = str_replace('&nbsp;', '', $value);
        }
        $match_data_table_trim[0] = $this->url_host . $match_data_photo[1][0];
        unset($match_data_table_trim[188]);
        return $match_data_table_trim;
    }

}
