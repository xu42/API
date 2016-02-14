<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2016/2/1
 * Time: 15:28
 */

require_once 'rollcall_database_tools.php';
require_once 'mydlpu_handle.php';

class rollcall_student {

    private $qrcode_data_student;
    private $database_dlpu_rollcall_name = 'dlpu_rollcall';                      // 签到系统数据库名
    private $collection_dlpu_rollcall_student_messages = 'student_messages';     // 学生信息集合(表)
    private $collection_dlpu_rollcall_student_record = 'student_record';         // 学生签到记录集合(表)


    /**
     * 将客户端传入的编码后的数据解析为数组
     * @param $qrcode_data_student
     * @return array
     */
    private function translationQRdata ($qrcode_data_student)
    {
        $qrcode_base64 = substr($qrcode_data_student, 3);
        $qrcode_data = base64_decode($qrcode_base64);

        if(!$qrcode_data) return ['data' => 'wrong format', 'messages' => 'error']; //格式错误
        $qrcode_array = explode('|', $qrcode_data);

        if(strlen($qrcode_array[7]) != 10) $qrcode_array[7] = (new rollcall_database_tools($this->database_dlpu_rollcall_name, $this->collection_dlpu_rollcall_student_messages))->getUsernameByClientFlag($qrcode_array[7])->_id; // 非微信用户
        return $qrcode_array;
    }
    
    /**
     * 保存学生签到数据
     * @return array
     */
    public function saveStudentRollcallRecord ($qrcode_data_student)
    {
        $qrcode_array = $this->translationQRdata($qrcode_data_student);
        $check_rollcall_record = $this->checkIsAlreadyRollcall($qrcode_array);
        if($this->checkIsExpired($qrcode_array['6'])) return ['data' => '二维码已失效(请在一个小时之内完成扫码)', 'messages' => 'error'];
        if($this->checkIsAlreadyRollcall($qrcode_array)) return ['data' => '本节课您已签到成功，请勿重复扫码签到', 'messages' => 'error'];
        (new rollcall_database_tools($this->database_dlpu_rollcall_name, $this->collection_dlpu_rollcall_student_record))->saveQRcodeData(['teacher' => $qrcode_array[0], 'room' => $qrcode_array[1], 'semester' => $qrcode_array[2], 'week' => $qrcode_array[3], 'day' => $qrcode_array[4], 'session' => $qrcode_array[5], 'granttime'=> $qrcode_array[6], 'student' => $qrcode_array[7], 'recordtime' =>$qrcode_array[8]]);
        return ['data' => $qrcode_array, 'messages' => 'OK'];
    }

    /**
     * 检测是否已经签到
     * @param $qrcode_data_student
     * @return bool
     */
    public function checkIsAlreadyRollcall ($qrcode_array)
    {
        return (new rollcall_database_tools($this->database_dlpu_rollcall_name, $this->collection_dlpu_rollcall_student_record))->checkIsAlreadyRollcall($qrcode_array[0], $qrcode_array[1], $qrcode_array[2], $qrcode_array[3], $qrcode_array[4], $qrcode_array[5], $qrcode_array[7]);
    }

    /**
     * 扫码应在二维码生成一小时之内完成
     * @param $granttime
     * @return bool
     */
    public function checkIsExpired ($granttime)
    {
        if($granttime + 3600 < time()) return TRUE;
        return FALSE;
    }
    
    /**
     * 保存学生绑定的客户端标识
     * @param $username
     * @param $client_flag
     * @return array
     */
    public function bindingStudentClient ($username, $client_flag)
    {
        $object_rollcall_database_tools = new rollcall_database_tools($this->database_dlpu_rollcall_name, $this->collection_dlpu_rollcall_student_messages);
        $object_rollcall_database_tools->saveStudentClientFlag($username, $client_flag);

        return ['data' => 'success', 'messages' => 'OK'];
    }
}