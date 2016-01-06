<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2015/12/22
 * Time: 14:22
 */
include_once 'student_crawl_tools.php';
include_once 'v1/alimedia/alimage.class.class.php';

class student_information extends student_crawl_tools {


    private $ak = '23189546';
    private $sk = 'e996bd1fe158861b762a3179e04bbeb6';
    private $namespace = 'dlpu';
    private $dir = '/photo';

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

        foreach($match_data_table[1] as $value){ //去除 数组元素中的 &nbsp;
            $match_data_table_trim[] = str_replace('&nbsp;', '', $value);
        }

        // 匹配出学号 $username[0]
        preg_match('/\d+/', $match_data_table_trim[6], $username);

        $save_photo = $this->savePhoto($username[0]);
        $match_data_table_trim[0] = $save_photo['url'];

        unset($match_data_table_trim[188]);
        return $match_data_table_trim;
    }

    /**
     * 获取照片并保存
     * @return mixed
     */
    private function savePhoto($username)
    {
        $this->url = $this->url_login . $this->url_student_photo;
        $filename = $username . '.jpg';

        // 检测文件是否存在
        if($this->isExists($filename)){     //存在，返回该文件的详细信息
            return $this->getFileInfo($filename);
        }else{      // 不存在，上传该文件并返回上传信息
            $image_data = $this->myCurl($this->url, $this->cookie);
            return $this->uploadToAli($image_data, $filename);
        }
    }

    /**
     * 上传照片到阿里百川http://wantu.taobao.com/
     * @param $image_data
     * @param $image_name
     * @return array
     */
    private function uploadToAli ($image_data, $filename)
    {
        $aliImage  = new AlibabaImage($this->ak, $this->sk);
        $uploadPolicy = new UploadPolicy($this->namespace);   // 上传策略。并设置空间名
        $uploadPolicy->dir = $this->dir; // 文件路径，(默认根目录"/")
        $uploadPolicy->name = $filename; // 文件名，(若为空，则默认使用文件名)
        $res = $aliImage->uploadData($image_data, $uploadPolicy);
        return $res;
    }

    /**
     * 判断文件是否已存在
     * @param $filename
     * @return bool
     */
    private function isExists($filename)
    {
        $aliImage  = new AlibabaImage($this->ak, $this->sk);
        $res = $aliImage->existsFile($this->namespace, $this->dir, $filename);
        return $res['exist'];
    }

    /**
     * 文件详细信息
     * @param $filename
     * @return array
     */
    private function getFileInfo ($filename)
    {
        $aliImage  = new AlibabaImage($this->ak, $this->sk);
        $res = $aliImage->getFileInfo($this->namespace, $this->dir, $filename);
        return $res;
    }
}
