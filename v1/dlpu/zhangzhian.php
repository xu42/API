<?php
error_reporting(0);
require_once 'mongodb.php';

class zhangzhian {

    public function insertOne($people_num, $desk_num, $dishes, $total_price)
    {
        $db = new mongodb('tmp_zhangzhian', 'dishes');
        $document = ['people' => $people_num, 'desk_num' => $desk_num, 'dishes' => $dishes, 'total_price' => $total_price];
        $db->insert($document);
        return ['data' => 'ok', 'messages' => 'success'];
    }
}