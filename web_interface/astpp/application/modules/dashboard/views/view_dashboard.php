<? extend('master.php') ?>
<? startblock('extra_head') ?>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/chart/highcharts.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/chart/exporting.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/chart/highcharts-3d.js"></script>
<style>
    .second{
        color: black;
        opacity:0.4;
        border: 0px;
        width: 100%; 
        padding:100px 0px 100px 0px;
        margin:5px;  
        display:relative;
        text-align:center;
        font-size:250%;
    }
    .back_strip
    {
   }

<!--Name:pooja
Date: 01-07-2016 -->
 .ball-clip-rotate>div
 {
     
display:inline-block;
    -webkit-animation:1s ease-in-out 0s normal none infinite running spin-rotate;
    animation:1s ease-in-out 0s normal none infinite running spin-rotate;
    background-color:#fff;
    width:42px;
    height:44px;
    border-radius:100%;
    margin:2px;
    -webkit-animation-fill-mode:both;
    animation-fill-mode:both;
    border:0px solid #fff;
    border-bottom-color:transparent;
    background:url('<?= base_url() ?>assets/images/loder-1.png')!important;
    
    
    }

@-webkit-keyframes spin-rotate
{
0%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}

    to
    {
    -webkit-transform:rotate(1turn);transform:rotate(1turn)
    }
}
@keyframes spin-rotate
{
0%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}
to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}
}
</style>
<script type="text/javascript">
    
    function build_recharge_graph(drop_val){
            var month_year=$("#month_year_dropdown").val();
            var items = month_year.split('#');  
        var year=items[1];
        var month=items[0];
        $.ajax({
            type:'POST',
            dataType: 'json',
            async: false ,
            url: "<?php echo base_url(); ?>dashboard/customerReport_call_statistics_with_profit/",
            data:{"month":month, "year":year ,"drop_val":drop_val},
            beforeSend: function(){
                $("#call-graph").append('<div class="loading col-md-offset-6"><div class="ball-clip-rotate"><div></div></div></div>');
                $("#call-graph").show();
            },
            complete: function(){
                $("#call-graph").empty();
                $("#call-graph").hide();
            },
            success: function(response_data) {
                $("#call-graph").hide();
                $('#call_graph_data').highcharts({
                    chart: {
                        zoomType: 'xy'
                    },
                    title: {
                        text: 'ABC'
                    },
                    xAxis: [{
                            categories:response_data.date
                        }],
                    yAxis: [{
                            min: 0,
                            title: {
                                    text: ' '
                            }
                        }, {
                            min: 0,
                            opposite: true, 
                            title: {
                                text: '<?php echo gettext("Profit per day"); ?>'
                            }
                        }],
                    tooltip: {
                        backgroundColor: '#FEFEC5',
                        borderColor: 'black',
                        borderRadius: 10,
                        borderWidth: 2,
                        formatter: function() {
                            var today_dropdown=$("#today_dropdown").val();
                            if(this.series.name == 'Total Calls'){
                                if(today_dropdown == 't_month'){
                                        return '<b>Total : </b>'+ this.y
                                        +'<br/><b>ACD : </b>'+ response_data.acd[this.x-1][1]
                                        +'<br/><b>MCD : </b>'+ response_data.mcd[this.x-1][1]
                                        +'<br/><b>ASR : </b>'+ response_data.asr[this.x-1][1];
                                }else{
                                        var day_str = new Date(year+"-"+month+"-"+this.x);
                                        var day_count = day_str.getDay();
                                        return '<b>Total : </b>'+ this.y
                                        +'<br/><b>ACD : </b>'+ response_data.acd[day_count-1][1]
                                        +'<br/><b>MCD : </b>'+ response_data.mcd[day_count-1][1]
                                        +'<br/><b>ASR : </b>'+ response_data.asr[day_count-1][1];
                                }
                            }else{
                                return this.series.name+': <b>'+this.y+'</b>';
                            }
                        }
                                  
                    },
                    legend: {
                        layout: 'horizontal',
                        align: 'center',
                        x: 0,
                        verticalAlign: 'top',
                        y: 0,
                        backgroundColor: '#EFEFEF'
                    },
                    series: [
                        {
                            name: '<?php echo gettext("Total Calls"); ?>',
                            type: 'column',
                            color:'#6E8CD7  ',
                            yAxis:0,
                            data: response_data.total
                        },
                        {
                            name: '<?php echo gettext("Answered Calls"); ?>',
                            type: 'spline',
                            color:'#34D3EB',
                            yAxis:0,
                            data: response_data.answered,
                            marker: {
                                enabled: true
                            },
                            dashStyle: 'shortdot'
                        },
                        {
                            name: '<?php echo gettext("Failed Calls"); ?>',
                            type: 'spline',
                            color:'#E94E02',
                            yAxis:0,
                            data: response_data.failed,
                            marker: {
                                enabled: true
                            },
                            dashStyle: 'shortdot'
                        },
                        {
                            name: '<?php echo gettext("Profit"); ?>',
                            type: 'spline',
                            yAxis:1,
                            color:'#008000',
                            data: response_data.profit,
                            marker: {
                                enabled: true
                            },
                            dashStyle: 'shortdot'
                        }
                    ]
                });
            }
        });
    }
    
    function get_account_count(drop_val,year,month){
            $.ajax({
            type:'POST',
            url: "<?php echo base_url(); ?>dashboard/account_count",
            dataType: 'JSON',
            data:{"drop_val":drop_val,"year":year,"month":month},
            success: function(response_data) {     
                $("#accounts_count").html(response_data.count);
            }
        });
    }
    
    function get_total_calls(drop_val,year,month){
            $.ajax({
            type:'POST',
            url: "<?php echo base_url(); ?>dashboard/call_count",
            dataType: 'JSON',
            data:{"drop_val":drop_val,"year":year,"month":month},
            success: function(response_data) {     
                $("#call_count").html(response_data.total_calls);
            }
        });
    }
    
    function get_orders_count(drop_val,year,month){
            $.ajax({
            type:'POST',
            url: "<?php echo base_url(); ?>dashboard/orders_count",
            dataType: 'JSON',
            data:{"drop_val":drop_val,"year":year,"month":month},
            success: function(response_data) {     
                $("#order_count").html(response_data.count);
            }
        });
    }
    
    function get_refill_value(drop_val,year,month){
            $.ajax({
            type:'POST',
            url: "<?php echo base_url(); ?>dashboard/getrefill_value",
            dataType: 'JSON',
            data:{"drop_val":drop_val,"year":year,"month":month},
            success: function(response_data) {     
                $("#refill_value").html(response_data.total_refill_amount);
            }
        });
    }
    
    function build_call_graph(url,drop_val){
            var month_year=$("#month_year_dropdown").val();
            var items = month_year.split('#');  
        var year=items[1];
        var month=items[0];
        get_account_count(drop_val,year,month);
                get_total_calls(drop_val,year,month);
                get_orders_count(drop_val,year,month);
                get_refill_value(drop_val,year,month);
                
        $.ajax({
            type:'POST',
            url: "<?php echo base_url(); ?>dashboard/customerReport_maximum_call"+url+"/",
            dataType: 'JSON',
            async: false ,
            data:{"year":year,"month":month,"drop_val":drop_val},
            beforeSend: function(){
                $("#call-count").append('<div class="loading col-md-offset-6"><div class="ball-clip-rotate"><div></div></div></div>');
                $("#call-count").show();
            },
            complete: function(){
                $("#call-count").empty();
                $("#call-count").hide();
            },
            success: function(response_data) {
           // alert(response_data);
                var radiobuttonvalue = $("input[name='calls_pie_chart']:checked").val();
                if(radiobuttonvalue == 'count'){
                    var graphby = "<?php echo gettext('By Calls'); ?>";
                }else{
                    var graphby = "<?php echo gettext('By Minutes'); ?>";
                }
                $("#bygraph").val(graphby);
                $("#call-count").hide();
                if(response_data == ''){
                    $("div.call_count_data").hide();            
                    $("div.not_data").addClass("second");
                    $("div.not_data").show();
                    $('div.not_data').html('<i class="fa fa-meh-o"></i> <?php echo gettext("No Records Found"); ?>');
                }
                else{
                    $("div.not_data").hide();
                    $("div.call_count_data").show();
                    $('#call_count_data').highcharts({
                        chart: {
                            type: 'pie',
                            options3d: {
                                enabled: true,
                                alpha: 45,
                                beta: 0,
                                depth: 25,
                                viewDistance: 25
                            }
                        },
                        title: {
                            text: ""
                        },
                        tooltip: {
                            backgroundColor: '#FEFEC5',
                            borderColor: 'black',
                            borderRadius: 10,
                            borderWidth: 2,
                            formatter: function() {
                                return this.point.name+': <b>'+this.y+'</b>';
                            }
                        
                        },
                        subtitle: {
                            text: ''
                        },
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                depth: 25,
                                dataLabels: {
                                    enabled: false
                                },
                                showInLegend: true
                            }
                        },
                        series: [{
                                name: '',
                                data: response_data
                            }]
                    });
                }
            }
        });
    }
    
    function build_country_graph(url,drop_val){
            var month_year=$("#month_year_dropdown").val();
            var items = month_year.split('#');  
        var year=items[1];
        var month=items[0];

            //  alert("<?php echo base_url(); ?>dashboard/customerReport_maximum_country"+url+"/");
        $.ajax({
            type:'POST',
            url: "<?php echo base_url(); ?>dashboard/customerReport_maximum_country"+url+"/",
            dataType: 'JSON',
            data:{"year":year,"month":month,"drop_val":drop_val},
            beforeSend: function(){
                $("#country-count").append('<div class="loading col-md-offset-6"><div class="ball-clip-rotate"><div></div></div></div>');
                $("#country-count").show();
            },
            complete: function(){
                $("#country-count").empty();
                $("#country-count").hide();
            },
            success: function(response_data) {
                var radiobuttonvaluecountry = $("input[name='country_pie_chart']:checked").val(); 
                if(radiobuttonvaluecountry == 'count'){
                    var graphby = "<?php echo gettext('By Calls'); ?>";
                }else{
                    var graphby = "<?php echo gettext('By Minutes'); ?>";
                }
                $("#bygraphcountry").val(graphby);
                $("#country-count").hide();
                if(response_data == ''){
                    $("div.country_count_data").hide();            
                    $("div.country_not_data").addClass("second");
                    $("div.country_not_data").show();
                    $('div.country_not_data').html('<i class="fa fa-meh-o"></i> <?php echo gettext("No Records Found"); ?>');
                }
                else{
                    $("div.country_not_data").hide();
                    $("div.country_count_data").show();
                    $('#country_count_data').highcharts({
                        chart: {
                            type: 'pie',
                            options3d: {
                                enabled: true,
                                alpha: 45,
                                beta: 0,
                                depth: 25,
                                viewDistance: 25
                            }
                        },
                        title: {
                            text: ""
                        },
                        tooltip: {
                            backgroundColor: '#FEFEC5',
                            borderColor: 'black',
                            borderRadius: 10,
                            borderWidth: 2,
                            formatter: function() {
                                return this.point.name+': <b>'+this.y+'</b>';
                            }
                        
                        },
                        subtitle: {
                            text: ''
                        },
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                depth: 25,
                                dataLabels: {
                                    enabled: false
                                },
                                showInLegend: true
                            }
                        },
                        series: [{
                                name: '',
                                data: response_data
                            }]
                    });
                }
            }
        });
    }
    
    function build_today_result(){
            $.ajax({
            type:'POST',
            url: "<?php echo base_url(); ?>dashboard/get_today_result",
            dataType: 'JSON',
            success: function(response_data) {     
                $("#today_refill_value").html(response_data.today_refill_amount);
                $("#today_order_count").html(response_data.today_order_count);
                $("#today_accounts_count").html(response_data.today_account_count);
                $("#today_call_count").html(response_data.today_total_calls);
            }
        });
    }
    
    $(document).ready(function() {
        <?php // ASTPPCOM-939 Start ?>
        $(".button_notification").click(function() {
            if(!$(this).hasClass('button_clicked')){
            $(this).addClass("button_clicked");
                var accountid = $(this).val();
                // alert(accountid)

                $.ajax({
                    type:'POST',
                    url: "<?php echo base_url(); ?>dashboard/send_notification/",
                    data : "accountid="+accountid,
                    success: function(response) {
                        $('.button_clicked').attr("disabled", true);
                        $("#toast-container").css("display","block");
                        setTimeout(function() { $('.button_notification').attr("disabled", false) }, 3000);
                        $(".toast-message").html("Notification Send Successfully");
                        $('.toast-top-right').delay(5000).fadeOut();
                        $(".button_notification").removeClass("button_clicked");
                    }
                });
            } 
        });
        <?php // END ?>

        $('.selectpicker').selectpicker();  
        var monthNames = [
                        "<?php echo gettext('January'); ?>",
                        "<?php echo gettext('February'); ?>",
                        "<?php echo gettext('March'); ?>",
                        "<?php echo gettext('April'); ?>",
                        "<?php echo gettext('May'); ?>",
                        "<?php echo gettext('June'); ?>",
                        "<?php echo gettext('July'); ?>",
                        "<?php echo gettext('August'); ?>",
                        "<?php echo gettext('September'); ?>",
                        "<?php echo gettext('October'); ?>",
                        "<?php echo gettext('November'); ?>",
                        "<?php echo gettext('December'); ?>"
            ];
        var month=$("#month_dropdown").val();
        $("#month_year_name").html(monthNames[month-1]);
        build_recharge_graph('t_week');
        build_call_graph('minutes','t_week');
        build_country_graph('minutes','t_week');
        
        build_today_result();

        $('input[name=calls_pie_chart]').change(function(){
            var radiobuttonvalue = $("input[name='calls_pie_chart']:checked").val();
            var drop_val=$("#today_dropdown").val();
            build_call_graph(radiobuttonvalue,drop_val);
        });
        $('input[name=country_pie_chart]').change(function(){
            var radiobuttonvaluecountry = $("input[name='country_pie_chart']:checked").val();
            var drop_val=$("#today_dropdown").val();
            build_country_graph(radiobuttonvaluecountry,drop_val);
        });
        $("#month_year_dropdown").change(function(){
            var month_year=$("#month_year_dropdown").val();
            var radiobuttonvalue = $("input[name='calls_pie_chart']:checked").val();
            var radiobuttonvaluecountry = $("input[name='country_pie_chart']:checked").val();
            $("#month_year_name").html(monthNames[month-1]);
            build_recharge_graph('t_month');
            build_country_graph(radiobuttonvaluecountry,'t_month');
            build_call_graph(radiobuttonvalue,'t_month');
            
        });
        
        $("#today_dropdown").change(function(){
            var drop_val=$("#today_dropdown").val();
            var radiobuttonvalue = $("input[name='calls_pie_chart']:checked").val();
            var radiobuttonvaluecountry = $("input[name='country_pie_chart']:checked").val();
            build_recharge_graph(drop_val);
            $("#month_year_name").html(monthNames[month-1]);
            build_country_graph(radiobuttonvaluecountry,drop_val);
            build_call_graph(radiobuttonvalue,drop_val);
            
        });
        
        
        $.ajax({
            type:'POST',
            url: "<?php echo base_url(); ?>dashboard/customerReport_calculation",
            dataType: 'JSON',
            success: function(response_data) {                        
                $("#asr_today").html(response_data.ASR);
                $("#asr_month").html(response_data.ASR_month);
                $("#acd_today").html(response_data.ACD);
                $("#acd_month").html(response_data.ACD_month);
                $("#mcd_today").html(response_data.mcd);
                $("#mcd_month").html(response_data.mcd_month);
                $("#total_calls_today").html(response_data.total_calls);
                $("#total_calls_month").html(response_data.total_calls_month);
                $("#debit_today").html(response_data.total_debit);
                $("#debit_month").html(response_data.total_debit_month);
                $("#cost_today").html(response_data.total_cost);
                $("#cost_month").html(response_data.total_cost_month);
                $("#profit_today").html(response_data.profit);
                $("#profit_month").html(response_data.profit_month);
            }
        });        
             
    });
    
</script> 
<? endblock() ?>
<?php startblock('page-title') ?>
    <?php echo $page_title; ?>
   
<?php endblock() ?>
<? startblock('content') ?>

<?php
//ASTPPCOM-990 START
function uptime()
{
  if(PHP_OS == "Linux") {
    $uptime = @file_get_contents( "/proc/uptime");
    if ($uptime !== false) {
      $uptime = explode(" ",$uptime);
      $uptime = $uptime[0];
      $days = explode(".",(($uptime % 31556926) / 86400));
      $hours = explode(".",((($uptime % 31556926) % 86400) / 3600));
      $minutes = explode(".",(((($uptime % 31556926) % 86400) % 3600) / 60));
      $time = ".";
      if ($minutes > 0)
        $time=$minutes[0]." mins".$time;
      if ($minutes > 0 && ($hours > 0 || $days > 0))
        $time = ", ".$time;
      if ($hours > 0)
        $time = $hours[0]." hours".$time;
      if ($hours > 0 && $days > 0)
        $time = ", ".$time;
      if ($days > 0)
        $time = $days[0]." days".$time;
    } else {
      $time = false;
    }
  } else {
    $time = false;
  }
  return $time;
}
function get_server_memory_usage()
{
    $free = shell_exec('free');
    $free = (string)trim($free);
    $free_arr = explode("\n", $free);
    $mem = explode(" ", $free_arr[1]);
    $mem = array_filter($mem);
    $mem = array_merge($mem);
    $memory_usage = $mem[2]/$mem[1]*100;
    return $memory_usage; 
}
function get_server_cpu_usage()
{
    $output = shell_exec('cat /proc/loadavg');
    $loadavg = substr($output,0,strpos($output," ")); 
    return $loadavg;
}
function byteFormat($bytes, $unit = "", $decimals = 2) 
{
 $units = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4, 
 'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8);
 
 $value = 0;
 if ($bytes > 0) {
 // Generate automatic prefix by bytes 
 // If wrong prefix given
 if (!array_key_exists($unit, $units)) {
 $pow = floor(log($bytes)/log(1024));
 $unit = array_search($pow, $units);
 }
 
 // Calculate byte value by prefix
 $value = ($bytes/pow(1024,floor($units[$unit])));
 }
 
 // If decimals is not numeric or decimals is less than 0 
 // then set default value
 if (!is_numeric($decimals) || $decimals < 0) {
 $decimals = 2;
 }
 
 // Format output
 return sprintf('%.' . $decimals . 'f '.$unit, $value);
}
function get_memory()
{
$memory=shell_exec("free -m");
$array_memory=explode("\n",$memory);
$mem=explode(":",$array_memory[1]);
$mem[1] = trim(preg_replace('/\s\s+/', ' ', $mem[1]));
$mem1=explode(" ",$mem[1]);
return $mem1;
}
function get_swap_memory()
{
$memory=shell_exec("free -m");
$array_memory=explode("\n",$memory);
$mem=explode(":",$array_memory[2]);
$mem[1] = trim(preg_replace('/\s\s+/', ' ', $mem[1]));
$mem1=explode(" ",$mem[1]);
return $mem1;
}
function get_harddisk()
{
$memory=shell_exec("df -h");
$array_memory=explode("\n",$memory);
$memory_used='';

$memory_used = array();
foreach($array_memory as $key => $val)
{
   $val = trim(preg_replace('/\s\s+/', ' ', $val));
   $mem1=explode(" ",$val);
   
   if($mem1[count($mem1)-1]=='/')
   {
      $memory_used['used'] = $mem1[count($mem1)-4];
      $memory_used['total'] = $mem1[count($mem1)-5];
      $memory_used['avail'] = $mem1[count($mem1)-3];
   }
}
return $memory_used;
}
function get_cpu_cores()
{
    $cores = shell_exec("nproc --all");
    return $cores;
}
function getOS()
{
    $os_platform = shell_exec("hostnamectl | grep 'Operating'");
    $array_memory=explode("\n",$os_platform);
    $os_platform =explode(":",$array_memory[0]);
    if(!empty($os_platform)){
    return $os_platform[1];
    }
    else
    {
    $os_platform = '';    
    return $os_platform;    
    }
}
function getArchitecture()
{
    $ar_platform = shell_exec("hostnamectl | grep 'Architecture'");
    $array_memory=explode("\n",$ar_platform);
    $ar_platform =explode(":",$array_memory[0]);
    if(!empty($ar_platform)){
    return $ar_platform[1];
    }
    else
    {
    $ar_platform = '';    
    return $ar_platform;    
    }
}
function getStatichost()
{
    $hostname_platform = shell_exec("hostnamectl | grep 'Static hostname'");
    $array_memory=explode("\n",$hostname_platform);
    $hostname_platform =explode(":",$array_memory[0]);
    if(!empty($hostname_platform)){
    return $hostname_platform[1];
    }
    else
    {
    $hostname_platform = '';    
    return $hostname_platform;    
    }
}
function get_kernel_version() {
    $kernel = explode(' ', file_get_contents('/proc/version')); //find by hostnamectl
    $kernel = $kernel[2];
    return $kernel;
}
//ASTPPCOM-990 END
//ASTPPCOM-989 START
function create_formatted_date($startdate,$enddate,$timezone,$timevisibly)
{
    $startdate = strtotime($startdate);
    $enddate = strtotime($enddate);

    $startonlydate = date('Y-m-d', $startdate); //check date
    $endonlydate = date('Y-m-d', $enddate); //check date

    if(strtotime($startonlydate) == strtotime($endonlydate))
    {
        if($timevisibly == 0)
        {
            $output = date('d M Y', $startdate);
        }
        else
        {
            $output = date('d M-Y', $startdate).' '.date('H:i A', $startdate).' - '.date('H:i A', $startdate)." [".$timezone."]";
        }
    }
    else
    {
        if($timevisibly == 0)
        {
            $output = date('d M Y', $startdate)." - ".date('d M Y', $enddate);
        }
        else
        {
            $output = date('d M-Y H:i A', $startdate).' - '.date('d M-Y H:i A', $enddate)." [".$timezone."]";
        }
    }
    return $output;
}
//ASTPPCOM-989 END
?>

<section class="slice p-0">
    <div class="w-section inverse p-0">
        <div class="">
                <div class="row">
                    <div class="col-sm-12 mb-3">
                            <div class="col-lg-9 col-md-8 p-0 float-left card border call_graph_box">
                            <h3 class="text-dark float-left col-sm-12 p-3">
                                  <div class="p-0 float-left">
                                     <div class="" style="margin-top:3px;">
                                        <i class="fa fa-bar-chart-o text-primary fa-fw"></i> <?php echo gettext("Call Stat"); ?>
                                     </div>
                                  </div>

                             <div class="float-right col-lg-8 col-md-9 p-0" id="floating-label">
                            
                            <div class="today_dd col-md-3 float-right pb-md-0">
                                <select id="today_dropdown" name='today' class="form-control selectpicker m-0" style="z-index:9;">   
                                        
                                    <option value="t_week"><?php echo gettext("This Week"); ?></option>
                                    <option value="t_month"><?php echo gettext("By Month"); ?></option>
                                </select>
                                
                            </div>
                            <div class="year_select d-none">
                            <div class="col-md-3 float-right pb-3 pr-md-0 pt-3 pt-md-0 pb-md-0">
                                        <select id="month_year_dropdown" name='year' class="form-control selectpicker m-0" style="z-index:9;">
                                        <?php
                                            for ($i =0; $i < 6; $i++) {
                                                                            echo '<option'.$selected.' value="'.date("m#Y", strtotime( date( 'Y-m-01' )." -$i months")).'">'.date("F Y", strtotime( date( 'Y-m-01' )." -$i months")).'</option>'."\n";
                                                                        }   
                                                                    ?>
                                        </select>
                                </div>
                            </div>
                             </div>
                            </h3>                               

                            <div id="call-graph"></div>
                            <div id='call_graph_data' class='call_graph_data p-0'></div>
                        </div>

                        <div class="float-left col-lg-3 col-md-4 px-sm-0 pl-lg-3 pt-4 pt-md-0 p-0">
                                <div id="month_total_info" class="col-12">
                                <div class="card p-4">
                                        <div class="col-md-12 float-left p-1 alert-dark">
                                            <div class="col-6 float-left"><?php echo gettext("Today"); ?></div>
                                            <div class="col-6 float-left text-right"><?php echo gettext("This Month"); ?></div>
                                        </div>
                                        <div class="col-md-12 float-left p-2 border border-primary">
                                            <div class="col-12 text-left badge text-primary"><?php echo gettext("ASR (%)"); ?></div>
                                            <div class="col-6 float-left" id="asr_today">0.00</div>
                                            <div class="col-6 float-left text-right" id="asr_month">0.00</div>
                                        </div>
                                        <div class="col-md-12 float-left p-2 border border-dark">
                                            <div class="col-12 text-left badge text-dark"><?php echo gettext("ACD"); ?></div>
                                            <div class="col-6 float-left" id="acd_today">0</div>
                                            <div class="col-6 float-left text-right" id="acd_month">0</div>
                                        </div>
                                        <div class="col-md-12 float-left p-2 border border-warning">
                                            <div class="col-12 text-left badge text-warning"><?php echo gettext("MCD"); ?></div>
                                            <div class="col-6 float-left" id="mcd_today">0.0000</div>
                                            <div class="col-6 float-left text-right" id="mcd_month">0.0000</div>
                                        </div>
                                        <div class="col-md-12 float-left p-2 border border-danger">
                                            <div class="col-12 text-left badge text-danger"><?php echo gettext("Total Calls"); ?></div>
                                            <div class="col-6 float-left" id="total_calls_today">0</div>
                                            <div class="col-6 float-left text-right" id="total_calls_month">0</div>
                                        </div>
                                        <div class="col-md-12 float-left p-2 border border-secondary">
                                            <div class="col-12 text-left badge text-secondary"><?php echo gettext("Debit"); ?></div>
                                            <div class="col-6 float-left" id="debit_today">0.0000 USD</div>
                                            <div class="col-6 float-left text-right" id="debit_month">0.0000 USD</div>
                                        </div>
                                        <div class="col-md-12 float-left p-2 border border-info">
                                            <div class="col-12 text-left badge text-info"><?php echo gettext("Cost"); ?></div>
                                            <div class="col-6 float-left" id="cost_today">0.0000 USD</div>
                                            <div class="col-6 float-left text-right" id="cost_month">0.0000 USD</div>
                                        </div>
                                        <div class="col-md-12 float-left p-2 border border-success">
                                            <div class="col-12 text-left badge text-success"><?php echo gettext("Profit"); ?></div>
                                            <div class="col-6 float-left" id="profit_today">0.0000 USD</div>
                                            <div class="col-6 float-left text-right" id="profit_month">0.0000 USD</div>
                                        </div>
                  
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard_values row mb-3">
                        <a href="<?php echo base_url(); ?>accounts/customer_list/" class="col-lg-3 col-md-6 col-sm-12 pt-2 pr-lg-2">
                            <div class="bg-primary card col-12 text-light">
                                <div class="alert-primary col-md-12 pt-2 px-0">
                                            <dl class="row m-0">
                                                <dt class="col-7"><?php echo gettext("Today"); ?></dt>
                                                <dd class="col-5 text-right" id="today_accounts_count">0</dd>
                                            </dl>
                                </div>
                                <div class="col-lg-8 col-7 float-left py-4">
                                    <div class="h1" id="accounts_count">0</div>
                                    <h3><?php echo gettext("New Accounts"); ?></h3>
                                </div>
                                <div class="col-lg-4 col-5 float-left py-4">
                                    <i class="fa fa-users fa-4x float-left"></i>
                                </div>
                            </div>
                        </a>
                        <a href="<?php echo base_url(); ?>orders/orders_list/" class="col-lg-3 col-md-6 col-sm-12 pt-2 pl-lg-2 pr-lg-2">
                            <div class="card col-12 text-light bg-success">
                            <div class="alert-success col-md-12 pt-2 px-0">
                                            <dl class="row m-0">
                                                <dt class="col-7"><?php echo gettext("Today"); ?></dt>
                                                <dd class="col-5 text-right" id="today_order_count">0</dd>
                                            </dl>
                                </div>
                                <div class="col-lg-8 col-7 float-left py-4">
                                    <div class="h1" id="order_count">0</div>
                                    <h3><?php echo gettext("Orders"); ?></h3>
                                </div>
                                <div class="col-lg-4 col-5 float-left py-4">
                                    <i class="fa fa-shopping-cart fa-4x float-left"></i>
                                </div>
                            </div>
                        </a>
                        <a href="<?php echo base_url(); ?>reports/refillreport/" class="col-lg-3 col-md-6 col-sm-12 pt-2 pr-lg-2 pl-lg-2">
                            <div class="card col-12 text-light bg-dark">
                            <div class="alert-dark col-md-12 pt-2 px-0">
                                            <dl class="row m-0">
                                                <dt class="col-6"><?php echo gettext("Today"); ?></dt>
                                                <dd class="col-6 text-right" id="today_refill_value">0</dd>
                                            </dl>
                                </div>
                                <div class="col-lg-8 col-7 float-left py-4">
                                    <div class="h1" id="refill_value">0</div>
                                    <h3><?php echo gettext("Refills"); ?> (<?php echo $currency;?>)</h3>
                                </div>
                                <div class="col-lg-4 col-5 float-left py-4">
                                    <i class="fa fa-credit-card fa-4x float-left"></i>
                                </div>
                            </div>
                        </a>
                        <a href="<?php echo base_url(); ?>reports/customerReport/" class="col-lg-3 col-md-6 col-sm-12 pt-2 pl-lg-2">
                            <div class="card col-12 text-light bg-danger">
                            <div class="alert-danger col-md-12 pt-2 px-0">
                                            <dl class="row m-0">
                                                <dt class="col-7"><?php echo gettext("Today"); ?></dt>
                                                <dd class="col-5 text-right" id="today_call_count">0</dd>
                                            </dl>
                                </div>
                                <div class="col-lg-8 col-7 float-left py-4">
                                    <div class="h1" id="call_count">0</div>
                                    <h3><?php echo gettext("Total Calls"); ?></h3>
                                </div>
                                <div class="col-lg-4 col-5 float-left py-4">
                                    <i class="fa fa-phone fa-4x float-left"></i>
                                </div>
                            </div>
                        </a>
                    </div>
            
            <!-- Kinjal Issue Number 671 Low balance report, Trunk activity report -->
            <?php   
                    $accountinfo = $this->session->userdata("accountinfo");
                    if($accountinfo['type'] == -1 || $accountinfo['type'] == 2){
                ?>
                  <div class="row equal-height">
                    <div class="col-lg-6 pr-lg-2 mb-3">
                        <?php //ASTPPENT-990 sanket start ?>
                        <div class="card mb-3 dashboard-block h-100">
                        <?php //ASTPPENT-990 sanket end ?>    
                                    <h3 class="text-dark p-3"><i class="fa fa-money text-primary fa-fw"></i> <?php echo gettext("Low Balance"); ?>
                                    <a href="<?php echo base_url();?>low_balance/low_balance_list/" class="float-right btn btn-secondary"><?php echo  gettext("View All"); ?></a>
                                    </h3>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                         <table class="table table-hover">
                                          <thead class="thead-light">
                                        <?php 
                                            $accountinfo = $this->session->userdata('accountinfo');
                                            $currency_id = $accountinfo['currency_id'];
                                            $currency = $this->common->get_field_name('currency', 'currency', $currency_id);
                                          ?>
                                            <tr>
                                              <th scope="col"><?php echo gettext("Account"); ?></th>
                                              <th scope="col"><?php echo gettext("Reseller"); ?></th>
                                              <th scope="col"><?php echo gettext("Current Balance"). "($currency)"; ; ?></th>
                                              <!-- ASTPPENTCOM-939 Start  -->
                                              <th scope="col"><?php echo gettext("Action"); ?></th>
                                              <!-- ASTPPENTCOM-939 END  -->
                                            </tr>
                                          </thead> 
                                          <tbody>
                                          <?php
                                                   if($accountinfo['type'] == '1'){
                                                       $reseller_id = $accountinfo['id'];
                                                   }else{
                                                       $reseller_id = "0";
                                                   }
                                               
                                                $where = "notify_flag = '" . 0 . "' AND deleted = '" . 0 .  " ' AND status = ' " . 0 . " ' AND (posttoexternal ='" . 0 . "' AND " . "balance <= notify_credit_limit"  . ") OR ( posttoexternal ='" . 1 . "' AND " . "credit_limit - balance <= notify_credit_limit"  . ")";

                                                   $entity_array = array (
                                                           "0",
                                                           "1",
                                                           "3" 
                                                   );
                                                   $limit = 5;
                                                   $this->db->where_in ( "type", $entity_array );
                                                   $this->db->limit($limit);
                                                   $query = $this->db_model->select("*", "accounts", $where, "id", "DESC");
                                                   if ($query->num_rows () > 0) {
                                                       $account_data = $query->result_array ();
                                                       foreach ( $account_data as $data_key => $accountinformation ) {
                
                                                            echo "<tr>";
                                                            echo "<td>".$this->common->get_field_name_coma_new ('first_name,last_name,number,company_name', 'accounts', $accountinformation ['id'] )."</td>";
                                                            echo "<td>".$this->common->reseller_select_value ('first_name,last_name,number,company_name', 'accounts', $accountinformation ['reseller_id']). "</td>";
                                                             echo "<td>".$this->common->convert_to_currency($select, $table, $accountinformation['balance'])."</td>";
                                                             // ASTPPCOM-939 Start
                                                             echo "<td>";
                                                    ?>
                                                    <button class="button_notification btn btn-secondary" value="<?php echo $accountinformation['id'];?>" type="button"><?php echo  gettext("Notify"); ?></button>
                                                        <?php  
                                                            echo "</td>";
                                                            echo "</tr>";
                                                            // END 
                                                       }

                                                   }
                                            ?>
                                          </tbody>
                                        </table>
                                     </div>
                                   </div>
                                </div>
                            </div>
                                    <?php } ?>
                            
                <?php  
                    $accountinfo = $this->session->userdata("accountinfo");
                    if($accountinfo['type'] == -1 || $accountinfo['type'] == 2 ){
                ?>
                <div class="col-lg-6 pl-lg-2 mb-3">
                    <?php //ASTPPENT-990 sanket start ?>
                    <div class="card mb-3 dashboard-block h-100">
                    <?php //ASTPPENT-990 sanket end ?>    
                                <h3 class="text-dark p-3"><i class="fa fa-phone text-primary fa-fw"></i> <?php echo gettext("Trunk Statistics"); ?>
                                <a href="<?php echo base_url();?>summary/provider/" class="float-right btn btn-secondary"><?php echo  gettext("View All"); ?></a>
                                </h3>
                                <div class="card-body">
                                    <div class="table-responsive">
                                    <table class="table table-hover">
                                      <thead class="thead-light">
                                        <tr>
                                          <th scope="col"><?php echo gettext("Trunk"); ?></th>
                                          <th scope="col"><?php echo gettext("Attempted Calls"); ?></th>
                                          <th scope="col"><?php echo gettext("Completed Calls"); ?></th>
                                          <th scope="col"><?php echo gettext("ASR"); ?></th>
                                        </tr>
                                      </thead> 
                                      <tbody>
                                      <?php
                                            $limit = 5;
                                            $table_name= "cdrs";
                                            $where1['provider_id >'] = 0;
                                            $where1['callstart >='] =$this->common->convert_GMT_new ( date('Y-m-d') . " 00:00:00");
                                            $where1['callstart <='] =$this->common->convert_GMT_new (date('Y-m-d') . " 23:59:59");
                                            $query = $this->db->select("trunk_id" . ",COUNT(*) AS attempts, AVG(billseconds) AS acd,MAX(billseconds) AS mcd,SUM(billseconds) AS duration,SUM(CASE WHEN calltype !='free' THEN billseconds ELSE 0 END) as billable,SUM(CASE WHEN billseconds > 0 THEN 1 ELSE 0 END) as completed,SUM(provider_call_cost) AS cost,",false);
                                            $this->db->from($table_name);
                                            $this->db->where($where1);
                                            $this->db->group_by('trunk_id');
                                            $this->db->order_by('callstart' , "DESC");
                                            $this->db->limit($limit);
                                            $result = $this->db->get(); 
                                            $account_data = $result->result_array ();
                                            foreach ( $account_data as $data_key => $val ) {
                                                $atmpt =  $val['attempts'];
                                                $cmplt = $val['completed'];
                                                $asr = ($atmpt > 0) ? (round(($cmplt / $atmpt) * 100, 2)) : '0.00';

                                                echo "<tr>";
                                                echo "<td>".$this->common->get_field_name ( 'name', 'trunks', $val ['trunk_id'] )."</td>";
                                                echo "<td>".$val['attempts']."</td>";
                                                echo "<td>".$val['completed']."</td>";
                                                echo "<td>".$asr."</td>";
                                                }
                                               
                                        ?>
                                      </tbody>
                                    </table>
                                 </div>
                                </div>
                               </div>
                             </div>
                        </div>
                    <?php }?>

        <!-- END -->
            <div class="row">
                <div class="col-lg-6 pr-lg-2">
                    <div class="card mb-3 dashboard-block">
                            <h3 class="text-dark p-3">
                                <i class="fa fa-users text-primary fa-fw"></i> <?php echo gettext("Top 10 Accounts"); ?>
                              <div class="dropdown float-right col-5 p-0">
                                <input type="text" class="border-primary col-8 float-right form-control text-center text-primary" readonly="true" name="bygraph" id="bygraph" value="<?php echo gettext("By Minutes"); ?>" style="background: transparent;font-size: 12px;">
                                <button type="button" class="btn btn-link dropdown-toggle position-relative float-right py-0" id="radioToggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                  <i class="fa fa-ellipsis-v"></i>
                                </button>
                                
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="radioToggle">
                                  <label class="dropdown-item">
                                    <input type="radio" name="calls_pie_chart" id="option2" value="count" class="ace" autocomplete="off"> <?php echo gettext("By Calls"); ?>
                                  </label>
                                  <label class="dropdown-item">
                                    <input type="radio" name="calls_pie_chart" id="option1" value="minutes" class="ace" autocomplete="off" checked> <?php echo gettext("By Minutes"); ?>
                                  </label>
                                </div>
                              </div>
                            </h3>
                        <div class="card-body">
                            <div id="call-count"></div>
                            <div id='call_count_data' class=' call_count_data col-md-12' style ='display:none'></div>  
                            <div id='not_data' class='col-md-12 not_data' style ='display:none'></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 pl-lg-2">
                    <div class="card mb-3 dashboard-block">
                            <h3 class="text-dark p-3">
                              <i class="fa fa-plane text-primary fa-fw"></i> <?php echo gettext("Top 10 Countries"); ?>
                              <div class="dropdown float-right col-5 p-0">
                                <input type="text" class="border-primary col-8 float-right form-control text-center text-primary" readonly="true" name="bygraphcountry" id="bygraphcountry" value="By Minutes" style="background: transparent;font-size: 12px;">
                                <button type="button" class="btn btn-link dropdown-toggle position-relative float-right py-0" id="radioToggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                  <i class="fa fa-ellipsis-v"></i>
                                </button>
                                
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="radioToggle">
                                  <label class="dropdown-item">
                                    <input type="radio" name="country_pie_chart" id="option22" value="count" class="ace" autocomplete="off"> <?php echo gettext("By Calls"); ?>
                                  </label>
                                  <label class="dropdown-item">
                                    <input type="radio" name="country_pie_chart" id="option11" value="minutes" class="ace" autocomplete="off" checked><?php echo gettext("By Minutes"); ?>
                                  </label>
                                </div>
                              </div>
                            </h3>

                        <div class="card-body">
                                <div id="country-count"></div>
                            <div id='country_count_data' class='col-md-12 country_count_data' style ='display:none'></div>
                            <div id='country_not_data' class='col-md-12 country_not_data' style ='display:none'></div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            if($this->session->userdata ( 'logintype' ) == 2 || $this->session->userdata ( 'logintype' ) == -1)
            {
            ?> 
            <div class="row event-info-box">
                <div class="col-lg-6 pr-lg-2">
                    <?php //ASTPPENT-990 sanket start ?>
                    <div class="card mb-3 dashboard-block events-block">
                    <?php //ASTPPENT-990 sanket end ?>    
                            <h3 class="text-dark p-3">
                                <?php //ASTPPCOM-1307_gautam_start ?>
                                <i class="fa fa-calendar text-primary fa-fw"></i> <?php echo gettext("Whats New"); ?>
                                <?php //ASTPPCOM-1307_gautam_end ?>                              
                            </h3>
                        <div class="card-body">
                            <div id="call-count"></div>
                            <?php
                            $feed_url = "https://newsinfo.astppbilling.org/posts.php";
                            $events = array();
                            if($feed_url!='')
                            {

                            // make request: TLS certificate verified (it was switched off, a man in the
                            // middle could inject script into the administrator's dashboard), short timeouts
                            $ch = curl_init();
                            // Will return the response, if false it print the response
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                            // Set the url
                            curl_setopt($ch, CURLOPT_URL, $feed_url);
                            // Execute
                            $output = curl_exec($ch);
                            curl_close($ch);
                            $xml_snippet = is_string($output) ? @simplexml_load_string( $output,null, LIBXML_NOCDATA) : false;
                            if ($xml_snippet !== false && isset($xml_snippet->channel->item)) {
                                $json = json_decode( json_encode( $xml_snippet ) );
                                $events = is_array($json->channel->item) ? $json->channel->item : array($json->channel->item);
                            }
                            }                     
                            ?>
                            <div class="rss-widget">
                                <?php
                                if(count($events) > 0)
                                {
                                ?>
                                <ul class="p-0">
                                    <?php
                                    foreach($events as $row)
                                    {
                                    date_default_timezone_set('Asia/Kolkata');
                                    // values from the feed: escaped, links and images only http(s)
                                    $link = preg_match('~^https?://~i', (string) $row->link) ? htmlspecialchars((string) $row->link, ENT_QUOTES, 'UTF-8') : '#';
                                    $imageurl = preg_match('~^https://~i', (string) $row->imageurl) ? htmlspecialchars((string) $row->imageurl, ENT_QUOTES, 'UTF-8') : '';
                                    $title = htmlspecialchars((string) $row->title, ENT_QUOTES, 'UTF-8');
                                    $description = trim(strip_tags(str_replace("<p></p>","", (string) $row->description)));

                                    $d1 = new DateTime($row->visDate);
                                    $d2 = new DateTime('now');
                                    $status = $row->status;

                                    if($status == 1)
                                    {
                                    //date info
                                    $startdate = $row->startDate;
                                    $enddate = $row->endDate; 
                                    $timezone = $row->timezone;
                                    $timevisibly = $row->timevisibly;
                                    $category = $row->category;                                   

                                    if($d1 > $d2 ){
                                    ?>
                                    <li class="event-info">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <?php
                                                if($imageurl != ''): ?>
                                                <a class="rsswidget" href="<?php echo $link; ?>" target="_blank" rel="noopener noreferrer"><img class="img-responsive" src="<?php echo $imageurl; ?>" /></a>
                                                <?php else: ?>
                                                <a class="rsswidget" href="<?php echo $link; ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-calendar"></i></a>    
                                                <?php endif; ?>    
                                            </div>
                                            <div class="col-md-6">
                                                <h5><a class="rsswidget_link" title="<?php echo $title; ?>" href="<?php echo $link; ?>" target="_blank" rel="noopener noreferrer"><?php echo $title; ?></a></h5>
                                                <div class="description" title="<?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(mb_strimwidth($description,"0","30", "..."), ENT_QUOTES, 'UTF-8'); ?></div>                
                                                </div>
                                            <div class="col-md-4">
                                                <?php
                                                if($category == 2) { ?>
                                                <div class="date-info">
                                                    <span class="font-12"><?php echo htmlspecialchars(create_formatted_date($startdate,$enddate,$timezone,$timevisibly), ENT_QUOTES, 'UTF-8'); ?></span>
                                                </div>
                                                <?php } ?>
                                                <span class="badge badge-primary float-left mt-1"><?php if($row->eventtype == 0 ){ echo "Offline Event"; } else { echo "Online Event"; }  ?></span>
                                            </div>
                                        </div>
                                        
                                    </li>
                                    <?php } } } ?>
                                </ul>
                                <?php
                                }
                                ?>
                            </div>
                            <!-- <div id='not_data' class='col-md-12 not_data' style ='display:none'></div> -->
                        </div>
                        <div class="card-footer">
                            <?php //ASTPPCOM-1307_gautam_start ?>
                            <a href="https://forum.astppbilling.org/login" target="_blank"><?php echo gettext('Forum');?> <i class="fa fa-external-link" aria-hidden="true"></i></a>
                            |

                            <a href="https://jira.astppbilling.org/projects/ASTPPCOM/issues" target="_blank"><?php echo gettext('Report an Issue');?> <i class="fa fa-external-link" aria-hidden="true"></i></a>
                            |

                            <a href="https://www.astppbilling.org/our-services/" target="_blank"><?php echo gettext('Professional Services');?> <i class="fa fa-external-link" aria-hidden="true"></i></a>
                            |

                            <a href="https://www.astppbilling.org/donate-to-astpp/" target="_blank"><?php echo gettext('Donate Now');?> <i class="fa fa-external-link" aria-hidden="true"></i></span></a>
                            <?php //ASTPPCOM-1307_gautam_end ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 pl-lg-2">
                <div class="row">    
                <div class="col-lg-6 pr-lg-2">
                    <div class="card mb-3 dashboard-block">
                            <h3 class="text-dark p-3">
                                <i class="fa fa-clock-o text-primary fa-fw"></i> <?php echo gettext("Up Time"); ?>                              
                            </h3>
                        <div class="card-body">
                            <?php echo uptime(); ?>
                        </div>                        
                    </div>
                </div>
                 <div class="col-lg-6 pl-lg-2">
                     <div class="card mb-3 dashboard-block">
                            <h3 class="text-dark p-3">
                                <i class="fa fa-tasks text-primary fa-fw"></i> <?php echo gettext("CPU Cores"); ?>                              
                            </h3>
                        <div class="card-body">
                            <?php echo get_cpu_cores(); ?>
                        </div>
                      </div>     
                </div>
                <div class="col-lg-6 pr-lg-2">
                    <div class="card mb-3 dashboard-block">
                            <h3 class="text-dark p-3">
                                <i class="fa fa-life-ring text-primary fa-fw"></i> <?php echo gettext("Hard Disk Usage"); ?>                              
                            </h3>
                        <div class="card-body">
                            <table class="table table-hover">
                                      <thead class="thead-light">
                                        <tr>
                                          <?php //ASTPPCOM-1307_gautam_start ?>  
                                          <th scope="col"><?php echo gettext('Total');?></th>
                                          <th scope="col"><?php echo gettext('Used');?></th>
                                          <th scope="col"><?php echo gettext('Available');?></th>
                                          <?php //ASTPPCOM-1307_gautam_end ?>
                                        </tr>
                                      </thead> 
                                      <tbody>
                                        <?php $get_details = get_harddisk(); 
                                        if(!empty($get_details))
                                        {
                                        ?>
                                        <tr>
                                          <td scope="col"><?php echo $get_details['total']; ?></td>
                                          <td scope="col"><?php echo $get_details['used']; ?></td>
                                          <td scope="col"><?php echo $get_details['avail']; ?></td>
                                        </tr>
                                        <?php } ?>                            
                                      </tbody>
                            </table>                            
                        </div>                        
                    </div>
                </div>
                <div class="col-lg-6 pl-lg-2">
                    <?php //ASTPPENT-990 sanket start ?>
                    <div class="card mb-3 dashboard-block specs-info">
                    <?php //ASTPPENT-990 sanket end ?>    
                            <h3 class="text-dark p-3">
                                <i class="fa fa-desktop text-primary fa-fw"></i> <?php echo gettext("Operating System"); ?>                              
                            </h3>
                        <div class="card-body">
                            <ul class="p-0 os-info">
                                <li>
                                    <?php //ASTPPCOM-1307_gautam_start ?>
                                    <span class="type-os"><?php echo gettext('OS');?>: </span>
                                    <span><?php echo getOS();?></span>
                                </li>
                                <li>
                                    <span class="type-os"><?php echo gettext('Architecture');?>: </span>
                                    <span><?php echo getArchitecture();?></span>
                                </li>
                                <li>
                                    <span class="type-os"><?php echo gettext('Static Hostname');?>: </span>
                                    <span><?php echo getStatichost();?></span>
                                    <?php //ASTPPCOM-1307_gautam_end ?>
                                </li>
                            </ul>
                        </div>
                    </div>       
                </div>
                <div class="col-lg-6 pr-lg-2">
                     <div class="card mb-3 dashboard-block">
                            <h3 class="text-dark p-3">
                                <i class="fa fa-tachometer text-primary fa-fw"></i> <?php echo gettext("CPU Usage"); ?>                              
                            </h3>
                        <div class="card-body">
                            <?php echo get_server_cpu_usage();?>
                        </div>
                      </div>     
                </div>
                <div class="col-lg-6 pl-lg-2">
                     <div class="card mb-3 dashboard-block">
                            <h3 class="text-dark p-3">
                                <i class="fa fa-shield text-primary fa-fw"></i> <?php echo gettext("Kernal Version"); ?>                              
                            </h3>
                        <div class="card-body">
                            <?php echo get_kernel_version();?>
                        </div>
                      </div>     
                </div>
                </div>
                </div>      
            
            </div>
            <div class="row event-info-box">
                <div class="col-lg-12">
                    <div class="card mb-3 dashboard-block">
                            <h3 class="text-dark p-3">
                                <i class="fa fa-server text-primary fa-fw"></i> <?php echo gettext("Memory Usage"); ?>                              
                            </h3>
                        <div class="card-body">
                            <div class="table-responsive">
                                <?php $memory_info = get_memory(); $swap_info = get_swap_memory(); ?>
                                    <table class="table table-hover">
                                      <thead class="thead-light">
                                        <tr>
                                          <?php //ASTPPCOM-1307_gautam_start ?>  
                                          <th scope="col"></th>
                                          <th scope="col"><?php echo gettext('Total');?></th>
                                          <th scope="col"><?php echo gettext('Used');?></th>
                                          <th scope="col"><?php echo gettext('Free');?></th>
                                          <th scope="col"><?php echo gettext('Shared');?></th>
                                          <th scope="col"><?php echo gettext('Cache');?></th>
                                          <th scope="col"><?php echo gettext('Available');?></th>
                                          <?php //ASTPPCOM-1307_gautam_end ?>
                                        </tr>
                                      </thead> 
                                      <tbody>
                                        <tr>
                                          <?php //ASTPPCOM-1307_gautam_start ?>  
                                          <td scope="col"><?php echo gettext('Memory');?></td>
                                          <?php //ASTPPCOM-1307_gautam_end ?>
                                          <td scope="col"><?php if($memory_info[0]!=''){ echo $memory_info[0].'MB'; } ?></td>
                                          <td scope="col"><?php if($memory_info[1]!=''){ echo $memory_info[1].'MB';} ?></td>
                                          <td scope="col"><?php if($memory_info[2]!=''){ echo $memory_info[2].'MB'; } ?></td>
                                          <td scope="col"><?php if($memory_info[3]!=''){ echo $memory_info[3].'MB'; } ?></td>
                                          <td scope="col"><?php if($memory_info[4]!=''){ echo $memory_info[4].'MB'; } ?></td>
                                          <td scope="col"><?php if($memory_info[5]!=''){ echo $memory_info[5].'MB'; }?></td>
                                        </tr>
                                        <tr>
                                          <?php //ASTPPCOM-1307_gautam_start ?>  
                                          <td scope="col"><?php echo gettext('Swap');?></td>
                                          <?php //ASTPPCOM-1307_gautam_end ?>
                                          <td scope="col"><?php if($swap_info[0]!=''){ echo $swap_info[0].'MB'; } ?></td>
                                          <td scope="col"><?php if($swap_info[1]!=''){ echo $swap_info[1].'MB'; } ?></td>
                                          <td scope="col"><?php if($swap_info[2]!=''){ echo $swap_info[2].'MB'; } ?></td>
                                          <td scope="col"><?php if($swap_info[3]!=''){ echo $swap_info[3].'MB'; } ?></td>
                                          <td scope="col"><?php if($swap_info[4]!=''){ echo $swap_info[4].'MB'; } ?></td>
                                          <td scope="col"><?php if($swap_info[5]!=''){ echo $swap_info[5].'MB'; } ?></td>
                                        </tr>
                                      </tbody>
                                    </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
</section>
<script>
    $(document).ready(function(){
        $('#today_dropdown').on('change', function() {
          if ( this.value == 't_month')
          {
            $(".year_select").removeClass("d-none");
          }
          else
          {
            $(".year_select").addClass("d-none");
          }
        });
    });
</script>
<? endblock() ?>
<? startblock('sidebar') ?>
<? endblock() ?>
<? end_extend() ?> 