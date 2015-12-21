<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2015/12/21
 * Time: 15:50
 */

/**
 * Class student_grade
 * 获取学生的成绩信息
 */
class student_grade {

    /**
     * @var $url string 系统登录页面地址
     * @var $cookie string 已成功登陆的用户的cookie信息
     * @var $courseGrade array 查询到的成绩信息
     */
    public $url = 'http://210.30.62.8:8080/jsxsd/';
    public $cookie = '';
    public $courseGrade;

    public function __construct($cookie)
    {
        $this->cookie = $cookie;
    }

    /**
     * @param string $kksj 开课时间即查询某学期的成绩 默认为空查询所有学期 查询格式为 2014-2015-2(2014-2015学年第二学期)
     * @param string $kcxz 课程性质 默认为空查询所有
     * @param string $kcmc 课程名称 默认为空查询所有 指定某一课程名称进行查询
     * @param string $xsfs 查询结果显示方式，已知有 all(显示全部成绩)和 max(显示最好成绩)，并没有区别
     * @return mixed FALSE for failed OR array for Course Grade(课程成绩)
     */
    public function getGrade($kksj='', $kcxz='', $kcmc='', $xsfs='all')
    {
        $this->courseGrade[0][0] = empty($kksj)?'全部':$kksj;

        $url = $this->url . 'kscj/cjcx_list';
        $post_data = "xsfs=$xsfs&kksj=$kksj&kcxz=$kcxz&kcmc=$kcmc";
        $res_data = $this->myCurl($url, $this->cookie, $post_data);

        $courseGrade = $this->reCourseGrade($res_data);
        return $courseGrade;
    }

    /**
     * 一个简单的封装CURL网络请求的函数
     * @param $url string 请求地址
     * @param $cookie string 已成功登陆的用户的cookie信息
     * @param $post_data 发送的数据
     * @return mixed 服务器response 网页源代码
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
     * 正则解析成绩信息的网页 获得课程成绩信息
     * @param $res_data 成绩信息的网页源码
     * @return mixd FALSE for failed OR array for Course Grade(课程成绩)
     */
    private function reCourseGrade($res_data)
    {
        preg_match_all('/<span>(\d+?)(<|。<)/', $res_data, $credit);
        if(count($credit[0]) == 0) return FALSE; // 未获取到成绩列表(通过判断是否获取到绩点信息)
        for($i=0;$i<count($credit[1]);$i++){
            $this->courseGrade[0][$i+1] = $credit[1][$i];
        }

        preg_match_all('/<tr>\s(.*?)<\/tr>/s', $res_data, $t1_grade_list);
        for($i=0;$i<count($t1_grade_list[1]);$i++)
        {
            preg_match_all('/>(.{1,66}?)</', $t1_grade_list[1][$i], $t2_grade_list);
            $this->courseGrade[1][$i] = $t2_grade_list[1];
        }

        return $this->courseGrade;
    }
}