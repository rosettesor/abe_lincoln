<?php

namespace Norse\Util;

class DB
{
    private static $_instance = null;
    private $_connections = null;
    private $_configs = null;

    /**
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance($database = null)
    {
        if (null === self::$_instance) {
            self::$_instance = new static();
        }
        //are we requesting the wrapper?
        if(is_null($database)){
            return self::$_instance;
        //or a database?
        } elseif(isset(self::$_instance->_configs[$database])) {
            //this database exists in our config! Have we connected yet or not?
            if(isset(self::$_instance->_connections[$database])){
                //Yup, return it
                return self::$_instance->_connections[$database];
            } else {
                //Nope, create and return it
                self::$_instance->_connections[$database] = new sqlChild(
                    self::$_instance->_configs[$database]['host'],
                    self::$_instance->_configs[$database]['username'],
                    self::$_instance->_configs[$database]['password'],
                    self::$_instance->_configs[$database]['dbname'],
                    isset(self::$_instance->_configs[$database]['port']) ? self::$_instance->_configs[$database]['port'] : 3306,
                    isset(self::$_instance->_configs[$database]['socket']) ? self::$_instance->_configs[$database]['socket'] : null
                );
                return self::$_instance->_connections[$database];

            }
        //or an error?
        } else {

        }
    }

    public function loadConfig(\Base $app){
        $this->_configs = $app->get('db');
    }

    /**
     * protect from cloning and serialization
     *
     * @return void
     */
    protected function __construct(){}
    private function __clone(){ }
    private function __wakeup(){ }
}


class sqlChild extends \mysqli
{

    public function sql2val($strSQL, $tokens = false){
        $arrResult = array();
        if(is_array($tokens)){
            $stmt = @parent::prepare($strSQL);
            foreach ($tokens as $each_token) {
                $type = gettype($each_token);
                switch($type) {
                    case 'boolean':
                        //convert to a tinyint
                        $stmt->bind_param('i', intval($each_token));
                        break;
                    case 'integer':
                        $stmt->bind_param('i', $each_token);
                        break;
                    case 'double':
                        //includes floats
                        $stmt->bind_param('d', $each_token);
                        break;
                    case 'string':
                        $stmt->bind_param('s', $each_token);
                        break;
                    case 'NULL':
                        $stmt->bind_param('s', $each_token);
                        break;
                    //Array, Object, Resource & Unknown are not bindable
                    default:
                        throw new \Exception('Can not bind type ' . $type);
                        break;
                }
                $stmt->execute();

            }

        } else {
            @parent::real_query($strSQL);
        }
        $res = @parent::use_result();
        if ($res === FALSE) {
            return FALSE;
        } else {
            $row = $res->fetch_array();
            if ($row === '' || $row === FALSE || $row === NULL) {
                return FALSE;
            }
            return $row[0];
        }
    }

    function sql2arr($strSQL, $tokens = false) {
        $arrResult = array();
        if(is_array($tokens)){
            echo "Triggering prepared statement\r\n";
            $stmt = @parent::prepare($strSQL);
            foreach ($tokens as $each_token) {
                $type = gettype($each_token);
                switch($type) {
                    case 'boolean':
                        echo __LINE__ . "\r\n";
                        //convert to a tinyint
                        $stmt->bind_param('i', intval($each_token));
                        break;
                    case 'integer':
                        echo __LINE__ . "\r\n";
                        $stmt->bind_param('i', $each_token);
                        break;
                    case 'double':
                        echo __LINE__ . "\r\n";
                        //includes floats
                        $stmt->bind_param('d', $each_token);
                        break;
                    case 'string':
                        echo __LINE__ . "\r\n";
                        $stmt->bind_param('s', $each_token);
                        break;
                    case 'NULL':
                        echo __LINE__ . "\r\n";
                        $stmt->bind_param('s', $each_token);
                        break;
                    //Array, Object, Resource & Unknown are not bindable
                    default:
                        echo __LINE__ . "\r\n";
                        throw new \Exception('Can not bind type ' . $type);
                        break;
                }
            }

            $stmt->execute();
            $meta = $stmt->result_metadata();

            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }
            call_user_func_array(array($stmt, 'bind_result'), $parameters);
            while ($stmt->fetch()) {
                foreach($row as $key => $val) {
                    $x[$key] = $val;
                }
                $arrResult[] = $x;
            }
        } else {
            @parent::real_query($strSQL);
            if ($res = @parent::use_result()) {
                while ($row = $res->fetch_assoc()) {
                    array_push($arrResult, $row);
                }
            } else {
            }
        }

        return $arrResult;
    }
    /**
     * @param array $rows
     * @param $table
     * @param null $onDuplicateKey
     * @return bool|mysqli_result|null
     */
    public function insertRows(array $rows, $table, $onDuplicateKey = null)
    {

        if (empty($rows)) {
            return null;
        }
        $query = empty($onDuplicateKey) ? 'INSERT IGNORE INTO ' : 'INSERT INTO ';
        $query .= $table . ' (' . implode(',', array_keys(end($rows))) . ') VALUES';

        foreach ($rows as $row) {
            $query .= sprintf("('%s'),", implode("','", $row));
        }
        $query = trim($query, ',');
        $query .= empty($onDuplicateKey) ? ';' : ' ON DUPLICATE KEY ' . $onDuplicateKey;
        return $this->query($query);
    }

}