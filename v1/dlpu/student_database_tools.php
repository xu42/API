<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2015/12/26
 * Time: 15:09
 */
include_once 'mongodb.php';
class student_database_tools extends mongodb {

    /**
     * student_database_tools constructor.
     */
    public function __construct ($database_name, $collection_name)
    {
        parent::__construct($database_name, $collection_name);
    }


    /**
     * 保存用户名和密码
     * @param $username
     * @param $password
     */
    public function saveUsernameAndPassword ($username, $password)
    {
        $this->update(['_id' => $username], ['_id' => $username, 'password' => $password], ['multi' => false, 'upsert' => true]);
        return NULL;
    }


    /**
     * 保存&更新 学生个人信息进数据库
     * @param $document
     * @return null
     */
    public function saveUserinfoToDatabase ($username, $document)
    {
        $this->update(['_id' => $username], $document, ['multi' => false, 'upsert' => true]);
        return NULL;
    }

    /**
     * 查找数据库中是否已有该学生的个人信息
     * @param $username
     */
    public function getUserinfoFromDatabaseByUsername ($username)
    {
        $filter = ['_id' => $username];
        $options = ['limit' => 1];

        $res = $this->find($filter, $options);
        $res_array = $res->toArray();
        if($res_array){
            return $res_array[0];
        }
        return FALSE;
    }


    /**
     * 保存&更新 成绩信息进数据库
     * @param $document
     * @return null
     */
    public function saveGradeToDatabase ($username, $semester, $document)
    {
        $this->update(['username' => $username, 'semester' => $semester], $document, ['multi' => false, 'upsert' => true]);
        return NULL;
    }

    /**
     * 查找数据库中是否有学生成绩信息
     * @param $username
     * @param $semester
     * @return bool
     */
    public function getGradeFromDatabaseByUsernameAndSemester ($username, $semester)
    {
        $filter = ['username' => $username, 'semester' => $semester];
        $options = ['limit' => 1];

        $res = $this->find($filter, $options);
        $res_array = $res->toArray();
        if($res_array){
            return $res_array[0];
        }
        return FALSE;
    }

}