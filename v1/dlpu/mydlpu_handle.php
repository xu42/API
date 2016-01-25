<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2016/1/17
 * Time: 14:53
 */

define('database_dlpu_userinfo_name', 'dlpu_userinfo');
define('collection_password_name', 'password');
define('SEMESTER', '2015-2016-2');

class mydlpu_handle {

    /**
     * 绑定微信与教务系统的用户名和密码
     * @param $authorization
     * @param $username
     * @param $password
     * @param $wechat_id
     * @return mixed
     */
    public function bindingWechatWithUsername ($username, $password, $wechat_id)
    {
        $url = 'https://api.xu42.cn/v1/dlpu/save';
        $headers = ['Authorization:' . $this->getToken()];
        $postdata = 'username=' . $username . '&password=' . $password . '&wechat=' . $wechat_id;
        return $this->myCurl($url, $headers, $postdata);
    }

    /**
     * 获取简单的用户信息 包括学号、密码
     * @param $wechat_id
     * @return object for存在该条信息（即 微信已与教务系统绑定）, boolean FALSE for不存在
     */
    public function getSimpleUserinfoByWechat ($wechat_id)
    {
        require_once 'student_database_tools.php';
        $db = new student_database_tools(database_dlpu_userinfo_name, collection_password_name);
        return $db->getPasswordFromDatabaseByWechatId($wechat_id);
    }

    /**
     * 根据微信openid获取用户名（学号）
     * @param $wechat_id
     * @return mixed
     */
    public function getUsernameByWechat ($wechat_id)
    {
        $userinfo = $this->getSimpleUserinfoByWechat($wechat_id);
        return $userinfo->_id;
    }

    /**
     * 一个GET & POST 请求方式的PHP CURL HTTPS封装函数
     * @param $url
     * @param $headers
     * @return mixed
     */
    private function myCurl ($url, $headers, $postdata = FALSE)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        if($postdata){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        }
        return curl_exec($ch);
    }

    /**
     * 获取api认证所需的token
     * @return array|string1
     */
    private function getToken ()
    {
        $memcache = new Memcached;
        $memcache->addServer('localhost', 11211);
        $token = $memcache->get('api_token');
        if(!$token){
            $url = 'https://api.xu42.cn/v1/token';
            $headers = ['Authorization: Basic dXNlcjA6dXNlcjBwYXNzd29yZA==', 'key: tmp'];
            $res = json_decode($this->myCurl($url, $headers), 1);
            $token = 'Bearer ' . $res['access_token'];
            $memcache->set('api_token', $token, time() + 3600);
        }
        return $token;
    }

    /**
     * 向api请求本周课程表信息
     * @param $username             登陆账号, 这里为学号
     * @param $semester             学期, eg. 2015-2016-1
     * @param $weeks                周次, eg. 12
     * @param $authorization        token
     * @param $wechat_id            微信 openid
     * @return mixed                JSON 格式的请求结果
     */
    public function getCurriculumWeeks ($username, $semester, $weeks, $wechat_id)
    {
        $url = 'https://api.xu42.cn/v1/dlpu/curriculum_theory/'.$username.'/'.$semester.'/'.$weeks;
        $headers = ['Authorization:' . $this->getToken(), 'wechat:' . $wechat_id];
        return $this->myCurl($url, $headers);
    }

    /**
     * 向api请求本学期课程表信息
     * @param $username
     * @param $semester
     * @param $authorization
     * @param $wechat_id
     * @return mixed
     */
    public function getCurriculumSemester ($username, $semester, $wechat_id)
    {
        $url = 'https://api.xu42.cn/v1/dlpu/curriculum_theory/'.$username.'/'.$semester.'/';
        $headers = ['Authorization:' . $this->getToken(), 'wechat:' . $wechat_id];
        return $this->myCurl($url, $headers);
    }

    /**
     * 获取学校当前周次信息
     * @return mixed
     */
    public function getCurrentWeek ()
    {
        $memcache = new Memcached;
        $memcache->addServer('localhost', 11211);
        $currentweek = $memcache->get('currentweek');
        if(!$currentweek){
            $url = 'https://api.xu42.cn/v1/dlpu/current_week';
            $headers = ['Authorization:' . $this->getToken()];
            $json = $this->myCurl($url, $headers);
            $currentweek = json_decode($json)->data;
            $memcache->set('currentweek', $currentweek);
        }
        return $currentweek;
    }

    /**
     * 获取学生成绩信息
     * @param $username
     * @param $semester
     * @param $wechat_id
     * @return mixed
     */
    public function getScore ($username, $wechat_id, $semester = '')
    {
        $url = 'https://api.xu42.cn/v1/dlpu/usergrade/'.$username.'/'.$semester;
        $headers = ['Authorization:' . $this->getToken(), 'wechat:' . $wechat_id];
        return $this->myCurl($url, $headers);
    }

    /**
     * 获取考试安排信息
     * @param $username
     * @param $semester
     * @param $wechat_id
     * @param string $category  考试安排分类信息 考试类别，1 => 期初, 2 => 期中, 3 => 期末
     * @return mixed
     */
    public function getExamArrangement ($username, $semester, $wechat_id, $category = '3')
    {
        $url = 'https://api.xu42.cn/v1/dlpu/exam_arrangement/'.$username.'/'.$semester.'/'.$category;
        $headers = ['Authorization:' . $this->getToken(), 'wechat:' . $wechat_id];
        return $this->myCurl($url, $headers);
    }

    public function translationToHans ($number)
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

    function getCurrentDay ()
    {
        $weekarray = [6, 0, 1, 2, 3, 4, 5];
        return $weekarray[date('w')];
    }
}