<?php
require_once('DataTier.php' );
set_time_limit(2000); // 
class PostgreSQLTier extends DataTier
{
	public $type = 'postgresql';
	public $name = '';
	private $db = NULL;
	private $host = "localhost";
	private $port = "5434";
	private $connection ="";
	function __construct($name, $host, $port, $user, $password) {
		$this->type = 'postgresql';
		$this->name = $name;
		$this->host = $host;
		$this->port = $port;
		$this->user = $user;
		$this->password = $password;
		$this->connection = "host=".$host." port=".$port." dbname=".$name." user=".$user." password=".$password;
		
	}
	public function getHost()
	{
		return $this->host;
	}
	public function getPort()
	{
		return $this->port;
	}
	public function getUser()
	{
		return $this->user;
	}
	public function getPassword()
	{
		return $this->password;
	}
	function open()
	{
		if($this->db === NULL)
		{
			$db = pg_connect($this->connection);
			if($db !== false)
			{
				$this->db = $db;
			}
		}

	}
	function close()
	{
		if($this->db !== NULL)
		{
			pg_close($this->db);
			// PHP 8.1+: goi pg_close lan hai tren ket noi da dong se nem Error
			$this->db = NULL;
		}

	}
	// PHP 8: truyen false vao cac ham pg_* la TypeError, nen moi truy van
	// deu di qua day va tra ve false khi khong ket noi duoc hoac SQL loi.
	private function query($msg)
	{
		// Mot so noi goi truyen thang chuoi SQL thay vi Message (vd: DataTier::update/insert)
		$sql = is_string($msg) ? $msg : $msg->get("query");
		$this->open();
		if($this->db === NULL)
		{
			return false;
		}
		return pg_query($this->db, $sql);
	}

	public function validColumn($name)
	{
		return '"'.$name.'"';
	}
	function exec($msg)
	{
		$result = $this->query($msg);
		if($result === false)
		{
			return -1;
		}
		$error = pg_result_error($result);

		if($error != "")
		{
			return -1;
		}
		//$this->close();
		return 1;
	}

	function getValue($msg)
	{
		$result = $this->query($msg);
		if($result === false)
		{
			return "";
		}

		$numrows = pg_num_rows($result);
		$value = "";
		if($numrows>0)
		{
			$row = pg_fetch_array($result, 0);
			$value = $row[0];
		}
		//$this->close();
		return $value;
	}
	function getArray($msg)
	{
		$data = [];
		$result = $this->query($msg);
		if($result === false)
		{
			return $data;
		}
		$numrows = pg_num_rows($result);

		$fields = pg_num_fields($result);
		for($i=0; $i<$numrows; $i++)
		{
			$row = pg_fetch_array($result, $i);
			$arr = [];
			for($j =0; $j<$fields; $j++)
			{
				$arr[count($arr)] = $row[$j];
			}
			$data[count($data)] = $arr;
		}

		return $data;
	}
	function getTable($msg)
	{
		$dt = new DataTable("");
		$result = $this->query($msg);
		if($result === false)
		{
			return $dt;
		}
		$numrows = pg_num_rows($result);
		$fields = pg_num_fields($result);
		for($n =0; $n<$fields; $n++)
		{
			$fieldname = pg_field_name($result, $n);
			$fieldtype = pg_field_type($result, $n);
			if($fieldtype == 'int2' || $fieldtype == 'int4' || $fieldtype== 'int8')
			{
				$fieldtype = 'i';
			}else if($fieldtype == 'float8')
			{
				$fieldtype = 'f';
			}else if($fieldtype == 'timestamp')
			{
				$fieldtype = 'd';
			}
			else{
				$fieldtype = '';
			}
			$dt->addColumn($fieldname);
			$dt->getColumns()[count($dt->getColumns())-1]->setType($fieldtype);
		}
		for($i=0; $i<$numrows; $i++)
		{
			$row = pg_fetch_array($result, $i);
			
			for($j =0; $j<$fields; $j++)
			{
				$dt->setValueAt($row[$j], $i, $j);

			}
		}
		return $dt;
	}
	
}

?>