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
    public function savePassword ($username, $password, $wechat_id)
    {
        $this->update(['_id' => $username, 'wechat_id' => $wechat_id], ['_id' => $username, 'wechat_id' => $wechat_id, 'password' => $password], ['multi' => false, 'upsert' => true]);
        return NULL;
    }

    /**
     * 根据用户名, 从数据库中获取用户密码
     * @param $username
     * @return bool
     */
    public function getPasswordFromDatabaseByUsername ($username)
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
     * 根据微信openid, 从数据库中获取用户密码
     * @param $wechat_id
     * @return bool
     */
    public function getPasswordFromDatabaseByWechatId ($wechat_id)
    {
        $filter = ['wechat_id' => $wechat_id];
        $options = ['limit' => 1];

        $res = $this->find($filter, $options);
        $res_array = $res->toArray();
        if($res_array){
            return $res_array[0];
        }
        return FALSE;
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
    public function getUserinfoFromDatabase ($username)
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
    public function getGradeFromDatabase ($username, $semester)
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

    
    /**
     * 保存&更新 我的课表->学期理论课表 信息入库
     * @param $username
     * @param $semester
     * @param $weeks
     * @param $document
     * @return null
     */
    public function saveCurriculumTheoryToDatabase ($username, $semester, $weeks, $document)
    {
        $this->update(['username' => $username, 'semester' => $semester, 'weeks' => $weeks], $document, ['multi' => false, 'upsert' => true]);
        return NULL;
    }

    /**
     * 查找数据库中是否有 我的课表->学期理论课表
     * @param $username
     * @param $semester
     * @param $weeks
     * @return bool
     */
    public function getCurriculumTheoryFromDatabase ($username, $semester, $weeks)
    {
        $filter = ['username' => $username, 'semester' => $semester, 'weeks' => $weeks];
        $options = ['limit' => 1];

        $res = $this->find($filter, $options);
        $res_array = $res->toArray();
        if($res_array){
            return $res_array[0];
        }
        return FALSE;
    }


    /**
     * 保存&更新 考试信息
     * @param $username
     * @param $semester
     * @param $category
     * @param $document
     * @return null
     */
    public function saveExamArrangementToDatabase ($username, $semester, $category, $document)
    {
        $this->update(['username' => $username, 'semester' => $semester, 'category' => $category], $document, ['multi' => false, 'upsert' => true]);
        return NULL;
    }

    /**
     * 查找数据库中是否有 考试信息
     * @param $username
     * @param $semester
     * @param $category
     * @return bool
     */
    public function getExamArrangementFromDatabase ($username, $semester, $category)
    {
        $filter = ['username' => $username, 'semester' => $semester, 'category' => $category];
        $options = ['limit' => 1];

        $res = $this->find($filter, $options);
        $res_array = $res->toArray();
        if($res_array){
            return $res_array[0];
        }
        return FALSE;
    }
    
}