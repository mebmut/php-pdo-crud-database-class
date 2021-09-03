<?php

    class CrudClass{

        private $_query,$_error = false,$_count = 0;
        private $_con, $_lastId = 0, $_results = [];

        public function __construct(){
            $host = 'localhost';
            $dbname = '_wizemedia';
            $dbuser = 'root';
            $dbpassword = '';
            try {
                $this->_con = new PDO('mysql:host='.$host.';dbname='.$dbname,$dbuser,$dbpassword);
                $this->_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->_con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
              } catch(PDOException $e) {
                die($e->getMessage());
              }

        }

        private function execute(string $sql,array $values=[]){
            $this->_error = false;
            if ($this->_query = $this->_con->prepare($sql)) {
                if ($this->_query->execute($values)) {
                    $this->_count = $this->_query->rowCount();
                    $this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
                    $this->_lastId = $this->_con->lastInsertId();
                }else{
                    $this->_error = true;
                }
            }
            return $this;
        }

        private function _read($table,$params){
            $conditions = '';
            $valueSet = [];
            if (array_key_exists('conditions',$params)) {
                foreach ($params['conditions'] as $key => $value) {
                   $conditions .= "$key = ? AND ";
                   $valueSet[] .= $value;
                }
                $conditions = ' WHERE '.rtrim($conditions," AND ");
            }
            //limit
            $limit = '';
            if (array_key_exists('limit',$params)) {
                $limit = " LIMIT ".$params['limit'];
            }
            //order
            $order = '';
            if (array_key_exists('order',$params)) {
                $order = " ORDER BY ".$params['order'];
            }
            //column
            $columns = '';
            if (array_key_exists('columns',$params)) {
                $columnArray = explode(',',$params['columns']);
                foreach ($columnArray as $key => $value) {
                    $columns .= "`$value`,"; 
                }
                $columns = trim($columns,',');
            }else{
                $columns = "*"; 
            }
            $sql = "SELECT {$columns} FROM {$table}{$conditions}{$order}{$limit}";
            if (!$this->execute($sql,$valueSet)->error()) {
                return true;
            }
            return false;
        }

        public function insert(string $table,array $values){
            $valueSet = [];
            $insertString = '';
            $valueString = '';

            if ($values) {
                foreach ($values as $key => $value) {
                   $insertString .= "`$key`,";
                   $valueString .= "?,";
                   $valueSet[] .= $value;
                }
                $valueString = rtrim($valueString,',');
                $insertString = rtrim($insertString,',');
                $sql = " INSERT INTO {$table}({$insertString}) VALUES({$valueString})";
                if(!$this->execute($sql,$valueSet)->error()){
                    return true;
                }

            }

        }

        public function update(string $table,int $id, array $values){
            $valueSet = [];
            $updateString = '';
            $conditions = "id = ".(int)$id;
            if ($values) {
                foreach ($values as $key => $value) {
                    $updateString .= "`$key` = ?,";
                    $valueSet[] .= $value;
                }$updateString = trim($updateString,',');
                $sql = "UPDATE `{$table}` SET {$updateString} WHERE {$conditions}";
                if(!$this->execute($sql,$valueSet)->error()){
                    return true;
                }
                return false;
            }
        }
        
        public function delete(string $table,int $id,array $conditions=[]){
            $valueSet = [$id];
            $conditionString = " id = ? AND ";
            if ($conditions) {
               foreach ($conditions as $key => $value) {
                   $conditionString .= "$key = ? AND ";
                   $valueSet[] .= $value;
               }
            }
            $conditionString = trim($conditionString,' AND ');
            $sql = "DELETE FROM `{$table}` WHERE {$conditionString}";
            if(!$this->execute($sql,$valueSet)->error()){
                return true;
            } return false;
        }

        public function findOne($table,$params=[]){
            $this->_read($table,$params);
            if ($this->_results) {
                return $this->_results[0];
            }
            return false;
        }

        public function findAll($table,$params=[]){
            $this->_read($table,$params);
            if ($this->_results) {
                return $this->_results;
            }
            return false;
        }

        public function count($name, $value){
            return $this->_count;
        }

        public function lastInsertId(){
            return $this->_lastId;
        }

        public function error(){
            return $this->_error;
        }
    }

?>