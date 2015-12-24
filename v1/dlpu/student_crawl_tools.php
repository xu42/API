<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2015/12/23
 * Time: 23:42
 */
class student_crawl_tools {

    /**
     * @var  $url_host                              string      教务处学生系统的HOST 加端口号(可以是域名)
     * @var  $url_login                             string      系统登录页面地址
     * @var  $url                                   string      myCurl 形参, 有继承者继承此变量, 请求资源的网络地址
     * @var  $url_student_grade                     string      相对url_host的地址   考试成绩查询
     * @var  $url_student_information               string      相对url_host的地址   学生信息(学籍卡片)
     * @var  $url_student_announcement              string      相对url_host的地址   学生已收公告信息
     * @var  $url_student_curriculum_theory         string      相对url_host的地址   学期理论课表
     * @var  $url_student_curriculum_experiment     string      相对url_host的地址   实验课表
     * @var  $url_student_curriculum_class          string      相对url_host的地址   班级课表
     * @var  $url_student_curriculum_teather        string      相对url_host的地址   教师课表
     * @var  $url_student_curriculum_classroom      string      相对url_host的地址   教室课表
     * @var  $url_student_curriculum_course         string      相对url_host的地址   课程课表
     * @var  $url_student_exam_arrangement          string      相对url_host的地址   考试安排
     * @var  $url_student_change_password           string      相对url_host的地址   修改密码
     * @var  $url_student_reset_password            string      相对url_host的地址   重置密码
     * @var $cookie                                 string      myCurl 形参, 有继承者继承此变量, 已成功登陆的用户的cookie信息
     * @var $postdata                               string      myCurl 形参, 有继承者继承此变量, 以 POST 方式请求资源的需要POST的数据
     */
    protected $url_host                             = 'http://210.30.62.8:8080';
    protected $url_login                            = 'http://210.30.62.8:8080/jsxsd/';
    protected $url                                  = '';
    protected $url_student_grade                    = 'kscj/cjcx_list';
    protected $url_student_information              = 'grxx/xsxx';
    protected $url_student_announcement             = 'ggly/ysgg_query';
    protected $url_student_curriculum_theory        = 'xskb/xskb_list.do';
    protected $url_student_curriculum_experiment    = 'syjx/toXskb.do';
    protected $url_student_curriculum_class         = 'kbcx/kbxx_xzb';
    protected $url_student_curriculum_teather       = 'kbcx/kbxx_teacher';
    protected $url_student_curriculum_classroom     = 'kbcx/kbxx_classroom';
    protected $url_student_curriculum_course        = 'kbcx/kbxx_kc';
    protected $url_student_exam_arrangement         = 'xsks/xsksap_list';
    protected $url_student_change_password          = 'grsz/grsz_xgmm';
    protected $url_student_reset_password           = 'system/resetPasswd';
    protected $cookie                               = '';
    protected $postdata                             = '';

    /**
     * student_learning_record_card constructor.
     */
    public function __construct($cookie)
    {
        $this->cookie = $cookie;
    }

    /**
     * 获取数据 由继承者重写
     * @return mixed 格式化后的数据
     */
    public function get()
    {
        $res_data = $this->myCurl($this->url, $this->cookie);
        $data = $this->re($res_data);
        return $data;
    }

    /**
     * 一个简单的封装CURL网络请求的函数
     * @param $url 请求地址
     * @param $cookie 发送的Cookie
     * @return mixed 服务器响应 网页源代码
     */
    protected function myCurl($url, $cookie, $postdata = '')
    {
        $headers = array('Content-Length:'.strlen($postdata), 'Referer:'.$this->url_login, 'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
        $res_data = curl_exec($ch);
        return $res_data;
    }

    /**
     * 正则解析网页 由继承者进行重写
     * @param $res_data
     * @return mixed
     */
    protected function re ($res_data) {
        return $res_data;
    }

}