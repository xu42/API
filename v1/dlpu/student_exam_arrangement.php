<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2015/12/24
 * Time: 18:16
 */
require_once 'student_crawl_tools.php';

/**
 * Class student_exam_arrangement
 * 学生考试安排信息
 */
class student_exam_arrangement extends student_crawl_tools {

    /**
     * exam_arrangement constructor.
     */
    public function __construct($cookie)
    {
        parent::__construct($cookie);
    }

    /**
     * 获取考生考试安排信息
     * @param $semester     学年学期, eg. 2015-2016-1
     * @param $category     考试类别，1 => 期初, 2 => 期中, 3 => 期末
     * @return mixed        array 考试安排的数组列表
     */
    public function get ($semester, $category)
    {
        $this->url = $this->url_login . $this->url_student_exam_arrangement;
        $postdata = 'xnxqid=' . $semester . '&xqlb=' .$category;
        $res_data = $this->myCurl($this->url, $this->cookie, $postdata);
        $data = $this->re($res_data);
        return $data;
    }

    /**
     * @param $res_data     待解析的网页源码
     * @return mixed array  考试安排的数组列表
     */
    protected function re ($res_data)
    {
        preg_match_all('/<tr>(.*?)<\/tr>/s', $res_data,$temp_tr);

        for($i = 1; $i < count($temp_tr[1]); $i++)
        {
            preg_match_all('/>(.*?)<\/t/', $temp_tr[1][$i],$temp_td);
            $data[$i-1] = $temp_td[1];
            unset($data[$i-1][7]);
        }

        return $data;
    }


}