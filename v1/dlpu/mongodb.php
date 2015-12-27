<?php
/**
 * Created by PhpStorm.
 * User: xuyan
 * Date: 2015/12/26
 * Time: 12:54
 */
class mongodb {

    /**
     * @var $host string                数据库服务器地址
     * @var $port string                数据库服务器端口号
     * @var $db_username string         数据库用户名
     * @var $db_password string         数据库密码
     * @var $database_name string       数据库名
     * @var $collection_name string     集合(表)名
     * @var $manger object
     */
    protected $host             = '127.0.0.1';
    protected $port             = '27017';
    protected $db_username      = '';
    protected $db_password      = '';
    protected $database_name    = '';
    protected $collection_name  = '';
    protected $namespace        = '';
    protected $manger           = NULL;
    protected $bluk             = NULL;
    protected $query            = NULL;


    /**
     * mongodb constructor.
     */
    public function __construct($database_name, $collection_name)
    {
        $this->database_name        = $database_name;
        $this->collection_name      = $collection_name;
        $this->namespace            = $this->database_name . '.' . $this->collection_name;
        $this->manger               = new MongoDB\Driver\Manager('mongodb://' . $this->host . ':' . $this->port);
        $this->bluk                 = new MongoDB\Driver\BulkWrite();
    }


    /**
     * Add an insert operation to the bulk
     * @param $document     array|object, A document to insert.
     * @return mixed        If the document did not have an _id, a MongoDB\BSON\ObjectID will be generated and returned; otherwise, no value is returned.
     */
    public function insert ($document)
    {
        $this->bluk->insert($document);
        try{
            $bluk_write_result = $this->manger->executeBulkWrite($this->namespace, $this->bluk);
        }catch (MongoDB\Driver\Exception $e){
            $bluk_write_result = $e->getMessage();
        }
        return $bluk_write_result;
    }


    /**
     * Add an update operation to the bulk
     * @param $filter       array|object, The search filter.
     * @param $new_obj      array|object, A document containing either update operators (e.g. $set) or a replacement document (i.e. only field:value expressions).
     * @param $options      array, ['multi' => false, 'upsert' => false] is default
     * @return string       没有返回值
     */
    public function update ($filter, $new_obj, $options = [])
    {
        $this->bluk->update($filter, $new_obj, $options);
        try{
            $bluk_write_result = $this->manger->executeBulkWrite($this->namespace, $this->bluk);
        }catch (MongoDB\Driver\Exception $e){
            $bluk_write_result = $e->getMessage();
        }
        return $bluk_write_result;
    }


    /**
     * Add a delete operation to the bulk
     * @param $filter       array|object, The search filter.
     * @param $options      array, ['limit' => 0] is default, Delete all matching documents (limit=0), or only the first matching document (limit=1)
     * @return string       没有返回值
     */
    public function delete ($filter, $options = [])
    {
        $this->bluk->delete($filter, $options);
        try{
            $bluk_write_result = $this->manger->executeBulkWrite($this->namespace, $this->bluk);
        }catch (MongoDB\Driver\Exception $e){
            $bluk_write_result = $e->getMessage();
        }
        return $bluk_write_result;
    }


    /**
     * @param $filter       array|object, The search filter.
     * @param $options      array, http://docs.php.net/manual/zh/mongodb-driver-query.construct.php
     * @return array
     */
    public function find ($filter, $options = [])
    {
        $this->query = new MongoDB\Driver\Query($filter, $options);
        try{
            $query_result = $this->manger->executeQuery($this->namespace, $this->query);
        }catch (MongoDB\Driver\Exception $e){
            $query_result = $e->getMessage();
        }
        return $query_result;
    }

}
