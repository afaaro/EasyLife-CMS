<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class Database {
	protected $connection;

	public function __construct($hostname, $username, $password, $database, $port = '3306') {
		$this->connection = new mysqli($hostname, $username, $password, $database, $port);

		if ($this->connection->error) {
			throw new \Exception('Error: ' . $this->connection->error . '<br />Error No: ' . $this->connection->errno);
		}

		$this->connection->set_charset("utf8");
		$this->connection->query("SET SQL_MODE = ''");
	}

	public function __destruct() {
		$this->connection->close();
	}

	public function query($sql) {

		$query = $this->connection->query($this->ReplacePrefix($sql));

		if (!$this->connection->error) {
			if ($query instanceof \mysqli_result) {

				$data = array();
				while ($row = $query->fetch_assoc()) {
					$data[] = $row;
				}				

				$result = new \stdClass();
				$result->num_rows = $query->num_rows;
				$result->row = isset($data[0]) ? $data[0] : array();
				$result->rows = $data;
				$query->free();
				//$result->dbresult = $this->fetchFirstColumn($query);
				
				//$query->close();

				return $result;
			} else {
				return true;
			}
			
		} else {
			throw new \Exception('Error: ' . $this->connection->error  . '<br />Error No: ' . $this->connection->errno . '<br />' . $sql);
		}
	}

    /**
    * Select elements from database with query
    *
    * @param : { string } { $query } { mysql query to run }
    * @return : { array } { $data } { array of selected data }
    */

    function _select($query) {
        global $__devmsgs;
        if (is_array($__devmsgs)) {
            $start = microtime();
            $result = $this->connection->query($this->ReplacePrefix($query));
            $end = microtime();
            $timetook = $end - $start;
            devWitch($query, 'select', $timetook);
        } else {
            $result = $this->connection->query($this->ReplacePrefix($query));
        }
        $this->num_rows = $result->num_rows ;
        $data = array();

        for ($row_no = 0; $row_no < $this->num_rows; $row_no++) {
            $result->data_seek($row_no);
            $data[] = $result->fetch_assoc();
        }

        if($result)
            $result->close();

        return $data;
    }

    /**
    * Select elements from database with numerous conditions
    *
    * @param : { string } { $tbl } { table to select data from }
    * @param : { string } { $fields } { all by default, element to fetch }
    * @param : { string } { $cond } { false by default, mysql condition for fetching data }
    * @param : { integer } { $limit } { false by default, number of entires to fetch }
    * @param : { string } { $order } { false by default, order to sort results }
    * @return : { array } { $data } { array of selected data }
    */

    function select($tbl,$fields='*',$cond=false,$limit=false,$order=false,$ep=false) {
        //return dbselect($tbl,$fields,$cond,$limit,$order);
        global $__devmsgs;
        
        $query_params = '';
        //Making Condition possible
        if($cond)
            $where = " WHERE ";
        else
            $where = false;

        $query_params .= $where;
        if($where) {
            $query_params .= $cond;
        }

        if($order)
            $query_params .= " ORDER BY $order ";
        if($limit)
            $query_params .= " LIMIT $limit ";

       $query = " SELECT $fields FROM $tbl $query_params $ep ";
    
        if (is_array($__devmsgs)) {
            $start = microtime();
            $data = $this->_select($query);
            $end = microtime();
            $timetook = $end - $start;
            devWitch($query, 'select', $timetook);
            return $data;
        } else {
            return $this->_select($query);
        }
    }


    /**
    * Count values in given table using MySQL COUNT
    * 
    * @param : { string }   { $tbl } { table to count data from }
    * @param : { string } { $fields } { field that you want to count }
    * @param : { string } { $cond } { condition for mysql }
    * @return : { integer } { $field } { count of elements }
    */

    function count($tbl,$fields='*',$cond=false) {
        global $db,$__devmsgs;
        if ($cond)
            $condition = " WHERE $cond ";
        $query = "SELECT COUNT($fields) FROM $tbl $condition";
        if (is_array($__devmsgs)) {
            $start = microtime();
            $result = $this->_select($query);
            $end = microtime();
            $timetook = $end - $start;
            devWitch($query, 'count', $timetook);
        } else {
            $result = $this->_select($query);
        }
        
        $fields = $result[0];

        if ($fields) {
            foreach ($fields as $field)
                return $field;
        }

        return false;
    }

    /**
     * Get row using query
     * @param : { string } { $query } { query to run to get row }
     */

    function GetRow($query) {
        $result = $this->_select($query);
        if($result) return $result[0];
    }

    /**
     * Execute a MYSQL query directly without processing
     * @param : { string } { $query } { query that you want to execute }
     * @return : { array } { array of data depending on query }
     */

    function Execute($query) {
        global $__devmsgs;
        try {
            if (is_array($__devmsgs)) {
                $start = microtime();
                $data = $this->connection->query($query);
                $end = microtime();
                $timetook = $end - $start;
                devWitch($query, 'execute', $timetook);
                return $data;
            } else {
                return $this->connection->query($query);
            }
        } catch(DB_Exception $e) {
            $e->getError();
        }
    }

	public function ReplacePrefix($query) {
		return str_replace("#__",PREFIX,$query);
	}

	public function escape($value) {
		return $this->connection->real_escape_string($value);
	}
	
	public function Affected_Rows() {
		return $this->connection->affected_rows;
	}

	public function insert_id() {
		return $this->connection->insert_id;
	}
	
	public function connected() {
		return $this->connection->ping();
	}

    /*
     * Insert data into the database
     * @param string name of the table
     * @param array the data for inserting into the table
     */
    public function insert($table,$data){
        if(!empty($data) && is_array($data)){
            $columns = '';
            $values  = '';
            $i = 0;
            if(!array_key_exists('created',$data)){
                $data['created'] = date("Y-m-d H:i:s");
            }
            if(!array_key_exists('modified',$data)){
                $data['modified'] = date("Y-m-d H:i:s");
            }
            foreach($data as $key=>$val){
                $pre = ($i > 0)?', ':'';
                $columns .= $pre.$key;
                $values  .= $pre."'".$val."'";
                $i++;
            }
            $query = "INSERT INTO ".$table." (".$columns.") VALUES (".$values.")";
            $insert = $this->connection->query($query);
            return $insert ? $this->connection->insert_id : false;
        }else{
            return false;
        }
    }
    
    /*
     * Update data into the database
     * @param string name of the table
     * @param array the data for updating into the table
     * @param array where condition on updating data
     */
    public function update($table,$data,$conditions){
        if(!empty($data) && is_array($data)){
            $colvalSet = '';
            $whereSql = '';
            $i = 0;
            if(!array_key_exists('modified',$data)){
                $data['modified'] = date("Y-m-d H:i:s");
            }
            foreach($data as $key=>$val){
                $pre = ($i > 0)?', ':'';
                $colvalSet .= $pre.$key."='".$val."'";
                $i++;
            }
            if(!empty($conditions)&& is_array($conditions)){
                $whereSql .= ' WHERE ';
                $i = 0;
                foreach($conditions as $key => $value){
                    $pre = ($i > 0)?' AND ':'';
                    $whereSql .= $pre.$key." = '".$value."'";
                    $i++;
                }
            }
            $query = "UPDATE ".$table." SET ".$colvalSet.$whereSql;
            $update = $this->connection->query($query);
            return $update ? $this->connection->affected_rows : false;
        }else{
            return false;
        }
    }
    
    /*
     * Delete data from the database
     * @param string name of the table
     * @param array where condition on deleting data
     */
    public function delete($table,$conditions){
        $whereSql = '';
        if(!empty($conditions)&& is_array($conditions)){
            $whereSql .= ' WHERE ';
            $i = 0;
            foreach($conditions as $key => $value){
                $pre = ($i > 0) ? ' AND ' : '';
                $whereSql .= $pre.$key." = '".$value."'";
                $i++;
            }
        }
        $query = "DELETE FROM ".$table.$whereSql;
        $delete = $this->connection->query($query);
        return $delete ? true : false;
    }

    /*
     * Returns rows from the database based on the conditions
     * @param string name of the table
     * @param array select, where, search, order_by, limit and return_type conditions
     */
    public function getRows($table,$conditions = array()){
        $sql = 'SELECT ';
        $sql .= array_key_exists("select",$conditions)?$conditions['select']:'*';
        $sql .= ' FROM '.$table;
        if(array_key_exists("where",$conditions)){
            $sql .= ' WHERE ';
            $i = 0;
            foreach($conditions['where'] as $key => $value){
                $pre = ($i > 0)?' AND ':'';
                $sql .= $pre.$key." = '".$value."'";
                $i++;
            }
        }
        
        if(array_key_exists("search",$conditions)){
            $sql .= (strpos($sql, 'WHERE') !== false)?'':' WHERE ';
            $i = 0;
            foreach($conditions['search'] as $key => $value){
                $pre = ($i > 0)?' OR ':'';
                $sql .= $pre.$key." LIKE '%".$value."%'";
                $i++;
            }
        }
        
        if(array_key_exists("order_by",$conditions)){
            $sql .= ' ORDER BY '.$conditions['order_by']; 
        }
        
        if(array_key_exists("start",$conditions) && array_key_exists("limit",$conditions)){
            $sql .= ' LIMIT '.$conditions['start'].','.$conditions['limit']; 
        }elseif(!array_key_exists("start",$conditions) && array_key_exists("limit",$conditions)){
            $sql .= ' LIMIT '.$conditions['limit']; 
        }
        
        $result = $this->connection->query($sql);
        
        if(array_key_exists("return_type",$conditions) && $conditions['return_type'] != 'all'){
            switch($conditions['return_type']){
                case 'count':
                    $data = $result->num_rows;
                    break;
                case 'single':
                    $data = $result->fetch_assoc();
                    break;
                default:
                    $data = '';
            }
        }else{
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    $data[] = $row;
                }
            }
        }
        return !empty($data) ? $data : false;
    }
}