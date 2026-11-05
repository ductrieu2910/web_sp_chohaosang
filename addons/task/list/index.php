<?php
$msg = $appSession->getTier()->createMessage();
$ac = "";
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
	
	$search = "";
	if(isset($_REQUEST['search']))
	{
		$search = $_REQUEST['search'];
	}
	$fdate = "";
	if(isset($_REQUEST['fdate']))
	{
		$fdate = $_REQUEST['fdate'];
	}
	$tdate = "";
	if(isset($_REQUEST['tdate']))
	{
		$tdate = $_REQUEST['tdate'];
	}
	$sql = "SELECT d1.id, d2.receipt_no, d2.start_date, d2.name, d2.description, d1.status_id FROM res_assign d1 LEFT OUTER JOIN project_task d2 ON(d1.rel_id = d2.id) WHERE d1.status =0 AND d1.resource_id ='".$appSession->getUserInfo()->getId()."'";
	$sql = $sql." AND d2.start_date>='".$fdate."'";
	$sql = $sql." AND d2.start_date<='".$tdate."'";
	if($search != "")
	{
		$sql = $sql." AND (".$appSession->getTier()->buildSearch($search, ["d2.receipt_no"]).")";
	}
	
	$sql = $sql." ORDER BY d1.start_date ASC";

	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);

	$sql = "SELECT d1.id, d1.name FROM res_status d1 WHERE d1.status = 0 ORDER BY d1.sequence ASC";
	$msg->add("query", $sql);
	$dt_status = $appSession->getTier()->getTable($msg);

?>
<div class="row">
	<div class="col-12">
		<table class="table">
		<tr>
			<td><?php echo $appSession->getLang()->find("Code");?></td>
			<td><?php echo $appSession->getLang()->find("Date");?></td>
			<td><?php echo $appSession->getLang()->find("Name");?></td>
			<td><?php echo $appSession->getLang()->find("Description");?></td>
			<td><?php echo $appSession->getLang()->find("Status");?></td>
		</tr>
		<?php
		for($i =0; $i<$dt->getRowCount(); $i++)
		{
			$id = $dt->getString($i, "id");
			$status_id = $dt->getString($i, "status_id");
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
			<select class="form-control" onchange="statusChange(this, '<?php echo $id;?>')">
			<option value=""></option>
			<?php
			for($j =0; $j<$dt_status->getRowCount(); $j++)
			{
				$_id = $dt_status->getString($j, "id");
				$_name = $dt_status->getString($j, "name");
			?>
			<option value="<?php echo $_id;?>" <?php if($_id == $status_id){ echo " selected "; }?>><?php echo $_name;?></option>
			<?php
			}
			?>
			</select>
			</td>
		</tr>
		<?php
		}
		?>
		</table>
	</div>
</div>
<?php
}else if($ac == "save")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$name = "";
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$value = "";
	if(isset($_REQUEST['value']))
	{
		$value = $_REQUEST['value'];
	}
	$sql = "UPDATE res_assign SET ".$name." ='".$value."' WHERE id ='".$id."'";
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
}
?>
