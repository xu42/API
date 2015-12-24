<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2015/12/24
 * Time: 19:11
 */
require_once 'student_crawl_tools.php';

/**
 * Class student_change_password
 * 学生修改密码
 */
class student_change_password extends student_crawl_tools {

    /**
     * student_change_password constructor.
     */
    public function __construct($cookie)
    {
        parent::__construct($cookie);
    }

    /**
     * 修改密码
     * @param $old_passwd       旧密码
     * @param $new_passwd       新密码
     */
    public function set ($old_passwd, $new_passwd)
    {
        $this->url = $this->url_login . $this->url_student_change_password;
        $postdata = 'oldpassword=' . $old_passwd . '&password1=' . $new_passwd . '&password2=' .$new_passwd .'&upt=1';
        $res_data = $this->myCurl($this->url, $this->cookie, $postdata);
        return $res_data;
    }

}