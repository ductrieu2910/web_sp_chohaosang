<?php
require_once(ABSPATH.'api/Task.php' );
$msg = $appSession->getTier()->createMessage();
$rel_id = '';
if(isset($_REQUEST['rel_id']))
{
	$rel_id = $_REQUEST['rel_id'];
}
$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "")
{
	$ac = "view";
}
if($ac == "view")
{
$sql = "SELECT d1.id, d1.name, d1.receipt_no, d1.start_date, d1.description  FROM project_task d1 WHERE  d1.rel_id='".$rel_id."' ORDER BY d1.start_date ASC";
$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);
$create_uid = '';
if(isset($_REQUEST['create_uid']))
{
	$create_uid = $_REQUEST['create_uid'];
}
$sql = "SELECT d1.create_uid, d1.order_no, d2.commercial_name AS company_name, d2.address AS company_address, d2.phone AS company_phone, d1.order_date, d3.name AS customer_name, d3.phone AS customer_phone, d3.address AS customer_address, d3.email AS customer_email, d3.contact_name AS customer_contact_name, d3.email AS customer_email, d4.name AS delivery_name, d4.tel AS delivery_phone, d4.address AS delivery_address, d4.description AS delivery_description, d5.name AS delivery_ward_name, d6.name AS delivery_dist_name, d7.name AS delivery_city_name, d2.currency_id, d1.receipt_date, d4.start_date FROM sale_local d1 LEFT OUTER JOIN res_company d2 ON(d1.company_id= d2.id) LEFT OUTER JOIN customer d3 ON(d1.customer_id = d3.id) LEFT OUTER JOIN sale_shipping d4 ON(d1.id = d4.sale_id) LEFT OUTER JOIN res_address d5 ON(d4.address_id = d5.id) LEFT OUTER JOIN res_address d6 ON(d5.parent_id = d6.id) LEFT OUTER JOIN res_address d7 ON(d6.parent_id = d7.id) WHERE d1.id='".$rel_id."'";
$msg->add("query", $sql);
$dt_sale = $appSession->getTier()->getTable($msg);
$delivery_address = "";
$delivery_name = "";
$delivery_phone = "";

if($dt_sale->getRowCount()>0)
{
	if($create_uid == "")
	{
		$create_uid =  $dt_sale->getString(0, "create_uid");
	}
	$customer_contact_name = $dt_sale->getString(0, "customer_contact_name");
	$customer_name = $dt_sale->getString(0, "customer_name");
	$customer_phone = $dt_sale->getString(0, "customer_phone");
	$customer_address = $dt_sale->getString(0, "customer_address");
	$customer_contact_name = $dt_sale->getString(0, "customer_contact_name");
	$customer_email = $dt_sale->getString(0, "customer_email");
	$delivery_name = $dt_sale->getString(0, "delivery_name");
	$delivery_phone = $dt_sale->getString(0, "delivery_phone");
	$delivery_address = $dt_sale->getString(0, "delivery_address");
	$delivery_ward_name = $dt_sale->getString(0, "delivery_ward_name");
	$delivery_dist_name = $dt_sale->getString(0, "delivery_dist_name");
	$delivery_city_name = $dt_sale->getString(0, "delivery_city_name");
	$delivery_date = $dt_sale->getString(0, "order_date");
	if($delivery_date != "")
	{
		$delivery_date = date("d/m/Y", strtotime($delivery_date));
	}
	
	
	if($delivery_ward_name != "")
	{
		if($delivery_address != "")
		{
			$delivery_address = $delivery_address.", ";
		}
		$delivery_address = $delivery_address.$delivery_ward_name;
	}
	if($delivery_dist_name != "")
	{
		if($delivery_address != "")
		{
			$delivery_address = $delivery_address.", ";
		}
		$delivery_address = $delivery_address.$delivery_dist_name;
	}
	if($delivery_city_name != "")
	{
		if($delivery_address != "")
		{
			$delivery_address = $delivery_address.", ";
		}
		$delivery_address = $delivery_address.$delivery_city_name;
	}
}

$name = "";
$description = "";
$sql = "SELECT d1.id, d1.parent_id, d2.name, d3.name AS attribute_name, d4.name AS type_name FROM sale_product_local d1 LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id) LEFT OUTER JOIN attribute d3 ON(d1.attribute_id = d3.id) LEFT OUTER JOIN product_type d4 ON(d1.type_id = d4.id) WHERE d1.sale_id='".$rel_id."'";

$msg->add("query", $sql);
$dt_product = $appSession->getTier()->getTable($msg);
for($i =0; $i<$dt_product->getRowCount(); $i++)
{
	if($dt_product->getString($i, "parent_id") != "")
	{
		continue;
	}
	$id = $dt_product->getString($i, "id");
	if($name != "")
	{
		$name = $name."; ";
	}
	$name = $name.$dt_product->getString($i, "name");
	$attribute_name = $dt_product->getString($i, "attribute_name");
	$type_name = $dt_product->getString($i, "type_name");
	if($attribute_name != "")
	{
		$name = $name."/".$attribute_name;
	}
	if($type_name != "")
	{
		$name = $name."/".$type_name;
	}
	for($j =0; $j<$dt_product->getRowCount(); $j++)
	{
		if($dt_product->getString($j, "parent_id") != $id)
		{
			continue;
		}
		$name = $name." + ";
		$name = $name.$dt_product->getString($j, "name");
		$attribute_name = $dt_product->getString($j, "attribute_name");
		$type_name = $dt_product->getString($j, "type_name");
		if($attribute_name != "")
		{
			$name = $name."/".$attribute_name;
		}
		if($type_name != "")
		{
			$name = $name."/".$type_name;
		}
		
	}
	
}
$description = "";
if($delivery_name != "")
{
	$description = "Khách hàng: ".$delivery_name."<br>";
	$description = $description."Điên thoại: ".$delivery_phone."<br>";
	$description = $description."Địa chỉ: ".$delivery_address."<br>";
	
}
$sql = "Select d2.id, d2.name FROM res_user_company d1 LEFT OUTER JOIN res_user d2 ON(d1.user_id = d2.id) WHERE d1.status =0 AND d2.status =0 AND (d1.employee_id != '' OR d2.id='".$create_uid."')";
$msg->add("query", $sql);
$dt_user = $appSession->getTier()->getTable($msg);

?>
<div class="row">
	<div class="col-12">
		<form>
		  <div class="form-group">
			<label for="editname"><?php echo $appSession->getLang()->find("Name");?></label>
			<input type="text" class="form-control" id="editname" value="<?php echo $name;?>">
		  </div>
		  <div class="form-group">
			<label for="edituser_id"><?php echo $appSession->getLang()->find("Assign");?></label>
			<select class="form-control" id="edituser_id">
			<option value=""></option>
			<?php
			for($i =0; $i<$dt_user->getRowCount(); $i++)
			{
				$id = $dt_user->getString($i, "id");
				$name = $dt_user->getString($i, "name");
			?>
			<option value="<?php echo $id;?>"><?php echo $name;?></option>
			<?php
			}
			?>
			</select>
		  </div>
		  <div class="form-group">
			<label for="editdescription"><?php echo $appSession->getLang()->find("Description");?></label>
			<textarea type="text" class="form-control" id="editdescription"><?php echo $description;?></textarea>
		  </div>
		</form>
	</div>
	<div class="col-12">
		<button class="btn btn-primary" onclick="addTask()"><?php echo $appSession->getLang()->find("Add");?></button>
	</div>
</div>
<div class="row">
	<div class="col-12">
		<table class="table">
		<tr>
			<td><?php echo $appSession->getLang()->find("Code");?></td>
			<td><?php echo $appSession->getLang()->find("Date");?></td>
			<td><?php echo $appSession->getLang()->find("Name");?></td>
			<td><?php echo $appSession->getLang()->find("Description");?></td>
			<td><?php echo $appSession->getLang()->find("Assign");?></td>
		</tr>
		<?php
		for($i =0; $i<$dt->getRowCount(); $i++)
		{
			$id = $dt->getString($i, "id");
			$receipt_no = $dt->getString($i, "receipt_no");
			$receipt_date = $dt->getString($i, "start_date");
			if($receipt_date != "")
			{
				$receipt_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($receipt_date));
			}
			$name = $dt->getString($i, "name");
			$description = $dt->getString($i, "description");
			
			$sql = "Select d1.id, d2.name FROM res_assign d1 LEFT OUTER JOIN res_user d2 ON(d1.resource_id = d2.id) WHERE d1.status =0 AND d1.rel_id='".$id."'";
			$msg->add("query", $sql);
			$dt_user = $appSession->getTier()->getTable($msg);

		?>
		<tr>
			<td><?php echo $receipt_no;?></td>
			<td><?php echo $receipt_date;?></td>
			<td><?php echo $name;?></td>
			<td><?php echo $description;?></td>
			<td>
			<?php
			for($j =0; $j<$dt_user->getRowCount(); $j++)
			{
				$user_name = $dt_user->getString($j, "name");
			?>
			<?php echo $user_name;?>
			<?php
			}
			?>
			</td>
		</tr>
		<?php
		}
		?>
		</table>
	</div>
</div>
<script>
	function loadTask()
	{
		var _url = '<?php echo URL;?>addons/sale/task/?rel_id=<?php echo $rel_id;?>';
		loadPage('popupContent', _url, function(status, message)
		{
			if(status== 0)
			{
				
			}
		}, false);
		
	}
	function addTask()
	{
		var _url = '<?php echo URL;?>addons/sale/task/?ac=add&rel_id=<?php echo $rel_id;?>';
		var ctr = document.getElementById('editname');
		if(ctr.value == '')
		{
			alert("Please enter name");
			ctr.focus();
			return;
		}
		var name = ctr.value;
		ctr = document.getElementById('edituser_id');
		if(ctr.value == '')
		{
			alert("Please select assign");
			ctr.focus();
			return;
		}
		var user_id = ctr.value;
		ctr = document.getElementById('editdescription');
		var description = ctr.value;
		
		_url = _url + "&name=" + encodeURIComponent(name);
		_url = _url + "&user_id=" + encodeURIComponent(user_id);
		_url = _url + "&description=" + encodeURIComponent(description);
		console.log(_url);
		loadPage('pnProducts', _url, function(status, message)
		{
			if(status== 0)
			{
				loadTask();
			}
			
		}, true);
		
	}
</script>
<?php
}else if($ac == "add")
{
	$name = '';
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$description = '';
	if(isset($_REQUEST['description']))
	{
		$description = $_REQUEST['description'];
	}
	$user_id = '';
	if(isset($_REQUEST['user_id']))
	{
		$user_id = $_REQUEST['user_id'];
	}
	$task = new Task();
	$start_date = date("Y-m-d")." 00:00:00";  
	$end_date = date("Y-m-d")." 23:59:59"; 
	$resource = "";	
	$module_id = "project_task";
	$task_id = $task->addTask($appSession, "", $name, $start_date, $end_date, 0, "", 0, $rel_id, "", "", "", $description, $resource, module_id, "", $appSession->getUserInfo()->getId(), ""); 
	if($task_id != "")
	{
		$assign_id = $appSession->getTool()->getId();
		$builder = $appSession->getTier()->createBuilder("res_assign");
		$builder->add("id", $assign_id);
		$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
		$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
		$builder->add("name", "");
		$builder->add("resource_id", $user_id);
		$builder->add("module_id", $module_id);
		$builder->add("type", "res_user");
		$builder->add("proccess", 0);
		$builder->add("job_title_id", "");
		$builder->add("job_id", "");
		$builder->add("position_id", "");
		$builder->add("rel_id", $task_id);
		$builder->add("status", 0);
		$sql = $appSession->getTier()->getInsert($builder);
	
		$msg->add("query", $sql);
		$r = $appSession->getTier()->exec($msg);
	}
	echo $task_id;
}
?>
    
