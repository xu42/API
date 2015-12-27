<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2015/12/22
 * Time: 16:56
 */

require_once 'student_login.php';
require_once 'student_database_tools.php';

class slim_handle {

    public $request ='';
    public $response ='';
    public $arguments ='';
    private $database_dlpu_userinfo_name = 'dlpu_userinfo';
    private $collection_userinfo_prefix_name = 'userinfo_20';
    private $database_dlpu_grade_name = 'dlpu_grade';
    private $collection_grade_prefix_name = 'grade_20';

    /**
     * @param $request          Slim ServerRequestInterface request
     * @param $response         Slim ResponseInterface response
     * @param $arguments        Slim arguments
     */
    public function __construct ($request, $response, $arguments)
    {
        $this->request      = $request;
        $this->response     = $response;
        $this->arguments    = $arguments;
    }


    /**
     * 当检测到Request Headers 没有密码时调用此方法
     * @return mixed 400 error
     */
    protected function noPassword ()
    {
        $this->response->getBody()->write(json_encode(['error' => 'no student_id password']));
        $response = $this->response->withStatus(400);
        $response = $response->withHeader('Content-type', 'application/json');
        return $response;
    }

    /**
     * 当检测到密码错误时调用此方法
     * @return mixed 400 error
     */
    protected function wrongPassword ()
    {
        $this->response->getBody()->write(json_encode(['error' => 'wrong student_id or password']));
        $response = $this->response->withStatus(400);
        $response = $response->withHeader('Content-type', 'application/json');
        return $response;
    }

    /**
     * 检测是否登录成功
     * @return bool True for Success, False for failed
     */
    protected function isLoginSuccess ()
    {
        $student_login = new student_login($this->arguments['username'], $this->request->getHeaderLine('password'));
        return $student_login->isSuccess();
    }

    /**
     * 获取 Cookie
     * @return string
     */
    protected function getCookie ()
    {
        $student_login = new student_login($this->arguments['username'], $this->request->getHeaderLine('password'));
        return $student_login->getCookie();
    }

    /**
     * 写入Response Body信息
     * @param $data Body
     * @return mixed Response
     */
    protected function writeResponseBody ($data)
    {
        $this->response->getBody()->write(json_encode(['messages' => 'OK', 'data' => $data]));
        $response = $this->response->withStatus(200);
        $response = $response->withHeader('Content-type', 'application/json');
        return $response;
    }

    /**
     * 根据学号生成collection名
     * eg. 学号是1305040000, 则生成的collection名是userinfo_2013, 其中'userinfo_20'是变量$this->collection_userinfo_prefix_name
     * @param $username
     * @return string
     */
    protected function getCollectionUserinfoNameByUsername ($username)
    {
        $collection_userinfo_name = $this->collection_userinfo_prefix_name . substr($username, 0, 2);
        return $collection_userinfo_name;
    }

    /**
     * 根据学号生成collection名
     * eg. 学号是1305040000, 则生成的collection名是grade_2013, 其中'grade_20'是变量$this->collection_userinfo_prefix_name
     * @param $username
     * @return string
     */
    protected function getCollectionGradeNameByUsername ($username)
    {
        $collection_userinfo_name = $this->collection_grade_prefix_name . substr($username, 0, 2);
        return $collection_userinfo_name;
    }

    /**
     * 根据学号和学生信息 生成适合插入数据库的格式的数组
     * @param $username
     * @param $userinfo
     * @return array
     */
    protected function getDocumentForInsertDatabaseUserinfo ($username, $userinfo)
    {
        $document = ['_id' => $username, 'data' => $userinfo];
        return $document;
    }

    /**
     * 根据学号、学期和成绩信息 生成适合插入数据库的格式的数组
     * @param $username
     * @param $userinfo
     * @return array
     */
    protected function getDocumentForInsertDatabaseGrade ($username, $semester, $grade)
    {
        $document = ['username' => $username, 'semester' => $semester, 'data' => $grade];
        return $document;
    }

    /**
     * 保存&更新 学生个人信息入库
     * @param $username
     * @param $userinfo
     */
    protected function saveUserinfoToDatabase ($username, $userinfo)
    {
        $db = new student_database_tools($this->database_dlpu_userinfo_name, $this->getCollectionUserinfoNameByUsername($username));
        $db->saveUserinfoToDatabase($username, $this->getDocumentForInsertDatabaseUserinfo($username, $userinfo));
        return NULL;
    }

    /**
     * 从数据库中拉取学生个人信息
     * @param $username
     */
    protected function getUserinfoFromDatabaseByUsername ($username)
    {
        $db = new student_database_tools($this->database_dlpu_userinfo_name, $this->getCollectionUserinfoNameByUsername($username));
        $res = $db->getUserinfoFromDatabaseByUsername($username);
        return $res->data;
    }

    /**
     * 保存&更新 学生成绩信息入库
     * @param $username     学号
     * @param $semester     学期 eg. 2015-2016-1
     * @param $grade        成绩信息
     * @return null
     */
    protected function saveGradeToDatabase ($username, $semester, $grade)
    {
        $db = new student_database_tools($this->database_dlpu_grade_name, $this->getCollectionGradeNameByUsername($username));
        $res = $db->saveGradeToDatabase($username, $semester, $this->getDocumentForInsertDatabaseGrade($username, $semester, $grade));
        return NULL;
    }

    /**
     * 从数据库中拉取学生成绩信息
     * @param $username
     * @param $semester
     * @return mixed
     */
    protected function getGradeFromDatabaseByUsernameAndSemester ($username, $semester)
    {
        $db = new student_database_tools($this->database_dlpu_grade_name, $this->getCollectionGradeNameByUsername($username));
        $res = $db->getGradeFromDatabaseByUsernameAndSemester($username, $semester);
        return $res->data;
    }
    
    /**
     * @return mixed 学生信息(学籍卡片)
     */
    public function userinfo ()
    {
        if(is_null($this->request->getHeaderLine('password'))) return $this->noPassword();

        // 是否需要最新数据
        if($this->request->getHeaderLine('latest') == 'yes') return $this->getUserinfoFromSchool();

        // 查看数据库中是否已有该条信息, 有则直接从数据库中拉数据
        $find_res = $this->getUserinfoFromDatabaseByUsername($this->arguments['username']);
        if($find_res) return $this->writeResponseBody($find_res);

        // 数据库中没有该条数据, 网页模拟登录抓取学生信息数据, 并存入数据库
        return $this->getUserinfoFromSchool();
    }

    /**
     * 从学校服务器获取学生个人信息 并保存入库
     * @return mixed
     */
    private function getUserinfoFromSchool ()
    {
        if($this->isLoginSuccess()) {
            require_once 'student_information.php';
            $student_information = new student_information($this->getCookie());
            $userinfo = $student_information->get();
            // 保存&更新 学生个人信息到数据库
            $this->saveUserinfoToDatabase($this->arguments['username'], $userinfo);
            return $this->writeResponseBody($userinfo);
        }else{
            return $this->wrongPassword();
        }
    }


    /**
     * @return mixed 学生成绩
     */
    public function usergrade ()
    {
        if(is_null($this->request->getHeaderLine('password'))) return $this->noPassword();

        // 是否需要最新数据
        if($this->request->getHeaderLine('latest') == 'yes') return $this->getUsergradeFromSchool();

        // 查看数据库中是否已有该条信息, 有则直接从数据库中拉数据
        $find_res = $this->getGradeFromDatabaseByUsernameAndSemester($this->arguments['username'], $this->arguments['kksj']);
        if($find_res) return $this->writeResponseBody($find_res);

        // 数据库中没有该条数据, 网页模拟登录抓取学生成绩信息数据, 并存入数据库
        return $this->getUsergradeFromSchool();
    }

    /**
     * 从学校服务器获取学生成绩信息 并保存入库
     * @return mixed
     */
    private function getUsergradeFromSchool ()
    {
        if($this->isLoginSuccess()) {
            require_once 'student_grade.php';
            $student_grade = new student_grade($this->getCookie());
            $usergrade = $student_grade->get($this->arguments['kksj'], '', '', 'all');
            // 保存&更新 学生成绩信息入库
            $this->saveGradeToDatabase($this->arguments['username'], $this->arguments['kksj'], $usergrade);
            return $this->writeResponseBody($usergrade);
        }else{
            return $this->wrongPassword();
        }
    }


    /**
     * @return mixed 公告信息
     */
    public function announcement ()
    {
        if(is_null($this->request->getHeaderLine('password'))) return $this->noPassword();

        if($this->isLoginSuccess()) {
            require_once 'student_announcement.php';
            $student_announcement = new student_announcement($this->getCookie());
            $announcement = $student_announcement->get();
            return $this->writeResponseBody($announcement);
        }else{
            return $this->wrongPassword();
        }

    }

    /**
     * @return mixed 我的课表->学期理论课表
     */
    public function curriculum_theory ()
    {
        if(is_null($this->request->getHeaderLine('password'))) return $this->noPassword();

        if($this->isLoginSuccess()) {
            require_once 'student_curriculum.php';
            $student_curriculum = new student_curriculum($this->getCookie());
            $curriculum_theory = $student_curriculum->curriculum_theory($this->arguments['semester'], $this->arguments['weeks']);
            return $this->writeResponseBody($curriculum_theory);
        }else{
            return $this->wrongPassword();
        }
    }

    /**
     * @return mixed 考试安排信息
     */
    public function exam_arrangement ()
    {
        if(is_null($this->request->getHeaderLine('password'))) return $this->noPassword();

        if($this->isLoginSuccess()) {
            require_once 'student_exam_arrangement.php';
            $student_curriculum = new student_exam_arrangement($this->getCookie());
            $exam_arrangement = $student_curriculum->get($this->arguments['semester'], $this->arguments['category']);
            return $this->writeResponseBody($exam_arrangement);
        }else{
            return $this->wrongPassword();
        }
    }

    /**
     * @return mixed 修改密码
     */
    public function change_password ()
    {
        if(is_null($this->request->getHeaderLine('password'))) return $this->noPassword();

        if($this->isLoginSuccess()) {
            require_once 'student_change_password.php';
            $change_password = new student_change_password($this->getCookie());
            $allPostPutVars = $this->request->getParsedBody();
            $res = $change_password->set($this->request->getHeaderLine('password'), $allPostPutVars['new_passwd']);
            return $this->writeResponseBody($res);
        }else{
            return $this->wrongPassword();
        }
    }


    /**
     * @return mixed 重置密码
     */
    public function reset_password ()
    {
        require_once 'student_reset_password.php';
        $reset_password = new student_reset_password();
        $allPostPutVars = $this->request->getParsedBody();
        $res = $reset_password->set($allPostPutVars['username'], $allPostPutVars['id_card']);
        return $this->writeResponseBody($res);
    }

}