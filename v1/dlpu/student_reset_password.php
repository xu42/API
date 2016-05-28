<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2015/12/24
 * Time: 20:10
 */
require_once 'student_crawl_tools.php';

/**
 * Class student_reset_password
 * 重置密码
 */
class student_reset_password extends student_crawl_tools {

    /**
     * 重置密码
     * @param $username     学号
     * @param $id_card      身份证号
     * @return bool         重置结果 True for success, False for failed
     */
    public function set ($username, $id_card)
    {
        $this->url = $this->url_login . $this->url_student_reset_password;
        $postdata = 'account='.$username.'&sfzjh='.$id_card;
        $res_data = $this->myCurl($this->url, '', $postdata);
        $reset_res = $this->re($res_data);
        return $this->isResetSuccess($reset_res);
    }

    /**
     * @param $res_data 网页源码
     * @return mixed    网页提示信息
     */
    protected function re ($res_data)
    {
        preg_match_all('/alert\(\'(.*?)\'\);/', $res_data, $data);
        return $data[1][0];
    }

    /**
     * 检测重置密码是否成功
     * "密码已重置为身份证号的后六位" 该字符串在UTF8编码下占42字节
     * @param $reset_res 重置结果(经过正则之后)
     * @return bool True for success, False for failed
     */
    private function isResetSuccess ($reset_res)
    {
        if(strlen($reset_res) == 42) return TRUE;
        return FALSE;
    }

}