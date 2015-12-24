<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2015/12/24
 * Time: 4:34
 */
include_once 'student_crawl_tools.php';

class student_curriculum extends student_crawl_tools {


    /**
     * student_curriculum constructor.
     */
    public function __construct($cookie)
    {
        parent::__construct($cookie);
//        $this->url = $this->url_login . $this->url_student_curriculum_theory;
//        $this->url = $this->url_login . $this->url_student_curriculum_experiment;
//        $this->url = $this->url_login . $this->url_student_curriculum_class;
//        $this->url = $this->url_login . $this->url_student_curriculum_teather;
//        $this->url = $this->url_login . $this->url_student_curriculum_classroom;
//        $this->url = $this->url_login . $this->url_student_curriculum_course;
    }

    /**
     * @param $semester 学年学期 格式例如：2015-2016-1
     * @param $weeks 周次 格式例如：1 (为空则获取本学期全部数据)
     */
    public function curriculum_theory($semester, $weeks)
    {
        $this->url = $this->url_login . $this->url_student_curriculum_theory;
        $this->postdata = 'xnxq01id=' . $semester . '&zc=' . $weeks . '&sfFD=1&demo=&cj0701id=';
        $res_data = $this->myCurl($this->url, $this->cookie, $this->postdata);
        $data = $this->reTheory($res_data);
        return $data;
    }


    /**
     * 解析出课表数据，三维数组，
     * 写的太恶心了，不注释了
     * @param $res_data 待解析的网页源代码
     */
    private function reTheory ($res_data)
    {
        preg_match_all('/<table(.*?)<\/table/s', $res_data, $theory_table);
        preg_match_all('/<tr>(.*?)<\/tr/s', $theory_table[1][1], $theory_tr);

        for($i = 0; $i < count($theory_tr[1]); $i++)
        {
            preg_match_all('/>(.*?)</',$theory_tr[1][$i],$temp);
            $theory_trr[] = $temp[1];
        }

        for($i = 0; $i < count($theory_trr); $i++)
        {
            $theory_empty[] = array_filter($theory_trr[$i]); // 删除数组空元素
        }

        for($i = 0; $i < count($theory_empty); $i++)
        {
            foreach($theory_empty[$i] as $v)
            {
                $theory[$i][] = $v;
            }
        }

        for($i = 1; $i < count($theory)-3; $i++)
        {
            $k = 0;
            for($j = 0; $j < count($theory[$i]);)
            {
                if($theory[$i][$j] != "&nbsp;")
                {
                    $table[$i-1][$k][0] = $theory[$i][$j];
                    $table[$i-1][$k][1] = $theory[$i][$j+1];
                    $table[$i-1][$k][2] = $theory[$i][$j+2];
                    $table[$i-1][$k][3] = $theory[$i][$j+4];
                    $j+=7;
                }else{
                    $j+=2;
                }
                $k+=1;
            }
        }

        return $table;
    }

}