<?php

require_once(ABSPATH.'api/Sale.php' );

$msg = $appSession->getTier()->createMessage();

$sale_id = '';
if(isset($_REQUEST['sale_id']))
{
	$sale_id = $_REQUEST['sale_id'];
}
$msg = $appSession->getTier()->createMessage();

$sql = "SELECT customer_id FROM sale_local WHERE id='".$sale_id."'";

$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);
$customer_id = "";
$status_id = "";
if($dt->getRowCount()>0)
{
	$customer_id = $dt->getString(0, "customer_id");
	
}


$sql = "SELECT d2.id AS sale_id, d1.id AS sale_product_id, d1.parent_id ,d2.order_no, d2.order_date, d7.name AS delivery_name, d3.code AS product_code, d3.name AS product_name";
$sql = $sql.", d4.name AS unit_name, d5.name AS attriubte_name, d6.name AS type_name";
$sql = $sql.", d1.currency_id, d1.quantity, d1.unit_price, d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d1.rel_id";
$sql = $sql." FROM sale_product_local d1";
$sql = $sql." LEFT OUTER JOIN sale_local d2 ON(d1.sale_id = d2.id)";
$sql = $sql." LEFT OUTER JOIN product d3 ON(d1.product_id = d3.id)";
$sql = $sql." LEFT OUTER JOIN product_unit d4 ON(d1.unit_id = d4.id)";
$sql = $sql." LEFT OUTER JOIN attribute d5 ON(d1.attribute_id = d5.id)";
$sql = $sql." LEFT OUTER JOIN product_type d6 ON(d1.type_id = d6.id)";
$sql = $sql." LEFT OUTER JOIN sale_shipping d7 ON(d2.id = d7.sale_id)";
$sql = $sql." WHERE d1.status =0 AND d2.id='".$sale_id."'";
$sql = $sql." ORDER BY d1.create_date ASC";



$msg->add("query", $sql);
$dt = $appSession->getTier()->getTable($msg);




?>
<!-- Tab Items Start -->
<table class="table">
	<tr class="header">
		<td>
			<?php echo $appSession->getLang()->find("Name");?>
		</td>
		<td>
			<?php echo $appSession->getLang()->find("Price");?>
		</td>
		<td>
			<?php echo $appSession->getLang()->find("Attribute");?>
		</td>
		<td>
			<?php echo $appSession->getLang()->find("Type");?>
		</td>
	</tr>
		<?php
		$total = 0;
		$currency_id = "23";
		for($i =0; $i<$dt->getRowCount(); $i++)
		{
			if($dt->getString($i, "parent_id") != "")
			{
				continue;
			}
			$sale_product_id = $dt->getString($i, "sale_product_id");
			$product_code = $dt->getString($i, "product_code");
			$product_name = $dt->getString($i, "product_name");
			$attriubte_name = $dt->getString($i, "attriubte_name");
			$type_name = $dt->getString($i, "type_name");
			$quantity = $dt->getFloat($i, "quantity");
			$unit_price = $dt->getFloat($i, "unit_price");
			$amount = $quantity * $unit_price;
			$total = $total + $amount;
		
			$product_id = $dt->getString($i, "product_id");
			$unit_id = $dt->getString($i, "unit_id");
			$attribute_id = $dt->getString($i, "attribute_id");
			$type_id = $dt->getString($i, "type_id");
			$currency_id = $dt->getString($i, "currency_id");
			$rel_id = $dt->getString($i, "rel_id");
			
		
			
			
		?>
		
			<tr>
				<td >
				<?php echo $product_code;?>. <?php echo $product_name;?></h2>
				</td>
				<td align="right">
					<?php echo $appSession->getCurrency()->format($currency_id, $unit_price); ?>
				</td>
			
				<td>
					<?php echo $attriubte_name;?>
				</td>

			<td>
				<?php echo $type_name;?>
			</td>
			</tr>
			
			<?php
			for($j =0; $j<$dt->getRowCount(); $j++)
			{
				if($dt->getString($j, "parent_id") == $sale_product_id)
				{
					$product_code = $dt->getString($j, "product_code");
					$product_name = $dt->getString($j, "product_name");
					$quantity = $dt->getFloat($j, "quantity");
					$unit_price = $dt->getFloat($j, "unit_price");
					$amount = $quantity * $unit_price;
					$total = $total + $amount;
					$attriubte_name = $dt->getString($j, "attriubte_name");
					$type_name = $dt->getString($j, "type_name");
					
			?>
			
			<tr>
				<td >
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $product_code;?>. <?php echo $product_name;?></h2>
				</td>
				<td align="right">
					<?php echo $appSession->getCurrency()->format($currency_id, $unit_price); ?>
				</td>
			
				<td>
					<?php echo $attriubte_name;?>
				</td>

			<td>
				<?php echo $type_name;?>
			</td>
			</tr>
			<?php
				}
			}
			?>
		<?php
		}
		?>
			
	
	<!-- Food List End -->
	<?php
	$sql = "SELECT d1.rel_id, d1.category_id, d1.operator, SUM(d1.percent) AS percent, SUM(d1.value) AS value, d1.sequence";
	$sql = $sql." FROM account_service_line_local d1";
	$sql = $sql." LEFT OUTER JOIN account_service d2 ON(d1.service_id = d2.id)";
	$sql = $sql." WHERE d1.rel_id='".$sale_id."' AND d1.status =0 GROUP BY d1.rel_id, d1.category_id, d1.operator, d1.sequence ORDER BY d1.sequence ASC";
	$msg->add("query", $sql);
	
	
						
	$serviceList = $appSession->getTier()->getArray($msg);
	$subTotal = $total;
	$discount = 0;
	$service = 0;
	for($i =0; $i<count($serviceList); $i++)
	{
		$a = ($total * floatval($serviceList[$i][3])) + floatval($serviceList[$i][4]);
		if($serviceList[$i][2] == "+")
		{
			$service =  $service + $a;
			$total =  $total + $a;
		}else if($serviceList[$i][2] == "-")
		{
			$discount =  $discount -  $a;
			$total =  $total - $a;
		}else if($serviceList[$i][2] == "*")
		{
			$service =  $service *  $a;
			$total =  $total * $a;
		}else if($serviceList[$i][3] == "/")
		{
			$discount =  $discount /  $a;
			$total =  $total / $a;
		}
		
	}

	?>
		<tr>
			<td>
				<?php echo $appSession->getLang()->find("Sub Total");?>:
			</td>
			<td align="right">
			<?php echo $appSession->getCurrency()->format($currency_id, $subTotal);?>
			
			</td>
			<td></td>
			<td></td>
		</tr>
		<?php
		if($discount != 0)
		{
		?>
		<tr>
			<td>
				<?php echo $appSession->getLang()->find("Discount");?>:
			</td>
			<td align="right">
			<?php echo $appSession->getCurrency()->format($currency_id, $discount);?>
			
			</td>
			<td></td>
			<td></td>
		</tr>
		<?php
		}
		?>
		<?php
		if($service !=0)
		{
		?>
		<tr>
			<td>
				<?php echo $appSession->getLang()->find("Service");?>:
			</td>
			<td align="right">
			<?php echo $appSession->getCurrency()->format($currency_id, $service);?>
			
			</td>
			<td></td>
			<td></td>
		</tr>
		<?php
		}
		?>
		<tr>
			<td>
				<?php echo $appSession->getLang()->find("Total");?>:
			</td>
			<td align="right">
			<?php echo $appSession->getCurrency()->format($currency_id, $subTotal + $discount + $service);?>
			
			</td>
			<td></td>
			<td></td>
		</tr>
	</table>

		<div class="row">
			<div class="col-12">
				<a class="btn btn-outline-primary" href="javascript:addTask('<?php echo $sale_id;?>')"><?php echo $appSession->getLang()->find("Task");?></a>
				<a class="btn btn-outline-primary" href="javascript:createPayment('<?php echo $sale_id;?>')"><?php echo $appSession->getLang()->find("Payment");?></a>
			<a class="btn btn-outline-primary" href="<?php echo URL;?>addons/sale/invoice/?sale_id=<?php echo $sale_id;?>" target="_blank"><?php echo $appSession->getLang()->find("Invoice");?></a>
				
				<a class="btn btn-outline-primary" href="javascript:closeBill('<?php echo $sale_id;?>', function(message){ doneCloseBill(message); })"><?php echo $appSession->getLang()->find("Close");?></a>
			</div>
		</div>
	</div>
</div>
<!-- Tab Items End -->


		