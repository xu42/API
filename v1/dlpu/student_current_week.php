<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2016/1/19
 * Time: 14:47
 */
include_once 'student_crawl_tools.php';

class student_current_week extends student_crawl_tools {

    /**
     * @param $cookie
     */
    public function __construct()
    {
        parent::__construct('');
    }

    /**
     * 获取当前周次
     * @return mixed
     */
    public function get ()
    {
        $this->url = $this->url_index;
        $res_data = $this->myCurl($this->url, $this->cookie);
        $data = $this->re($res_data);
        return $data;
    }

    /**
     * 解析当前周次
     * @param $res_data
     * @return mixed
     */
    protected function re ($res_data)
    {
        preg_match('/xiaoli_c\">第 (\d+) 周</', iconv('GBK', 'UTF-8', $res_data), $weeks);
        return $weeks[1];
    }

}