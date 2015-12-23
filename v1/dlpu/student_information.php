<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2015/12/22
 * Time: 14:22
 */
include_once 'student_crawl_tools.php';

class student_information extends student_crawl_tools {

    /**
     * student_information constructor.
     */
    public function __construct($cookie)
    {
        parent::__construct($cookie);
        $this->url = $this->url_login . $this->url_student_information;
    }


    /**
     * 获取详细的用户信息
     * @return array 用户信息
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
        preg_match_all('/>(.+?)<\/td>/', $res_data, $match_data_table);
        preg_match_all('/img alt="照片" src="(.+?)"/', $res_data, $match_data_photo);

        foreach($match_data_table[1] as $value){ //去除 数组元素中的 &nbsp;
            $match_data_table_trim[] = str_replace('&nbsp;', '', $value);
        }
        $match_data_table_trim[0] = $this->url_host . $match_data_photo[1][0];
        unset($match_data_table_trim[188]);
        return $match_data_table_trim;
    }

}
