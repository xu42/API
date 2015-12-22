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
     * @return mixed 学生信息(学籍卡片)
     */
    public function userinfo ()
    {
        if(is_null($this->request->getHeaderLine('password'))) return $this->response->withStatus(401);

        $student_login = new student_login($this->arguments['username'], $this->request->getHeaderLine('password'));

        if($student_login->isSuccess()) {
            $student_login_cookie = $student_login->getCookie();

            require_once 'student_information.php';
            $student_information = new student_information($student_login_cookie);
            $userinfo = $student_information->getInfo();

            $this->response->getBody()->write(json_encode(['messages' => 'OK', 'data' => $userinfo]));
            $response = $this->response->withHeader('Content-type', 'application/json');
            return $response;
        }else{
            $this->response->getBody()->write(json_encode(['messages' => 'wrong student_id or password', 'data' => NULL]));
            $response = $this->response->withHeader('Content-type', 'application/json');
            return $response;
        }
    }

    /**
     * @return mixed 学生成绩
     */
    public function usergrade ()
    {
        if(is_null($this->request->getHeaderLine('password'))) return $this->response->withStatus(401);

        $student_login = new student_login($this->arguments['username'], $this->request->getHeaderLine('password'));
        if($student_login->isSuccess()) {
            $student_login_cookie = $student_login->getCookie();

            require_once 'student_grade.php';
            $student_grade = new student_grade($student_login_cookie);
            $usergrade = $student_grade->getGrade($this->arguments['kksj'], '', '', 'all');

            $this->response->getBody()->write(json_encode(['messages' => 'OK', 'data' => $usergrade]));
            $response = $this->response->withHeader('Content-type', 'application/json');
            return $response;
        }else{
            $this->response->getBody()->write(json_encode(['messages' => 'wrong student_id or password', 'data' => NULL]));
            $response = $this->response->withHeader('Content-type', 'application/json');
            return $response;
        }
    }


}