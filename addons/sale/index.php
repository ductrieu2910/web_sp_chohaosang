<?php
$fdate = "";
if(isset($_SESSION['order_fdate']))
{
	$fdate = $_SESSION['order_fdate'];
}
$tdate = "";
if(isset($_SESSION['order_tdate']))
{
	$tdate = $_SESSION['order_tdate'];
}
$status = "";
if(isset($_SESSION['order_status']))
{
	$status = $_SESSION['order_status'];
}

?>
<link href="<?php echo URL;?>assets/daterange/daterangepicker.css" rel="stylesheet" type="text/css" />
<script src="<?php echo URL;?>assets/daterange/moment.min.js"></script>
<script src="<?php echo URL;?>assets/daterange/daterangepicker.min.js"></script>
<br>
<div class="container">
	<div class="row">
		<div class="col-8">
			<div id="reportrange" style="cursor: pointer; width: 100%">
				<img src="<?php echo URL;?>assets/images/calendar.png" border="0" /></i>&nbsp;
				<span></span> <img src="<?php echo URL;?>assets/images/menu_down.png" border="0" />
			</div>
		</div>
		<div class="col-4">
			<div class="input-group mb-3">
				<input type="search" id="editsearch" class="form-control"/>
				<button class="input-group-text" onclick="loadOrders()">Search</button>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-12">
			<div id="pnOrders"></div>
		</div>
	</div>
</div>
<br>
<br>

<script>
 var fdate = "<?php echo $fdate;?>";
var tdate = "<?php echo $tdate;?>";
$( document ).ready(function() {
	var start = moment();
    var end = moment();

    function cb(start, end) {
		if(start != null)
		{
			$('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
			fdate = start.format('YYYY-MM-D') + ' 00:00:00.000';
			tdate = end.format('YYYY-MM-D')+ ' 23:59:59.999';
		}else{
			$('#reportrange span').html(null);
		}
        
		loadOrders();
    }

    $('#reportrange').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
		
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);
    cb(start, end);
});
function loadOrders()
{
	var search= document.getElementById('editsearch').value;
	
	var _url = '<?php echo URL;?>addons/sale/list/?search=' + encodeURIComponent(search);
	var cancel_order =0;
	
	var x= document.getElementsByName('status[]');
	var status = '';
	for(var i=0; i<x.length; i++)
	{
		if(x[i].checked)
		{
			if(status != ''){
				status = status + ",";
			}
			status = status + x[i].value;
		}
		
	}
	_url = _url + "&cancel_order=" + encodeURIComponent(cancel_order);
	_url = _url + "&status=" + encodeURIComponent(status);
	_url = _url + "&fdate=" + encodeURIComponent(fdate);
	_url = _url + "&tdate=" + encodeURIComponent(tdate);
	
	loadPage('pnOrders', _url, function(status, message)
	{
		if(status== 0)
		{
			
		}
		
	}, false);
}
function loadSale(id)
{
	var _url = '<?php echo URL;?>addons/sale/line/?sale_id=' + id;
	loadPage('pnOrders', _url, function(status, message)
	{
		if(status== 0)
		{
			
		}
		
	}, false);
}
function delOrder(id)
{
	var result = confirm("Want to delete");
	if (!result) {
		return;
	}
	var _url = '<?php echo URL;?>addons/action/?ac=delSale';
	_url = _url + '&id=' + encodeURIComponent(id);
	
	loadPage('contentView', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message == "OK")
			{
				
				loadOrders();
			}
			else{
				alert(message);
			}
		}
		
	}, true);
}
</script>