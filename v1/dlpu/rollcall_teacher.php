<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2016/2/1
 * Time: 15:28
 */
require_once 'rollcall_database_tools.php';
require_once 'mydlpu_handle.php';

class rollcall_teacher {

    private $teacher_job_number;                                                 // 教师工号
    private $teacher_name;                                                       // 教师姓名
    private $teacher_id_number;                                                  // 教师身份证号码
    private $teacher_password;                                                   // 教师密码
    private $teacher_phone;                                                      // 教师手机号
    private $teacher_client_flag;                                                // 教师客户端标识
    private $teacher_current_room_number;                                        // 请求二维码当前教室
    private $teacher_current_corese_number;                                      // 请求二维码当前授课课程编号
    private $teacher_current_time;                                               // 请求二维码当前时间
    private $teacher_current_semester = '2015-2016-2';                           // 请求二维码当前学期
    private $teacher_current_session;                                            // 请求二维码当前节次
    private $database_dlpu_rollcall_name = 'dlpu_rollcall';                      // 签到系统数据库名
    private $collection_dlpu_rollcall_teacher_messages = 'teacher_messages';     // 教师信息集合(表)
    private $collection_dlpu_rollcall_qrcode_data = 'qrcode_data';               // 二维码数据集合(表)
    private $collection_dlpu_rollcall_qrcode = 'qrcode';                         // 生成的二维码信息集合(表)
    private $collection_dlpu_rollcall_student_record = 'student_record';         // 学生签到记录(表)


    /**
     * 签到系统
     * 二维码发放
     * @param $teacher_job_number
     * @param $teacher_password
     * @param $teacher_current_room_number
     * @param $teacher_current_session
     * @param string $teacher_client_flag
     * @return array
     */
    public function getQR($teacher_job_number, $teacher_password, $teacher_current_room_number, $teacher_current_session, $teacher_client_flag = '')
    {
        if(is_null($teacher_job_number) || is_null($teacher_password) || is_null($teacher_current_room_number) || is_null($teacher_current_session))
            return ['data' => 'Missing parameter', 'messages' => 'error'];

        $current_week = (new mydlpu_handle())->getCurrentWeek();
        $current_day = (new mydlpu_handle())->getCurrentDay();

        $identity_verify_res = $this->verifyTeacherIdentity($teacher_job_number, $teacher_password);
        if($identity_verify_res){
            $grant_time = time();
            $qrcode = $teacher_job_number . '|' . $teacher_current_room_number . '|' . $this->teacher_current_semester . '|' . $current_week . '|' . $current_day .  '|' . $teacher_current_session . '|' . $grant_time;
            $qrcode_base64 = base64_encode($qrcode);
            $prefix =  '';
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            for ( $i = 0; $i < 3; $i++ )
            {
                $prefix .= $chars[mt_rand(0, strlen($chars) - 1)];
            }
            $qrcode_data = ['teacher' => $teacher_job_number, 'room' => $teacher_current_room_number, 'semester' => $this->teacher_current_semester, 'week' => $current_week, 'day' => $current_day, 'session' => $teacher_current_session, 'granttime' => $grant_time, 'data' => $qrcode_base64];
            $this->saveQRcodeData($qrcode_data);
            return ['data' => $prefix. $qrcode_base64, 'messages' => 'OK'];
        }else{
            return ['data' => 'wrong job number or password', 'messages' => 'error'];
        }
    }


    /**
     * 教师端，获取当前节次学生签到详情
     * @param $teacher
     * @param $session
     * @return array|mixed
     */
    public function getRollCallDetail ($teacher, $semester, $week, $day, $session)
    {
        if(is_null($teacher) || is_null($semester) || is_null($week) || is_null($day) || is_null($session)) return ['data' => 'Missing parameter', 'messages' => 'error'];

        $object_rollcall_database_tools = new rollcall_database_tools($this->database_dlpu_rollcall_name, $this->collection_dlpu_rollcall_student_record);
        $res = $object_rollcall_database_tools->getRollCallDetail($teacher, $semester, $week, $day, $session);

        $data['teacher'] = $teacher;
        $data['semester'] = $semester;
        $data['week'] = $week;
        $data['day'] = $day;
        $data['session'] = $session;
        $data['count'] = count($res);
        foreach($res as $value) $data['student'][] = $value->student;

        return ['data' => $data, 'messages' => 'OK'];
    }

    /**
     * 删除某次点名信息
     * @param $teacher
     * @param $semester
     * @param $week
     * @param $day
     * @param $session
     * @param $password
     * @return array
     */
    public function deleteRollCallDetail ($teacher, $semester, $week, $day, $session, $password)
    {
        if(is_null($teacher) || is_null($semester) || is_null($week) || is_null($day) || is_null($session)) return ['data' => 'Missing parameter', 'messages' => 'error'];

        $identity_verify_res = $this->verifyTeacherIdentity($teacher, $password);

        if($identity_verify_res){
            $object_rollcall_database_tools = new rollcall_database_tools($this->database_dlpu_rollcall_name, $this->collection_dlpu_rollcall_student_record);
            $data = $object_rollcall_database_tools->deleteRollCallDetail($teacher, $semester, $week, $day, $session);
            return ['data' => TRUE, 'messages' => 'OK'];
        }else{
            return ['data' => 'wrong job number or password', 'messages' => 'error'];
        }
    }

    /**
     * 在请求二维码时 验证教师身份是否合法
     * @param $teacher_job_number
     * @param $teacher_password
     * @return bool
     */
    private function verifyTeacherIdentity($teacher_job_number, $teacher_password)
    {
        $object_rollcall_database_tools = new rollcall_database_tools($this->database_dlpu_rollcall_name, $this->collection_dlpu_rollcall_teacher_messages);
        return $object_rollcall_database_tools->verifyTeacherIdentityForGetQR($teacher_job_number, $teacher_password);
    }

    /**
     * 保存二维码数据
     * @param $qrcode_data
     * @return mixed
     */
    private function saveQRcodeData ($qrcode_data)
    {
        $object_rollcall_database_tools = new rollcall_database_tools($this->database_dlpu_rollcall_name, $this->collection_dlpu_rollcall_qrcode_data);
        return $object_rollcall_database_tools->saveQRcodeData($qrcode_data);
    }

}