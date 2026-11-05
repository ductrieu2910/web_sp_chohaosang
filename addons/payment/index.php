<?php
require_once(ABSPATH . 'api/Sale.php');
$sale = new Sale($appSession);
$sale_id = $sale->findSaleId();
$sale->checkSaleService($sale_id, $appSession->getConfig()->getProperty("customer_id"));

$sql = "SELECT d1.order_no FROM sale_local d1 WHERE d1.id='" . $sale_id . "'";

$msg->add("query", $sql);
$saleLine = $appSession->getTier()->getArray($msg);
$receipt_no = "";
if (count($saleLine) > 0) {
  $receipt_no = $saleLine[0][0];
}

$productList = $sale->productList($sale_id);

$sql = "SELECT d1.id, d1.name, d1.address FROM res_company d1 WHERE d1.status =0 AND d1.type='ONLINE'";
$msg->add("query", $sql);
$companies = $appSession->getTier()->getArray($msg);

$sql = "SELECT d1.id, d1.name, lg.description AS name_lg, d2.name AS category_name FROM account_payment d1 LEFT OUTER JOIN account_payment_category d2 ON(d1.category_id = d2.id) LEFT OUTER JOIN res_lang_line lg ON(d1.id = lg.rel_id AND lg.status=0 AND lg.name='account_payment_name' AND lg.lang_id='".$appSession->getConfig()->getProperty("lang_id")."')";
$sql = $sql." WHERE d1.status =0 ORDER BY d1.sequence ASC, d1.name ASC";
$msg->add("query", $sql);

$dt = $appSession->getTier()->getTable($msg);
$payment_method =[];
for($i =0; $i<$dt->getRowCount(); $i++)
{
	$payment_id = $dt->getString($i, "id");
	$payment_name = $dt->getString($i, "name_lg");
	if($payment_name == "")
	{
		$payment_name = $dt->getString($i, "name");
	}
	$payment_category_name = $dt->getString($i, "category_name");
	$payment_method[count($payment_method)] = [$payment_id, $payment_name, $payment_category_name];
}


$sql = "SELECT d1.id, d1.currency_id, d1.amount, d3.code AS currency_code, d2.name AS payment_name, d1.payment_id";
$sql = $sql . " FROM account_payment_line_local d1";
$sql = $sql . " LEFT OUTER JOIN account_payment d2 ON(d1.payment_id = d2.id)";
$sql = $sql . " LEFT OUTER JOIN res_currency d3 ON(d1.currency_id = d3.id)";
$sql = $sql . " WHERE d1.line_id='" . $sale_id . "' AND d1.status =0";

$msg->add("query", $sql);
$paymentLines = $appSession->getTier()->getArray($msg);
$totalSale = $sale->totalSalePrice($sale_id);

?>
<section class="section-eight">
        <div class="container">
            <div class="row">
                <div class="quality-main about-qulity-main fade_down">
                    <p class="quality"><?php echo $appSession->getLang()->find("Payment");?></p>
                </div>
                
                <div class="col-xxl-6 col-xl-6 col-lg-6">
					 <div class="contact-head-main">
						<h3><?php echo $appSession->getLang()->find("Product");?></h3>
                    <table class="table">
					  <?php
					  $total = 0;
					  for ($i = 0; $i < $productList->getRowCount(); $i++) {
						$id = $productList->getString($i, "id");
						$code = $productList->getString($i, "code");
						$document_id = $productList->getString($i, "document_id");
						$name = $productList->getString($i, "name_lg");
						$currency_id = $productList->getString($i, "currency_id");
						if ($name == "") {
						  $name = $productList->getString($i, "name");
						}
						$old_price = $productList->getFloat(0, "old_price");
						$unit_price = $productList->getFloat($i, "unit_price");
						$quantity = $productList->getFloat($i, "quantity");
						$amount = $unit_price * $quantity;

						$type_name = $productList->getString($i, "type_name");
						$attribute_name = $productList->getString($i, "attribute_name");

						$unit_name = $productList->getString($i, "unit_name");
						$currency_code = $productList->getString($i, "currency_code");
						$total = $total + $amount;
					  ?>
						<tr>
						  
							<td><a href="<?php echo URL; ?><?php echo  $appSession->getTool()->validUrl($name); ?>/p-<?php echo $code; ?>"><?php echo $name; ?></a>
							</td>
							<td><?php echo $unit_name;?></td>
							<td><?php echo $attribute_name;?></td>
							<td><?php echo $type_name;?></td>
							<td align="right"><?php echo $appSession->getCurrency()->format($currency_id, $unit_price); ?></td>
						 
						</tr>
					  <?php } ?>
					  
				
					  <?php
					  $sql = "SELECT d1.percent, d1.value, d1.category_id, d1.operator, d2.name";
					  $sql = $sql . " FROM account_service_line_local d1";
					  $sql = $sql . " LEFT OUTER JOIN account_service d2 ON(d1.service_id = d2.id)";
					  $sql = $sql . " WHERE d1.rel_id='" . $sale_id . "' AND d1.status =0 ORDER BY d1.sequence ASC";
					  $msg->add("query", $sql);

					  $serviceList = $appSession->getTier()->getArray($msg);
					  $amount = $total;

					  for ($i = 0; $i < count($serviceList); $i++) {
						$a = ($total * floatval($serviceList[$i][0])) + floatval($serviceList[$i][1]);
						if ($serviceList[$i][3] == "+") {
						  $amount =  $amount + $a;
						  $total =  $total + $a;
						} else if ($serviceList[$i][3] == "-") {
						  $amount =  $amount -  $a;
						  $total =  $total - $a;
						} else if ($serviceList[$i][3] == "*") {
						  $amount =  $amount *  $a;
						  $total =  $total * $a;
						} else if ($serviceList[$i][3] == "/") {
						  $amount =  $amount /  $a;
						  $total =  $total / $a;
						}
						$service_name = $serviceList[$i][4];
					  ?>
						<tr>
						  <td colspan="4"><?php echo $service_name; ?></td>
						  <td><?php echo $appSession->getCurrency()->format($appSession->getConfig()->getProperty("currency_id"), $a); ?></td>
						</tr>
					  <?php
					  }
					  ?>
					  <tr>
						<td colspan="4"><b><?php echo $appSession->getLang()->find("Total"); ?></b></td>
						<td align="right"><b><?php echo $appSession->getCurrency()->format($appSession->getConfig()->getProperty("currency_id"), $total); ?></b></td>
					  </tr>
				
					
					</table>
					
					</div>
                   
                </div>
                <div class="col-xxl-6 col-xl-6 col-lg-6">
					<div class="contact-head-main">
						<h3><?php echo $appSession->getLang()->find("Payment");?></h3>
						<table class="table">
						<?php
						for ($i = 0; $i < count($paymentLines); $i++) {
						  $_id = $paymentLines[$i][0];
						  $currency_id = $paymentLines[$i][1];
						  $amount = $appSession->getTool()->toDouble($paymentLines[$i][2]);
						  $currency_code = $paymentLines[$i][3];
						  $totalSale = $totalSale - $amount;
						  $payment_name = $paymentLines[$i][4];
						  $payment_id = $paymentLines[$i][5];
						?>
						<tr>
							<td><?php echo $payment_name; ?></td>
							<td align="right"><?php echo $appSession->getCurrency()->format($currency_id, $amount); ?></td>
							<td width="30"><a href="javascript:removePayment('<?php echo $_id; ?>')"><?php echo $appSession->getLang()->find("Delete");?></a></td>
						</tr>
							
						<?php
						}
						?>
						</table>
			
					
					<div class="accordion accordion-flush custom-accordion"
						id="accordionFlushExample">
						
						  <?php 
						  for ($i = 0; $i < count($payment_method); $i++) 
						  {
						  ?>
						<div class="accordion-item">
							<div class="accordion-header" id="flush-headingFour">
								<div class="accordion-button collapsed"
									data-bs-toggle="collapse"
									data-bs-target="#flush-payment_method<?php echo $i;?>">
									<div class="custom-form-check form-check mb-0">
										<label class="form-check-label" for="payment_method<?php echo $i;?>"><input
												class="form-check-input mt-0" type="radio"
												name="flexRadioDefault" id="payment_method<?php echo $i;?>" onclick="paymentMethod('<?php echo $payment_method[$i][0]; ?>', '<?php echo $payment_method[$i][2]; ?>')";><?php echo $payment_method[$i][1]; ?></label>
									</div>
								</div>
							</div>
							<div id="flush-payment_method<?php echo $i;?>"
								class="accordion-collapse collapse"
								data-bs-parent="#accordionFlushExample">
								<div class="accordion-body">
									<p class="cod-review" id="paymentInfo<?php echo $payment_method[$i][0]; ?>">
									</p>
								</div>
							</div>
						</div>
							<?php
							
							}
							?>

					</div>
					
					 <?php 
					 if ($productList->getRowCount() > 0) 
					{ 
				?>
					
				
					<div class="Submit mb-0">
                            <a href="javascript:checkOut()" class="btn-main btn2"><?php echo $appSession->getLang()->find("Check Out");?>
                                <span class="arrow-section">
                                    <img class="arrow" src="<?php echo URL;?>assets/images/svg/right-arrow-svg.svg"
                                        alt="right-arrow-svg">
                                </span>
                                <div class="btn-box-left btn2"></div>
                                <div class="btn-box-right btn2"></div>
                            </a>
                    </div>
					<?php
					}
					?>
						
                    
                </div>
            </div>
        </div>
    </section>
	<script>
  
  var category_name = '';
  var payment_term = '';

  function paymentMethod(id, name) {

	payment_id = id;
	category_name = name;
    if (category_name == "CREDITCARD") {
      paymentBank('ATM');
      return;
    }

    var params = "ac=payment_type_amount";
    params = params + "&lang_id=<?php echo $appSession->getConfig()->getProperty("lang_id"); ?>";
    params = params + "&session_id=<?php echo $appSession->getConfig()->getProperty("session_id"); ?>";
    params = params + "&user_id=<?php echo $appSession->getConfig()->getProperty("user_id"); ?>";
    params = params + "&customer_id=<?php echo $appSession->getConfig()->getProperty("customer_id"); ?>";
    params = params + "&category_name=" + category_name;
    var _url = "<?php echo URL; ?>api/product/?" + params;
    
    loadPage('contentView', _url, function(status, message) {
      if (status == 0) {

        payment_term = message;
        buildPayment();

      } else {
        console.log(message);
      }

    }, true);
  }
  var total = <?php echo $totalSale; ?>;
  var balance = 0;
  var payment_points = "";
  var vouchers = "";
  var payment_amount = 0;
  var payment_description = "";

  function buildPayment() {

    var s = '';
    if (category_name == "WALLET") {
      var items = payment_term.split(';');
      var holders = new Array();

      for (var j = 0; j < items.length; j++) {


        var item = items[j].split(":");

        if (item.length == 3) {
          holders.push(item);
        }
      }
      var s = "";
      for (var j = 0; j < holders.length; j++) {
        if (j > 0) {
          s = s + "<hr>";
        }
        var wallet_amount = parseFloat(holders[j][0]);
        var h_id = holders[j][1];
        var holder_code = holders[j][2];
        if (wallet_amount > total) {
          wallet_amount = total;
        }
        s = s + holder_code + ": <b>" + wallet_amount.toLocaleString('en-US') + "</b>";
        s = s + '<input type="button" class ="btn btn-success" onclick="addWallet(\'' + h_id + '\', ' + wallet_amount + ')" value="<?php echo $appSession->getLang()->find("Add Payment"); ?>">';

      }

    } else if (category_name == "LOYALTY") {

      var arr = payment_term.split(';');
      var j = 0;
      for (var i = 0; i < arr.length; i++) {

        var items = arr[i].split(':');

        if (items.length == 3) {

          var point = items[0];
          var amount = items[1];
          var paid = items[2];
          s = s + '<span style="padding:4px"><input type="button" style="min-width:150px; marign:4px" class="btn btn-secondary" onclick="paymentPoint(' + point + ', ' + paid + ')" value="' + amount + ' - ' + point + '"></span>';
          j += 1;
          if (j == 3) {
            s = s + "<br><br>";
            j = 0;
          }

        }
      }
    } else if (category_name == "VOUCHER") {
      var arr = payment_term.split('\n');

      for (var i = 0; i < arr.length; i++) {

        var items = arr[i].split('\t');

        if (items.length == 4) {

          var name = items[0];
          var code = items[1];
          var currency_id = items[2];
          var amount = items[3];
          s = s + '<span style="padding:4px"><input type="button" style="width:100%; marign:4px" class="btn btn-secondary" onclick="addPaymentVoucherCode(\'' + code + '\')" value="' + amount + ' - ' + code + '/' + name + '"></span>';
          s = s + "</br></br>";

        }
      }
      s += '<div class="custom-input">';
      s += '<label for="editvoucher_code" class="form-label"><?php echo $appSession->getLang()->find("Voucher Code"); ?></label>';
      s += '<input class="form-control" type="text" id="editvoucher_code">';
      s += '</div>';
      s += '<div class="custom-textarea" >';
      s += '<label for="editpayment_description" class="form-label"><?php echo $appSession->getLang()->find("Description"); ?></label>';
      s += '<textarea class="form-control" id="editpayment_description"></textarea>';
      s += '</div>';
      s += '<button class ="btn btn-animation fw-bold" onclick="addPaymentVoucher()"><?php echo $appSession->getLang()->find("Add Payment"); ?></button>'
    } else {
      var paid = total;

      s += '<div class="custom-input">';
      s += '<label for="editpayment_amount" class="form-label"><?php echo $appSession->getLang()->find("Payment Amount"); ?></label>';
      s += '<input type="text" id="editpayment_amount" class="form-control" value="' + paid + '">';
      s += '</div>';
      s += '<div class="custom-textarea">';
      s += '<label for="editpayment_description" class="form-label"><?php echo $appSession->getLang()->find("Description"); ?></label>';
      s += '<textarea class="form-control" id="editpayment_description"></textarea>';
      s += '</div>';
      s += '<br>';
      s += '<button class="btn btn-animation fw-bold" onclick="addPayment()"><?php echo $appSession->getLang()->find("Add Payment"); ?></button>';
    }
    var ctr = document.getElementById("paymentInfo" + payment_id);
    if (ctr != null) {
      ctr.innerHTML = s;
    }

  }

  function paymentBank(bank_code) {

    var return_action = "APP_ORDER_<?php echo $sale_id; ?>";
    var postData = "order_id=" + return_action + "&order_desc=Payment-FOSACHA";
    postData = postData + "&order_type=billpayment";
    postData = postData + "&amount=<?php echo $total; ?>";
    postData = postData + "&language=vn";
    postData = postData + "&bank_code=" + bank_code;
    var _url = "<?php echo URL; ?>vnpay_php/?" + postData;

    loadPage('contentView', _url, function(status, message) {
      if (status == 0 && message.indexOf("code") != -1) {

        var obj = JSON.parse(message);
        if (obj.code == "00" && obj.message == "success") {

          window.location.href = obj.data;
        } else {
          console.log(message);
        }
      } else {
        console.log(message);
      }

    }, true);


  }
  var holder_id = "";

  function addWallet(id, amount) {
    holder_id = id;
    payment_amount = amount;
    doAddPayment();

  }

  function addPayment() {
    var ctr = document.getElementById('editpayment_amount');
    if (ctr.value == '') {
      alert('<?php echo $appSession->getLang()->find("Please enter your payment amount"); ?>');
      ctr.focus();
      return;
    }
    var amount = parseFloat(ctr.value);
    if (category_name == 'WALLET' && amount > balance) {
      alert('<?php echo $appSession->getLang()->find("Paymenent amount is less than or equal"); ?> ' + balance);
      ctr.focus();
      return;
    }
    if (amount > total) {
      amount = total;
    }
    if (amount <= 0) {

      alert('<?php echo $appSession->getLang()->find("Invalid payment amount"); ?>');
      return;
    }
    var ctr = document.getElementById('editpayment_description');
    payment_description = ctr.value;

    payment_amount = amount;
    doAddPayment();
  }

  function paymentPoint(point, amount) {
    payment_amount = amount;
    payment_points = point;
    doAddPayment();
  }

  function addPaymentVoucherCode(code) {
    vouchers = code;
    doAddPayment();
  }

  function addPaymentVoucher() {
    var ctr = document.getElementById('editvoucher_code');
    if (ctr.value == '') {
      alert('<?php echo $appSession->getLang()->find("Please enter your voucher code"); ?>');
      ctr.focus();
      return;
    }
    vouchers = ctr.value;
    var ctr = document.getElementById('editpayment_description');
    payment_description = ctr.value;
    doAddPayment();
  }

  var running = false;

  function doAddPayment() {

    if (running == true) {
      return;
    }
    running = true;
    var params = "ac=addPayment";
    params = params + "&lang_id=<?php echo $appSession->getConfig()->getProperty("lang_id"); ?>";
    params = params + "&company_id=<?php echo $appSession->getConfig()->getProperty("company_id"); ?>";
    params = params + "&session_id=<?php echo $appSession->getConfig()->getProperty("session_id"); ?>";
    params = params + "&user_id=<?php echo $appSession->getConfig()->getProperty("user_id"); ?>";
    params = params + "&customer_id=<?php echo $appSession->getConfig()->getProperty("customer_id"); ?>";

    params = params + "&payment_id=" + payment_id;
    params = params + "&holder_id=" + holder_id;
    params = params + "&payment_amount=" + encodeURIComponent(payment_amount);
    params = params + "&payment_points=" + encodeURIComponent(payment_points);
    params = params + "&payment_description=" + encodeURIComponent(payment_description);
    params = params + "&vouchers=" + encodeURIComponent(vouchers);
    var _url = "<?php echo URL; ?>api/product/?" + params;

    loadPage('contentView', _url, function(status, message) {
      if (status == 0) {
        running = false;
        if (message.indexOf("OK") != -1) {

          document.location.reload();
        }
      } else {
        console.log(message);
      }

    }, true);
  }

  function removePayment(id) {
    var result = confirm("Want to remove payment?");
    if (!result) {
      return;
    }
    var params = "ac=removePayment";
    params = params + "&lang_id=<?php echo $appSession->getConfig()->getProperty("lang_id"); ?>";
    params = params + "&company_id=<?php echo $appSession->getConfig()->getProperty("company_id"); ?>";
    params = params + "&session_id=<?php echo $appSession->getConfig()->getProperty("session_id"); ?>";
    params = params + "&user_id=<?php echo $appSession->getConfig()->getProperty("user_id"); ?>";
    params = params + "&customer_id=<?php echo $appSession->getConfig()->getProperty("customer_id"); ?>";
    params = params + "&id=" + id;
    var _url = "<?php echo URL; ?>api/product/?" + params;

    loadPage('contentView', _url, function(status, message) {
      if (status == 0) {
        if (message == "OK") {
          document.location.reload();
        }
      } else {
        console.log(message);
      }

    }, true);

  }
  function checkOut() {
    

    var _url = "<?php echo URL; ?>api/product/?ac=checkOut";
    
    _url = _url + "&session_id=<?php echo $appSession->getConfig()->getProperty("session_id"); ?>";
    _url = _url + "&sale_id=<?php echo $sale_id; ?>";
    _url = _url + "&user_id=<?php echo $appSession->getConfig()->getProperty("user_id"); ?>";

    loadPage('contentView', _url, function(status, message) {
      if (status == 0) {
        if (message.indexOf("OK") != -1) {
			document.location.href = '<?php echo URL; ?>paid';
         
        } else {
          alert(message);
        }
      }
    }, true);
  }
</script>