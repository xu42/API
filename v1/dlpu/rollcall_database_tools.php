<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2016/2/2
 * Time: 7:58
 */
error_reporting(0);

require_once 'mongodb.php';

class rollcall_database_tools extends mongodb {

    /**
     * rollcall_database_tools constructor.
     */
    public function __construct ($database_name, $collection_name)
    {
        parent::__construct($database_name, $collection_name);
    }


    /**
     * 在请求二维码时 验证教师身份是否合法
     * @param $teacher_job_number       教师工号
     * @param $teacher_password         教师密码
     * @return bool
     */
    public function verifyTeacherIdentityForGetQR ($teacher_job_number, $teacher_password)
    {
        $filter = ['_id' => $teacher_job_number];
        $options = ['limit' => 1];

        $res = $this->find($filter, $options);
        $res_array = $res->toArray();
        $res = $res_array[0];

        if($res_array != NULL && $teacher_password == $res->password) return TRUE;
        return FALSE;
    }

    /**
     * 保存二维码数据
     * @param $qrcode_data
     * @return mixed
     */
    public function saveQRcodeData ($qrcode_data)
    {
        return $this->insert($qrcode_data);
    }


    /**
     * 保存学生学号和客户端标识
     * @param $username
     * @param $client_flag
     * @return null
     */
    public function saveStudentClientFlag ($username, $client_flag)
    {
        return $this->update(['_id' => $username], ['_id' => $username, 'client_flag' => $client_flag], ['multi' => false, 'upsert' => true]);
    }

    /**
     * 通过学生端绑定的设备标识获取学生学号
     * @param $client_flag
     * @return mixed
     */
    public function getUsernameByClientFlag ($client_flag)
    {
        $filter = ['client_flag' => $client_flag];
        $options = ['limit' => 1];

        $res = $this->find($filter, $options);
        $res_array = $res->toArray();
        return $res_array[0];
    }

    /**
     * 检查一学生是否已经签到
     * @param $teacher
     * @param $room
     * @param $semester
     * @param $week
     * @param $day
     * @param $session
     * @param $student
     * @return mixed
     */
    public function checkIsAlreadyRollcall ($teacher, $room, $semester, $week, $day, $session, $student)
    {
        $filter = ['teacher' => $teacher, 'room' => $room, 'semester' => $semester, 'week' => $week, 'day' => $day, 'session' => $session, 'student' => $student];
        $options = ['limit' => 1];

        $res = $this->find($filter, $options);
        $res_array = $res->toArray();
        return $res_array[0];
    }

    /**
     * 教师端，获取当前节次学生签到详情
     * @param $teacher
     * @param $semester
     * @param $week
     * @param $day
     * @param $session
     * @return mixed
     */
    public function getRollCallDetail ($teacher, $semester, $week, $day, $session)
    {
        $filter = ['teacher' => $teacher, 'semester' => $semester, 'week' => $week, 'day' => $day, 'session' => $session];

        $res = $this->find($filter);
        $res_array = $res->toArray();
        return $res_array;
    }

    /**
     * 删除某次签到信息
     * @param $teacher
     * @param $semester
     * @param $week
     * @param $day
     * @param $session
     * @return string
     */
    public function deleteRollCallDetail ($teacher, $semester, $week, $day, $session)
    {
        $filter = ['teacher' => $teacher, 'semester' => $semester, 'week' => $week, 'day' => $day, 'session' => $session];
        $options = ['limit' => 0];

        return $this->delete($filter, $options);
    }

}