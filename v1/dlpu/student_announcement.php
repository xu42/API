<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2015/12/23
 * Time: 21:39
 */

include_once 'student_crawl_tools.php';

class student_announcement extends student_crawl_tools {

    /**
     * student_announcement constructor.
     */
    public function __construct($cookie)
    {
        parent::__construct($cookie);
        $this->url = $this->url_login . $this->url_student_announcement;
    }

    /**
     * @return array 获取公告信息
     */
    public function get()
    {
        $res_data = $this->myCurl($this->url, $this->cookie);
        $data = $this->re($res_data);
        return $data;
    }


    /**
     * @param $res_data 网页源代码
     * @return array 用户信息
     */
    protected function re($res_data)
    {
        preg_match_all('/<tr>(.|\n)*?<\/tr>/', $res_data, $announcement_tr);
//        preg_match('/[\u4e00-\u9fa5]+/', $announcement_tr[0][1], $announcement_title);

        for($i=1; $i<=count($announcement_tr[0])-2; $i++) {
            preg_match_all('/">((.|\n)*?)<\/t/', $announcement_tr[0][$i+1], $list_temp);
            preg_match('/\'(.*?)\'/', $list_temp[1][4], $list_temp_a);
            $list_temp[1][4] = $this->url_host . $list_temp_a[1];
            $announcement_list[$i-1] = $list_temp[1];
        }

        return $announcement_list;
    }

}