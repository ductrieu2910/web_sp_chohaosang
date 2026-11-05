<?php
$fdate = date("Y-m-d")." 00:00:00";
$tdate = date("Y-m-d")." 23:59:59";
?>
<br>
<link href="<?php echo URL;?>assets/daterange/daterangepicker.css" rel="stylesheet" type="text/css" />
<script src="<?php echo URL;?>assets/daterange/moment.min.js"></script>
<script src="<?php echo URL;?>assets/daterange/daterangepicker.min.js"></script>
<div class="container">
	<br>
	<div class="row">
		<div class="quality-main about-qulity-main fade_down">
			<p class="quality"><?php echo $appSession->getLang()->find("Task");?></p>
		</div>
	</div>

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
				<button class="input-group-text" onclick="loadTask()">Search</button>
			</div>
		</div>
	</div>
	<br>
	<div class="row">
		<div class="col-12">
			<div id="pnTasks"></div>
		</div>
	</div>
</div>

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
        
		loadTask();
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
function loadTask()
{
	var search= document.getElementById('editsearch').value;
	
	var _url = '<?php echo URL;?>addons/task/list/?search=' + encodeURIComponent(search);
	_url = _url + "&status=" + encodeURIComponent(status);
	_url = _url + "&fdate=" + encodeURIComponent(fdate);
	_url = _url + "&tdate=" + encodeURIComponent(tdate);
	
	loadPage('pnTasks', _url, function(status, message)
	{
		if(status== 0)
		{
			
		}
		
	}, false);
}
function statusChange(theInput, id)
{
	var status_id = theInput.value;
	var _url = '<?php echo URL;?>addons/task/list/?ac=save&id=' + id;
	_url = _url + "&value=" + status_id;
	_url = _url + "&name=status_id";
	console.log(_url);
	loadPage('pnTasks', _url, function(status, message)
	{
		if(status== 0)
		{
			
		}
		
	}, true);
}
</script>