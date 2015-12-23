<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2015/12/22
 * Time: 16:56
 */

require_once 'student_login.php';

class slim_handle {

    public $request ='';
    public $response ='';
    public $arguments ='';


    /**
     * @param $request          Slim ServerRequestInterface request
     * @param $response         Slim ResponseInterface response
     * @param $arguments        Slim arguments
     */
    public function __construct ($request, $response, $arguments)
    {
        $this->request = $request;
        $this->response = $response;
        $this->arguments = $arguments;
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
     * @return mixed 学生信息(学籍卡片)
     */
    public function userinfo ()
    {
        if(is_null($this->request->getHeaderLine('password'))) return $this->noPassword();

        if($this->isLoginSuccess()) {
            require_once 'student_information.php';
            $student_information = new student_information($this->getCookie());
            $userinfo = $student_information->get();
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

        if($this->isLoginSuccess()) {
            require_once 'student_grade.php';
            $student_grade = new student_grade($this->getCookie());
            $usergrade = $student_grade->get($this->arguments['kksj'], '', '', 'all');
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

}