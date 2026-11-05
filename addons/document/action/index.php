<?php

require_once(ABSPATH.'config.php' );
$len = 0;
if(isset($_REQUEST["len"])) {
	$len = $_REQUEST["len"];
}
if(isset($_POST["len"])) {
	$len = $_POST["len"];
}
$path = DOC_PATH;
$dir = "";
$current_date =  date('Y-m-d');
if($current_date != "")
{
	$arr_date = explode("-", $current_date);
	if(count($arr_date)>2)
	{
		$dir = $arr_date[0]."/". + $arr_date[1]."/". + $arr_date[2];
	}
}
$path = $path."/".$dir;
if(is_dir($path) == false)
{
	if (!mkdir($path, 0777, true)) {
		die('Failed to create folders...');
	}
}
$message = "";
$company_id = $appSession->getConfig()->getProperty("company_id");
for($i =0; $i<$len; $i++)
{
	$file_id = $appSession->getTool()->getId();
	$file_name = $path."/".$file_id;
	if (move_uploaded_file($_FILES["file".$i]["tmp_name"], $file_name)) {
		$name = $_FILES["file".$i]["name"];
		$rel_id = "";
		if(isset($_REQUEST["rel_id".$i])) {
			$rel_id = $_REQUEST["rel_id".$i];
		}
		if(isset($_POST["rel_id".$i])) {
			$rel_id = $_POST["rel_id".$i];
		}
		$category_id = "";
		if(isset($_REQUEST["category_id".$i])) {
			$category_id = $_REQUEST["category_id".$i];
		}
		if(isset($_POST["category_id"])) {
			$category_id = $_POST["category_id".$i];
		}
		$extension = "";
		$arr = explode(".", $name);
		if(count($arr)>0)
		{
			$extension = $arr[count($arr) -1];
			$name= $arr[0];
		}
		$sql = "INSERT INTO document(";
		$sql = $sql."id, name, type, rel_id, status, create_uid, create_date, write_date";
		$sql = $sql.", path, ext, company_id, write_uid, category_id";
		$sql = $sql.") VALUES(";
		$sql = $sql."'".$file_id."', '".str_replace("'", "''", $name)."', 'file', '".$rel_id."', 0";
		$sql = $sql.", '', NOW(), NOW()";
		$sql = $sql.", '".$dir."', '".$extension."','".$company_id."', '', '".$category_id."')";
		$msg = $appSession->getTier()->createMessage();
		$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
		$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
		$msg->add("user_id", $appSession->getUserInfo()->getId());
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
		if($message != "")
		{
			$message = $message.",";
		}
		$message = $message.$file_id;
		
	}
	
	
}
echo $message;
?>
