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
    private $collection_password_name = 'password';
    private $collection_userinfo_prefix_name = 'userinfo_20';
    private $database_dlpu_grade_name = 'dlpu_grade';
    private $collection_grade_prefix_name = 'grade_20';
    private $database_dlpu_curriculum_name = 'dlpu_curriculum';
    private $collection_curriculum_theory_prefix_name = 'theory_20';
    private $database_dlpu_exam_arrangement_name = 'dlpu_exam_arrangement';
    private $collection_exam_arrangement_prefix_name = 'exam_arrangement_20';

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
     * 接收客户端保存&更新密码的需求, 用户名、微信openid和密码,全部在Header中传输
     */
    public function savePasswordToDatabase ()
    {
        $username = $this->request->getgetHeaderLine('username');
        $wechat_id = $this->request->getgetHeaderLine('wechat_id');
        $password = $this->request->getgetHeaderLine('password');

        // 判断用户名密码是否正确, 正确则入库, 错误则不入库, 无论正确与错误都给出错误信息
        $is = (new student_login($username, $password))->loginAndReturnCookieOrFalse();
        if($is) {
            $db = new student_database_tools($this->database_dlpu_userinfo_name, $this->collection_password_name);
            $db->savePassword($username, $password, $wechat_id);
            return $this->writeResponseBody('succeess');
        }
        return $this->writeResponseBody('学号或密码错误', 'failed');
    }


    /**
     * 根据微信openid, 从数据库中获取用户密码
     * @param $wechat_id
     */
    private function getPasswordFromDatabaseByWechatId ($wechat_id)
    {
        $db = new student_database_tools($this->database_dlpu_userinfo_name, $this->collection_password_name);
        return $db->getPasswordFromDatabaseByWechatId($wechat_id)->password;
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
     * 检测是否登录成功 并返回Cookie或者False
     * @return 登录成功则返回cookie信息,登录失败则返回FALSE
     */
    protected function loginAndReturnCookieOrFalse ()
    {
//        $password = $this->getPasswordFromDatabaseByWechatId($this->request->getHeaderLine('wechat_id'));
//        $student_login = new student_login($this->arguments['username'], $password);
        $student_login = new student_login($this->arguments['username'], $this->request->getHeaderLine('password'));
        return $student_login->loginAndReturnCookieOrFalse();
    }

    /**
     * 获取 Cookie
     * @return string
     */
//    protected function getCookie ()
//    {
//        $student_login = new student_login($this->arguments['username'], $this->request->getHeaderLine('password'));
//        return $student_login->getCookie();
//    }

    /**
     * 写入Response Body信息
     * @param $data Body
     * @return mixed Response
     */
    protected function writeResponseBody ($data, $messages = 'OK')
    {
        $this->response->getBody()->write(json_encode(['messages' => $messages, 'data' => $data]));
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
     * 根据学号生成collection名 成绩信息
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
     * 根据学号生成collection名 我的课表->理论课表
     * eg. 学号是1305040000, 则生成的collection名是 theory_2013, 其中'theory_20'是变量$this->collection_curriculum_theory_prefix_name
     * @param $username
     * @return string
     */
    protected function getCollectionCurriculumTheoryNameByUsername ($username)
    {
        $collection_curriculum_theory_name = $this->collection_curriculum_theory_prefix_name . substr($username, 0, 2);
        return $collection_curriculum_theory_name;
    }

    /**
     * 根据学号生成collection名 考试安排
     * eg. 学号是1305040000, 则生成的collection名是 exam_arrangement_2013, 其中'exam_arrangement_20'是变量$this->collection_exam_arrangement_prefix_name
     * @param $usernam
     * @return string
     */
    protected function getCollectionExamArrangementNameByUsername ($username)
    {
        $collection_exam_arrangement_name = $this->collection_exam_arrangement_prefix_name . substr($username, 0, 2);
        return $collection_exam_arrangement_name;
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
     * 根据学号、学期、周次和课表信息 生成适合插入数据库的格式的数组
     * @param $username
     * @param $semester
     * @param $weeks
     * @param $curriculum_theory
     * @return array
     */
    protected function getDocumentForInsertDatabaseCurriculum ($username, $semester, $weeks, $curriculum_theory)
    {
        $document = ['username' => $username, 'semester' => $semester, 'weeks' => $weeks, 'data' => $curriculum_theory];
        return $document;
    }

    /**
     * 根据学号、学期、考试分类(期初、期中、期末)和考试安排信息 生成适合插入数据库的格式的数组
     * @param $username
     * @param $semester
     * @param $category
     * @param $exam_arrangement
     * @return array
     */
    protected function getDocumentForInsertDatabaseExamArrangement ($username, $semester, $category, $exam_arrangement)
    {
        $document = ['username' => $username, 'semester' => $semester, 'category' => $category, 'data' => $exam_arrangement];
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
    protected function getUserinfoFromDatabase ($username)
    {
        $db = new student_database_tools($this->database_dlpu_userinfo_name, $this->getCollectionUserinfoNameByUsername($username));
        return $db->getUserinfoFromDatabase($username);
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
    protected function getGradeFromDatabase ($username, $semester)
    {
        $db = new student_database_tools($this->database_dlpu_grade_name, $this->getCollectionGradeNameByUsername($username));
        return $db->getGradeFromDatabase($username, $semester);
    }

    /**
     * 保存&更新 我的课表->学期理论课表 信息入库
     * @param $username
     * @param $semester
     * @param $weeks
     * @return null
     */
    protected function saveCurriculumTheoryToDatabase ($username, $semester, $weeks, $curriculum_theory)
    {
        $db = new student_database_tools($this->database_dlpu_curriculum_name, $this->getCollectionCurriculumTheoryNameByUsername($username));
        $res = $db->saveCurriculumTheoryToDatabase($username, $semester, $weeks, $this->getDocumentForInsertDatabaseCurriculum($username, $semester, $weeks, $curriculum_theory));
        return NULL;
    }

    /**
     * 从数据库中拉取 我的课表->学期理论课表信息
     * @param $username
     * @param $semester
     * @param $weeks
     * @return mixed
     */
    protected function getCurriculumTheoryFromDatabase ($username, $semester, $weeks)
    {
        $db = new student_database_tools($this->database_dlpu_curriculum_name, $this->getCollectionCurriculumTheoryNameByUsername($username));
        return $db->getCurriculumTheoryFromDatabase($username, $semester, $weeks);
    }

    /**
     * 保存&更新 考试安排信息
     * @param $username
     * @param $semester
     * @param $category
     * @param $exam_arrangement
     * @return null
     */
    protected function saveExamArrangementToDatabase ($username, $semester, $category, $exam_arrangement)
    {
        $db = new student_database_tools($this->database_dlpu_exam_arrangement_name, $this->getCollectionExamArrangementNameByUsername($username));
        $res = $db->saveExamArrangementToDatabase($username, $semester, $category, $this->getDocumentForInsertDatabaseExamArrangement($username, $semester, $category, $exam_arrangement));
        return NULL;
    }

    /**
     * 从数据库中拉取 考试安排信息
     * @param $username
     * @param $semester
     * @param $category
     * @return bool
     */
    protected function getExamArrangementFromDatabase ($username, $semester, $category)
    {
        $db = new student_database_tools($this->database_dlpu_exam_arrangement_name, $this->getCollectionExamArrangementNameByUsername($username));
        return $db->getExamArrangementFromDatabase($username, $semester, $category);
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
        $find_res = $this->getUserinfoFromDatabase($this->arguments['username']);
        if($find_res) return $this->writeResponseBody($find_res->data);

        // 数据库中没有该条数据, 网页模拟登录抓取学生信息数据, 并存入数据库
        return $this->getUserinfoFromSchool();
    }

    /**
     * 从学校服务器获取学生个人信息 并保存入库
     * @return mixed
     */
    private function getUserinfoFromSchool ()
    {
        $isCookie = $this->loginAndReturnCookieOrFalse();
        if($isCookie) {
            require_once 'student_information.php';
            $student_information = new student_information($isCookie);
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
        $find_res = $this->getGradeFromDatabase($this->arguments['username'], $this->arguments['kksj']);
        if($find_res) return $this->writeResponseBody($find_res->data);

        // 数据库中没有该条数据, 网页模拟登录抓取学生成绩信息数据, 并存入数据库
        return $this->getUsergradeFromSchool();
    }

    /**
     * 从学校服务器获取学生成绩信息 并保存入库
     * @return mixed
     */
    private function getUsergradeFromSchool ()
    {
        $isCookie = $this->loginAndReturnCookieOrFalse();
        if($isCookie) {
            require_once 'student_grade.php';
            $student_grade = new student_grade($isCookie);
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

        $isCookie = $this->loginAndReturnCookieOrFalse();
        if($isCookie) {
            require_once 'student_announcement.php';
            $student_announcement = new student_announcement($isCookie);
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

        // 是否需要最新数据
        if($this->request->getHeaderLine('latest') == 'yes') return $this->getCurriculumTheoryFromSchool();

        // 查看数据库中是否已有该条信息, 有则直接从数据库中拉数据
        $find_res = $this->getCurriculumTheoryFromDatabase($this->arguments['username'], $this->arguments['semester'], $this->arguments['weeks']);
        if($find_res) return $this->writeResponseBody($find_res->data);

        // 数据库中没有该条数据, 从学校服务器获取, 并存入数据库
        return $this->getCurriculumTheoryFromSchool();
    }

    /**
     * 从学校服务器获取 我的课表->学期理论课表 并保存入库
     * @return mixed
     */
    protected function getCurriculumTheoryFromSchool ()
    {
        $isCookie = $this->loginAndReturnCookieOrFalse();
        if($isCookie) {
            require_once 'student_curriculum.php';
            $student_curriculum = new student_curriculum($isCookie);
            $curriculum_theory = $student_curriculum->curriculum_theory($this->arguments['semester'], $this->arguments['weeks']);
            // 保存&更新 学生成绩信息入库
            $this->saveCurriculumTheoryToDatabase($this->arguments['username'], $this->arguments['semester'], $this->arguments['weeks'], $curriculum_theory);
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

        // 是否需要最新数据
        if($this->request->getHeaderLine('latest') == 'yes') return $this->getExamArrangementFromSchool();

        // 查看数据库中是否已有该条信息, 有则直接从数据库中拉数据
        $find_res = $this->getExamArrangementFromDatabase($this->arguments['username'], $this->arguments['semester'], $this->arguments['category']);
        if($find_res) return $this->writeResponseBody($find_res->data);

        return $this->getExamArrangementFromSchool();
    }

    /**
     * 从学校服务器获取 考试安排信息
     * @return mixed
     */
    protected function getExamArrangementFromSchool ()
    {
        $isCookie = $this->loginAndReturnCookieOrFalse();
        if($isCookie) {
            require_once 'student_exam_arrangement.php';
            $student_curriculum = new student_exam_arrangement($isCookie);
            $exam_arrangement = $student_curriculum->get($this->arguments['semester'], $this->arguments['category']);
            // 保存&更新 考试安排信息入库
            $this->saveExamArrangementToDatabase($this->arguments['username'], $this->arguments['semester'], $this->arguments['category'], $exam_arrangement);
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

        $isCookie = $this->loginAndReturnCookieOrFalse();
        if($isCookie) {
            require_once 'student_change_password.php';
            $change_password = new student_change_password($isCookie);
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