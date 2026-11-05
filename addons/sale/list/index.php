<?php

$search = '';
if(isset($_REQUEST['search']))
{
	$search = $_REQUEST['search'];
}
$fdate = '';
if(isset($_REQUEST['fdate']))
{
	$fdate = $_REQUEST['fdate'];
}
$tdate = '';
if(isset($_REQUEST['tdate']))
{
	$tdate = $_REQUEST['tdate'];
}
$status = [];
if(isset($_REQUEST['status']) && $_REQUEST['status'] != "")
{
	$status = $appSession->getTool()->split($_REQUEST['status'], ',');
}
$cancel_order = "0";
if(isset($_REQUEST['cancel_order']))
{
	$cancel_order = $_REQUEST['cancel_order'];
}


$sql = "SELECT d1.id, d1.order_no, d1.receipt_date, d2.code AS customer_code, d2.name AS customer_name, d2.phone AS customer_tel, d2.address AS customer_address, d3.name AS delivery_name, d3.address AS delivery_address, d3.start_date AS delivery_date, d3.tel AS delivery_phone, d3.description AS delivery_description, d8.name AS company_name, d9.name AS ward, d10.name AS dist, d11.name AS city, d1.company_id FROM sale_local d1 LEFT OUTER JOIN customer d2 ON(d1.customer_id = d2.id) LEFT OUTER JOIN sale_shipping d3 ON(d1.id = d3.sale_id) LEFT OUTER JOIN res_company d8 on(d1.company_id = d8.id) LEFT OUTER JOIN res_address d9 on(d3.address_id = d9.id) LEFT OUTER JOIN res_address d10 ON(d9.parent_id = d10.id) LEFT OUTER JOIN res_address d11 ON(d10.parent_id = d11.id) WHERE (d1.status =0 OR d1.status=3)";

if($fdate != "")
{
	$sql = $sql." AND d1.receipt_date>='".$fdate."'";
}
if($tdate != "")
{
	$sql = $sql." AND d1.receipt_date<='".$tdate."'";
}
if($search != "")
{
	$sql = $sql. " AND (".$appSession->getTier()->buildSearch(["d1.order_no", "d2.code", "d2.name", "d2.phone", "d2.address", "d3.name", "d3.address", "d3.tel", "d3.description", "d8.name", "d9.name", "d10.name", "d11.name"], $search).")";
}

$sql = $sql." ORDER BY d1.order_date DESC";
$msg = $appSession->getTier()->createMessage();
$msg->add("query", $sql);
$dt_orders = $appSession->getTier()->getTable($msg);

$line_ids = "";
for($i =0; $i<$dt_orders->getRowCount(); $i++)
{
	if($line_ids != "")
	{
		$line_ids = $line_ids." OR ";
	}
	$line_ids = $line_ids." d1.line_id='".$dt_orders->getString($i, "id")."' OR d1.rel_id='".$dt_orders->getString($i, "id")."'";
}


$sql = "SELECT d1.line_id, d1.rel_id, d2.name, d1.payment_id, SUM(d1.amount) AS amount FROM account_payment_line_local d1 LEFT OUTER JOIN account_payment d2 ON(d1.payment_id = d2.id) WHERE d1.status =0";
if($line_ids != "")
{
	$sql = $sql. " AND (".$line_ids.")";
}else{
	$sql = $sql. " AND 1=0";
}
$sql = $sql. " GROUP BY d1.line_id, d2.name, d1.payment_id, d1.rel_id";

$msg->add("query", $sql);
$dt_payments = $appSession->getTier()->getTable($msg);



?>
<div style="padding: 4px">
<div class="table-responsive">
<table class="table table-striped table-hover table-sm">
  <thead>
    <tr>
      <th >#</th>
      <th >Số đơn</th>
      <th>Ngày đặt</th>
	  <th>Thanh toán</th>
	  <th>Tiền thanh toán</th>
	  <th>Thực hiện</th>
	  <th>Liên hệ</th>
	  <th>Thời gian YC</th>
	  <th>Điện thoại</th>
	  <th>Địa chỉ</th>
	  <th>Ghi chú</th>
	  <th></th>
    </tr>
  </thead>
  <tbody>
	<?php
	
	for($i =0; $i<$dt_orders->getRowCount(); $i++)
	{
		$id = $dt_orders->getString($i, "id");
		$hasItem = true;
		if(count($status)>0)
		{
			$hasItem = false;
			for($n =0; $n<count($status); $n++)
			{
				for($j=0; $j<$dt_status_line->getRowCount(); $j++)
				{
					if($dt_status_line->getString($j, "id") == $id)
					{
						if($dt_status_line->getString($j, "status_id") == $status[$n])
						{
							$hasItem = true;
						}
						
						break;						
					}
					if($hasItem == true)
					{
						break;
					}
				}
				if($hasItem == true)
				{
					break;
				}
			}
		}
		if($hasItem == false)
		{
			continue;
		}
		$order_no = $dt_orders->getString($i, "order_no");
		$receipt_date = $dt_orders->getString($i, "receipt_date");
		$customer_code = $dt_orders->getString($i, "customer_code");
		$customer_name = $dt_orders->getString($i, "customer_name");
		$customer_phone = $dt_orders->getString($i, "customer_phone");
		$customer_address = $dt_orders->getString($i, "customer_address");
		$status_name = "";
		$forecolor = "";
		$backcolor = "";
		$company_id = "";
		$payment_name = "";
		$payment_amount = 0;
		for($j=0; $j<$dt_payments->getRowCount(); $j++)
		{
			if($dt_payments->getString($j, "line_id") == $id || $dt_payments->getString($j, "rel_id") == $id)
			{
				if($payment_name != "")
				{
					$payment_name = $payment_name." + ";
				}
				$payment_name = $payment_name.$dt_payments->getString($j, "name");
				$payment_amount = $payment_amount + $dt_payments->getFloat($j, "amount");
			}
		}
		
		
		$company_name = $dt_orders->getString($i, "company_name");
		$delivery_name = $dt_orders->getString($i, "delivery_name");
		$delivery_address = $dt_orders->getString($i, "delivery_address").", ".$dt_orders->getString($i, "ward").", ".$dt_orders->getString($i, "dist").", ".$dt_orders->getString($i, "city");
		$delivery_phone = $dt_orders->getString($i, "delivery_phone");
		$delivery_description = $dt_orders->getString($i, "delivery_description");
		$delivery_date = $dt_orders->getString($i, "delivery_date");
		if($delivery_date != "")
		{
			$d = $appSession->getTool()->toDateTime($delivery_date);
			$stime = $appSession->getTool()->paddingLeft($d->getHours(), '0', 2).":".$appSession->getTool()->paddingLeft($d->getMinutes(), '0', 2);
			
			$delivery_date = $appSession->getTool()->paddingLeft($d->getDays(), '0', 2)."/".$appSession->getTool()->paddingLeft($d->getMonths(), '0', 2)."/".$appSession->getTool()->paddingLeft($d->getYears(), '0', 2);
			$delivery_date = $delivery_date." ".$stime;
		}
		if($delivery_name == ""){
			$delivery_name = "Unknow - ".$customer_name;
		}
	?>
    <tr>
      <td><a href="javascript:loadSale('<?php echo $id;?>');"><img src="<?php echo URL;?>assets/images/edit.png"></a></td>
      <td><?php echo $order_no;?></td>
      <td><?php
	  if($receipt_date != "")
	{
		$d = $appSession->getTool()->toDateTime($receipt_date);
		$stime = $appSession->getTool()->paddingLeft($d->getHours(), '0', 2).":".$appSession->getTool()->paddingLeft($d->getMinutes(), '0', 2);
		
		$receipt_date = $appSession->getTool()->paddingLeft($d->getDays(), '0', 2)."/".$appSession->getTool()->paddingLeft($d->getMonths(), '0', 2)."/".$appSession->getTool()->paddingLeft($d->getYears(), '0', 2);
		echo $receipt_date." ".$stime;
	}
	  ?></td>
	   <td><?php echo $payment_name;?></td>
	   <td align="right"><?php echo $appSession->getCurrency()->format("", $payment_amount);?></td>
	   <td><a href="javascript:loadSale('<?php echo $id;?>');"><img src="<?php echo URL;?>assets/images/add.png"></a></td>
	    <td><?php echo $delivery_name;?></td>
		<td><?php echo $delivery_date;?></td>
		<td><?php echo $delivery_phone;?></td>
		<td><?php echo $delivery_address;?></td>
		<td><?php echo $delivery_description;?></td>
	   <td><a href="javascript:delOrder('<?php echo $id;?>');"><i class="zmdi zmdi-delete"></i></a></td>
    </tr>
   <?php
	}
	?>
  </tbody>
</table>
</div>
</div>



