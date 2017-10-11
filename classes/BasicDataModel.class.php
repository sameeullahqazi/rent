<?php
	// require_once(dirname(dirname(__FILE__)) . '/includes/email_parsing.php');
	
	class BasicDataModel
	{
		var $id;
		var $obj_ready_only = false;

		public static $plurals = array(
			'loginattempt'				=> 'user_login_attempts',
			'pageredirect'				=> 'page_redirects',
			'employeeleaveapplication'	=> 'employee_leave_applications',
			'employeepayslip'			=> 'employee_pay_slips',
			'updatedatarequest'			=> 'update_data_requests',
		);
		// $encoded_password = strtolower(md5($mysqli->real_escape_string($password) . $this->salt));
		public static $salt = '9201340522657012';

		function __construct($id = null, $read_only_mode = true){
			if(!empty($id))
			{
				$id = "id='" . $id . "'";
				$this->Select($id, $read_only_mode);
				//error_log("base class constructor called! ".var_export($this, true));
			}
		}
		
		private static function plural($singular) {
			if (isset(BasicDataModel::$plurals[strtolower($singular)])) {
				return BasicDataModel::$plurals[strtolower($singular)];
			} else {
				return $singular.'s';
			}
		}

		// Fills the caller object with the data of the first row matching the criteria passed
		public function Select($criteria, $read_only_mode = true)
		{
			global $mysqli;
			
			// Set read only mode
			$this->obj_ready_only = $read_only_mode;
			try
			{
				$class_name = get_class($this);
				$object_attributes = array_diff_key(get_object_vars($this), get_class_vars(__CLASS__));
				$table_name = $this->plural(strtolower($class_name));
				$sql = "select * from $table_name where $criteria";
				// error_log("SQL in BasicDataModel::select(): " . $sql);
				$result = $mysqli->query($sql);

				if(!$result)
					Throw new Exception("Mysql error " . $mysqli->errno . " executing select statement: " . $sql . "   ---   " . $mysqli->error);

				$count = $result->num_rows;
				if($count > 0)
				{
					$row = $result->fetch_assoc();
					foreach($object_attributes as $name => $value)
						eval("\$this->\$name = \$row[\$name];");

					$this->id = $row['id'];
				}
				// error_log("this in Select(): ".var_export($this, true));
				return $count;
			}
			catch(Exception $e)
			{
				Throw $e;
			}
		}
		
		// Returns data from the table
		public function getTableData($criteria = '')
		{
			try
			{
				$class_name = get_class($this);
				$table_name = $this->plural(strtolower($class_name));
				
				if(!empty($criteria))
					$criteria = ' where ' . $criteria;
					
				$sql = "select * from $table_name $criteria";
				//error_log("sql: ".$sql);
				return self::getDataTable($sql);
			}
			catch(Exception $e)
			{
				Throw $e;
			}
		}

	/*
		// Inserts the data that the caller object is filled with in the database

		public function Insert()
		{
			global $mysqli;
			
			try
			{
				if($this->obj_ready_only)
				{
					die("Invalid operation! Trying to insert a read only object!");
				}

				$class_name = get_class($this);
				$object_attributes = array_diff(get_object_vars($this), get_class_vars(__CLASS__));
				$table_name = $this->plural(strtolower($class_name));

				$field_names = "";
				$field_values = "";

				foreach($object_attributes as $name => $value)
				{
					if(!is_null($value) && $name != 'id')//
					{
						if($field_names != '')
						{
							$field_names .= ', ';
							$field_values .= ', ';
						}
						$field_names .= $name;
						if(!in_array(strtolower($value), array('now()', 'null')) )
						{
							$value = $mysqli->real_escape_string($value);
							$value = "'".$value."'";
						}
						$field_values .= $value;
					}
				}
				$sql = "insert into $table_name ($field_names) values ($field_values)";
				//error_log("BasicDataModel Insert SQL: ".$sql);
				if(!$mysqli->query($sql))
				{
					Throw new Exception("Error executing Insert statement: ". (__LINE__) . " " . $mysqli->error);
				}
				$num_affected_rows = $mysqli->affected_rows;
				if($num_affected_rows > 0)
					$this->id = $mysqli->insert_id;

				return $num_affected_rows;
			}
			catch(Exception $e)
			{
				Throw $e;
			}

		}


		public function Update()
		{
			global $mysqli;
			
			try
			{
				if($this->obj_ready_only)
				{
					error_log("Invalid operation! Trying to update a read only object!");
					die("Invalid operation! Trying to update a read only object!");
				}

				$class_name = get_class($this);
				$object_attributes = array_diff(get_object_vars($this), get_class_vars(__CLASS__));

				$table_name = $this->plural(strtolower($class_name));

				$fields = "";
				foreach($object_attributes as $name => $value)
				{

					if(!is_null($value) && $name != 'id')
					{
						if($fields != '')
						{
							$fields .= ', ';
						}
						if(!in_array(strtolower($value), array('now()', 'null')) )
						{
							$value = $mysqli->real_escape_string($value);
							$value = "'".$value."'";
						}
						$fields .= $name." = ".$value;
					}
				}
				$sql = "update $table_name set $fields where id = '".$this->id."'";
				// error_log("Update SQL: ".$sql);
				$result = $mysqli->query($sql);
				if($mysqli->error) {
					error_log("Error executing Update statement: " . $mysqli->error . "    query: " . $sql);
					Throw new Exception("Error executing Update statement: ".$mysqli->error);
				}
				$num_affected_rows = $mysqli->affected_rows;
				return $num_affected_rows;
			}
			catch(Exception $e)
			{
				// error_log('caught exception in BasicDataModel: ' . $e);
				Throw $e;
			}
		}
		
		*/
		public function Insert()
		{
			global $mysqli;
			try
			{
				$field_names = "";
				$field_values = "";
				$num_affected_rows = 0;
				
				$class_name = get_class($this);
				$table_name = $this->plural(strtolower($class_name));
				
				if($this->obj_ready_only)
				{
					die("Invalid operation! Trying to insert a read only object!");
				}

				$obj = clone($this);
				unset($obj->{'id'});
				unset($obj->{'obj_ready_only'});

				foreach($obj as $name => $value)
				{
					if(!is_null($value))//
					{
						if($field_names != '')
						{
							$field_names .= ', ';
							$field_values .= ', ';
						}
						$field_names .= "`$name`";
						if(!in_array(strtolower($value), array('now()', 'null')) )
						{
							$value = $mysqli->real_escape_string($value);
							$value = "'".$value."'";
						}
						$field_values .= $value;
					}
				}
				if(!empty($field_names))
				{
					$sql = "insert into $table_name ($field_names) values ($field_values)";
					// error_log("BasicDataModel Insert SQL in Insert2: ".$sql);
				
					if(!$mysqli->query($sql))
					{
						Throw new Exception("Error executing Insert statement: ". (__LINE__) . " " . $mysqli->error . "\nSQL: ".$sql);
					}
					$num_affected_rows = $mysqli->affected_rows;
					if($num_affected_rows > 0)
						$this->id = $mysqli->insert_id;
						
					
				}

				return $num_affected_rows;
			}
			catch(Exception $e)
			{
				Throw $e;
			}
		}
		
		public function Update()
		{
			global $mysqli;
			try
			{
				if($this->obj_ready_only)
				{
					error_log("Invalid operation! Trying to update a read only object!");
					die("Invalid operation! Trying to update a read only object!");
				}
				
				$obj = clone($this);
				unset($obj->{'id'});
				unset($obj->{'obj_ready_only'});
				
				$fields = "";
				$num_affected_rows = 0;
				$class_name = get_class($this);
				$table_name = $this->plural(strtolower($class_name));

				foreach($obj as $name => $value)
				{
					if(!is_null($value))//
					{
						if($fields != '')
						{
							$fields .= ', ';
						}
						if(!in_array(strtolower($value), array('now()', 'null')) )
						{
							$value = $mysqli->real_escape_string($value);
							$value = "'".$value."'";
						}
						$fields .= "`".$name."` = ".$value;
					}
				}
				
				if(!empty($fields))
				{

					$sql = "update $table_name set $fields where id = '".$this->id."'";
					// error_log("Update SQL in Update2(): ".$sql);
					$rs = $mysqli->query($sql);
					if($mysqli->error) {
						error_log("Error executing Update statement: " . $mysqli->error . "\n    query: " . $sql);
						Throw new Exception("Error executing Update statement: ".$mysqli->error . "\nSQL: " . $sql);
					}
					$num_affected_rows = $mysqli->affected_rows;
				}
				return $num_affected_rows;
			}
			catch(Exception $e)
			{
				// error_log('caught exception in BasicDataModel: ' . $e);
				Throw $e;
			}
		}

		// Deletes the row matching the id of the caller object
		public function Delete()
		{
			global $mysqli;
			
			try
			{
				if($this->obj_ready_only)
				{
					die("Invalid operation! Trying to delete a read only object!");
				}

				$class_name = get_class($this);
				$table_name = $this->plural(strtolower($class_name));
				$sql = "delete from $table_name where id = '".$this->id."'";
				$result = $mysqli->query($sql);
				if(!$result)
					Throw new Exception("Error executing Delete statement: ".$mysql->error);

				$num_affected_rows = $mysqli->affected_rows;
				return $num_affected_rows;
			}
			catch(Exception $e)
			{
				Throw $e;
			}
		}
		
		/* Validates form field data according to the rules passed in $fields */
		public static function validateFormData($data, $fields)
		{
			// error_log("data: " . var_export($data, true));
			// error_log("Fields: ".var_export($fields, true));
			$errors = array();
			
			foreach($fields as $field => $checks)
			{
				foreach($checks as $type => $value)
				{
					switch($type)
					{
						// A required field; cannot be left empty
						case 'required':
							if($value == 1 && $data[$field] == '')
								$errors[$field] .= "Cannot be left empty. ";
							
							break;
					
						// The data must conform with the specified type
						case 'type':
							if($value == 'numeric' && !is_numeric($data[$field]))
								$errors[$field] .= "Must be a numeric value. ";
							
							break;
					
						// Input data cannot exceed the maximum length specified
						case 'length':
							break;
							
						case 'valid_email':
							if (!is_rfc3696_valid_email_address($data[$field]))
								$errors[$field] .= "Must be a valid email. ";
							
							break;
					
					}
				}
			}
			return $errors;
		}
		
		// Returns the resultset resulting from the specified SQL as an array 
		public static function getDataTable($sql)
		{
			global $mysqli;
			$data_table = array();
			
			try
			{
				$result = $mysqli->query($sql);
			
				if(!$result)
				{
					throw new Exception("SQL error in funtion BasicDataModel::getDataTable(): ".$mysqli->error);
				}
				else if($result->num_rows > 0)
				{
					while($row = $result->fetch_assoc())
					{
						$data_table[] = $row;
					}
				}

				return $data_table;
			}
			catch(Exception $e)
			{
				error_log($e->getMessage());
				throw $e;
			}
		}
		
		// Returns the first row of the resultset resulting from the specified SQL as an array 
		public static function getDataRow($sql)
		{
			global $mysqli;
			$row = array();
			
			try
			{
				$result = $mysqli->query($sql);

				if(!$result)
				{
					throw new Exception("SQL error in funtion BasicDataModel::getDataRow(): ".$mysqli->error . "\nSQL : " .$sql);
				}
				else if($result->num_rows > 0)
				{
					$row = $result->fetch_assoc();
				}

				return $row;
			}
			catch(Exception $e)
			{
				error_log($e->getMessage());
				throw $e;
			}
		}
		
		
		public static function getSQLInsertTableData($table_name, $table_data, $op = "insert")
		{
			global $mysqli;
			
			try
			{
				if(!empty($table_data))
				{
					$arr_field_names = array();
					$arr_field_values = array();
		
					foreach($table_data as $name => $value)
					{
						$value = $mysqli->real_escape_string($value);
						if(!in_array(strtolower($value), array('now()', 'null')) )
							$value = "'".$value."'";
						
						$arr_field_names[] = "`".$name."`";
						$arr_field_values[] = $value;
					}
		
					$sql = "$op into `$table_name` (".implode(',', $arr_field_names).") values (".implode(',', $arr_field_values).")";
					// error_log("Insert SQL in InsertTableData(): ".$sql);
					return $sql;
				}
			}
			catch(Exception $e)
			{
				error_log($e->getMessage());
				throw $e;
			}
			
		}
		
		/* Inserts table data */
		public static function InsertTableData($table_name, $table_data, $op = "insert")
		{
			global $mysqli;
			
			try
			{
				if(!empty($table_data))
				{
					$sql = BasicDataModel::getSQLInsertTableData($table_name, $table_data, $op);
					// error_log("Insert SQL in InsertTableData(): ".$sql);
					if(!$mysqli->query($sql))
						throw new Exception("SQL error in BasicDataObject::InsertTableData(): ".$mysqli->error.", \nSQL: ".$sql);
					else
						return $mysqli->insert_id;
				}
			}
			catch(Exception $e)
			{
				error_log($e->getMessage());
				throw $e;
			}
			
		}
		
		public static function UpdateTableData($table_name, $table_data, $row_id, $col_name = 'id')
		{
			global $mysqli;
			try
			{
				if(!empty($table_data))
				{
					$arr_field_names = array();
					$arr_field_values = array();
		
					foreach($table_data as $name => $value)
					{
						$value = $mysqli->real_escape_string($value);
						if(!in_array(strtolower($value), array('now()', 'null')) )
							$value = "'".$value."'";
						$table_data[$name] = $value;
					}
					$str_table_data = urldecode(http_build_query($table_data, '', ', '));

					$sql = "update `$table_name` set $str_table_data where $col_name = '" . $mysqli->real_escape_string($row_id) . "'";
					// error_log("Update SQL in BasicDataModel::UpdateTableData(): ".$sql);
				
					$result = $mysqli->query($sql);
					if(!$result)
					{
						error_log("SQL error in BasicDataObject::UpdateTableData(): ".$mysqli->error.", \nSQL: ".$sql);
						throw new Exception("SQL error in BasicDataObject::UpdateTableData(): ".$mysqli->error.", \nSQL: ".$sql);
					}
				}
			}
			catch(Exception $e)
			{
				throw $e;
			}

			return $mysqli->affected_rows;
		}
		
		public static function getDataColumns($table_name, $columns_to_include = null, $columns_to_exclude = null)
		{
			$rows = array();
			$sql = "show columns in $table_name";
			if(!empty($columns_to_include))
				$arr_where_clause[] = "Field in " . implode(', ', $columns_to_include);

			if(!empty($columns_to_exclude))
				$arr_where_clause[] = "Field not in " . implode(', ', $columns_to_exclude);
			
			if(!empty($arr_where_clause))
				$sql .= " where " . implode(" and ", $arr_where_clause);

			$rows = BasicDataModel::getDataTable($sql);
			return $rows;
		}

		public static function encodePassword($password)
		{
			global $mysqli;
			return strtolower(md5($mysqli->real_escape_string($password) . self::$salt));
		}
		
		/* Inserts table data */
		public static function InsertMultipleRows($table_name, $table_data)
		{
			global $mysqli;
			try
			{
				if(!empty($table_data))
				{
					$arr_data = array();
		
					foreach($table_data as $table_row)
					{
						$arr_field_names = array();
						$arr_field_values = array();
				
						foreach($table_row as $name => $value)
						{
							$arr_field_names[] = "`".$name."`";
							$arr_field_values[] = "'".$mysqli->real_escape_string($value)."'";
						}
						$arr_data[] = "(" . implode(',', $arr_field_values) . ")";
					}
		
					$sql = "insert into `$table_name` (".implode(',', $arr_field_names).") values ".implode(', ', $arr_data);
					// error_log("Insert SQL in InsertMultipleRows(): ".$sql);
					$result = $mysqli->query($sql);
					if(!$result)
					{
						error_log("SQL error in BasicDataObject::InsertTableData(): ".$mysqli->error.", \nSQL: ".$sql);
						throw new Exception("SQL error in BasicDataObject::InsertMultipleRows(): ".$mysqli->error.", \nSQL: ".$sql);
					}
				}
				
			}
			catch(Exception $e)
			{
				throw $e;
			}
			return $mysqli->insert_id;
		}
		
		public static function UpdateMultipleRows($table_name, $table_data, $update_column = 'id')
		{
			global $mysqli;
			try
			{
				$sql = "update $table_name set ";
			
				$data_values = array_values($table_data);
				$column_names = array_keys($data_values[0]);
			
				foreach($column_names as $i => $column_name)
				{	
					if($i > 0)
						$sql .= ", ";
					$sql .= "$column_name = (case";
					foreach($table_data as $column_val => $row)
					{
						$data_val = empty($row[$column_name]) ? 'NULL' : "'" . $mysqli->real_escape_string($row[$column_name]). "'";
						$sql .= " when $update_column = '$column_val' then " . $data_val . "";
					}
					$sql .= " end)";
				}
			
				$keys = array_keys($table_data);
				if(!empty($keys))
					$sql .= " where id in (" . implode (',', $keys). ")";
			
				error_log("Update SQL in BasicDataObject::UpdateMultipleRows(): " . $sql);
				if(!$mysqli->query($sql))
				{
					throw new Exception("SQL error in BasicDataModel::UpdateMultipleRows(): ".$mysqli->error.", \nSQL: ".$sql);
				}

				return $mysqli->affected_rows;
			}
			catch(Exception $e)
			{
				error_log($e->getMessage());
				throw $e;
			}
		}
		
		// A test function that demonstrates DB transactions; (commit and rollback)
		public static function testTransaction()
		{
			
			$test = new Test();
		//	Database::autocommit(false);
		 	Database::begin_transaction();
			try
			{
				$insert_sql = "insert into test (id, name) values (1, 'Test name')";
				$result = Database::query($insert_sql);
				
				$test->id = 2;
				$test->name = "Adeel Zahid' Khan";
				$test->Insert();
				$test->id = 3;
				$test->name = 'Naveed "Gulzaar';
				$test->Insert();
				
				$insert_sql = "insert into test (id, name) values (4, 'Another Test name')";
				$result = Database::query($insert_sql);
				
				error_log("Committing transaction in BasicDataModel::testTransaction() ...");
				Database::commit();
				// Database::autocommit(TRUE);
			}
			catch(Exception $e)
			{
				error_log("Rolling back transaction BasicDataModel::testTransaction() ..." . $e->getMessage());
				Database::rollback();
			}
		}

	}
?>
