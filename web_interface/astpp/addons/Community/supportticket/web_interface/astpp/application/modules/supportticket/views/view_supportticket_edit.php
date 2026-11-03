<? extend('master.php') ?>
<? startblock('extra_head') ?>

<!--

//~ change by bansi faldu
//~ issue:#94


ASTPP  3.0 
For Email Template Changes
-->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/ck/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/ck/ckfinder/ckfinder.js"></script>

<!-- <script type="text/javascript" src="<?php // echo base_url(); ?>assets/tinymce/tinymce.min.js"> -->

</script>
<script type="text/javascript">

// tinymce.init({
//   mode : "specific_textareas",
//   editor_selector: 'Emailtemplate',
//   height: 150,
//   width: 700,
//   theme: 'modern',
//   plugins: [
//     'advlist autolink lists link image charmap print preview hr anchor pagebreak',
//     'searchreplace wordcount visualblocks visualchars code fullscreen',
//     'insertdatetime media nonbreaking save table contextmenu directionality',
//     'emoticons template paste textcolor colorpicker textpattern imagetools'
//   ],
//   toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
//   toolbar2: 'print preview media | forecolor backcolor emoticons',
//   image_advtab: true,
//   templates: [
//     { title: 'Test template 1', content: 'Test 1' },
//     { title: 'Test template 2', content: 'Test 2' }
//   ],
//   content_css: [
//     '<?php // echo base_url(); ?>assets/css/tinymce_fast_font.css',
//     '<?php // echo base_url(); ?>assets/css/tinymce_codepen_min.css'
//   ],
  
//    setup: function (editor) {
//         editor.on('change', function () {
//             editor.save();
//         });
//     }
  
//  });
</script>

<!--***************************************************************-->
<script>
$ (document).ready(function(){
    CKEDITOR.replace('template1');
});
</script>
<script>
	$(document).ready(function(){
		$('.custom-file-input').change(function() {
		  var file = $('.custom-file-input')[0].files[0].name;
		  $('#label_0').html(file);
		});
		$("#welcomeDiv button").on("click",function(){
			$("#file ~ .custom-file-label").text("");
			document.getElementById("file").value = null;
		});
	});
	function getfilename(cnt){
		var file = document.getElementById('file['+cnt+']').files[0].name;
		$('#label_'+cnt).html(file);
		uploadfile(cnt);
	}
	

</script>

	<script type="text/javascript" src="<?php echo base_url(); ?>assets/markup/markitup/sets/default/set.js"></script>
<script type="text/javascript">
$(function() {
	$('#template').markItUp(mySettings);

});
</script>

<script type="text/javascript">
  jQuery(document).ready(function(){
    jQuery('#myform').submit(function(){
    
   jQuery("input[type=file]").bind(function(){
     jQuery(this).rules("add", {
       size: '10000',
       messages: {
         accept :"Only jpeg, jpg or png images"
       }
     });
   });
});
  });

    
function addFile(){
var file_name = document.getElementById("file").value;
var file_size=0;
$('.fileupload').each(function() { 
	file_size=file_size+(this.files[0].size/1048576);
	});
if(file_size > 20)
{
	alert("Attachment size can not more than 20 MB.");
}
else{
if(file_name == ''){
	alert("Please select Attach file");

}
else{
  document.getElementById('welcomeDiv').style.display = "none";
  var cnt = $('#mytab').find('input:file').length;
	if(cnt == 4){
	alert('You have upload maximum four file in Attachment');
	}
        if (cnt < 4){

var root=document.getElementById('mytab').getElementsByTagName('tr')[0].parentNode;
var oR = cE('tr');var oC = cE('td');var oI = cE('input'); var oS=cE('span');var oA=cE('p');oL=cE('p');nR=cE('tr');nC=cE('td');
var attachLabel = cE('label'); var attachDiv = cE('div');var attachBtnDivRemove = cE('div');var attachRowDiv = cE('div');
cA(oC,'class','col-12');cA(oR,'class','col-12');cA(attachRowDiv,'class','col-12 mt-2');cA(attachDiv,'class','col-md-6 float-left');cA(attachLabel,'class','custom-file-label btn-primary btn-file text-left');cA(attachLabel,'id','label_'+cnt);cA(attachLabel,'for','file['+cnt+']');cA(oI,'type','file');cA(oI,'name','file[]');cA(oI,'id','file['+cnt+']');cA(oI,'class','p-0 h-100 custom-file-input form-control col-md-12 fileupload');cA(attachBtnDivRemove,'class','col-md-6 float-left py-2 px-2');cA(oS,'class','btn btn-danger mx-2');cA(oI,'onchange','getfilename('+cnt+')');
cA(oA,'style','color:red;display:none;');cA(oA,'id','attach_file_'+cnt);
oS.style.cursor='pointer';
        }   
oS.onclick=function(){
	if(cnt == 1){
	   document.getElementById('welcomeDiv').style.display = "block";
	}
	
	$("#attach_file_"+cnt).hide();
	document.getElementById("attach_file_"+cnt).innerHTML="";
		
  this.parentNode.parentNode.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode.parentNode.parentNode);
 
}
oS.appendChild(document.createTextNode('Remove'));
oC.appendChild(attachRowDiv);attachRowDiv.appendChild(attachDiv);attachDiv.appendChild(oI);attachDiv.appendChild(attachLabel);attachRowDiv.appendChild(attachBtnDivRemove);attachBtnDivRemove.appendChild(oS);oR.appendChild(oC);root.appendChild(oR);
attachRowDiv.appendChild(oA);
}
}
}
function cE(el){
this.obj =document.createElement(el);
return this.obj

}
function cA(obj,att,val){
obj.setAttribute(att,val);
return this.obj
}
function uploadfile(cnt){
 
	//alert(cnt);
 
 $("#attach_file").hide();
 $("#attach_file_"+cnt).hide();
var file_size=0;
$('.fileupload').each(function() {
	
	var ext=this.files[0].name.split(".");
	ext=ext[ext.length-1].toLowerCase();
	var arrayExtensions = ["jpg" , "jpeg", "png", "pdg" , "xlsx" , "doc", "docx", "img", "txt"];
 
    if (arrayExtensions.lastIndexOf(ext) == -1) {
        
       var attach_file="Only allows file types of jpg, jpeg, png, pdf, xlsx, txt, docx and doc format.";
        
        if(cnt==undefined){
			$("#attach_file").show();
			document.getElementById("attach_file").innerHTML = attach_file;
			$("#file").val("");
		}else{
			$("#attach_file_"+cnt).show();
			document.getElementById("attach_file_"+cnt).innerHTML = attach_file;
			document.getElementById("file["+cnt+"]").value="";
			
			
		}
        
        

//document.getElementById("attach_file_"+cnt).innerHTML = attach_file; 
        
    }
	file_size=file_size+(this.files[0].size/1048576);
	
	});
if(file_size > 20)
{
	alert("Attachment size can not more than 20 MB.");
}
}


</script>
<script type="Text/JavaScript">
    $(document).ready(function(){
  $('#subject').change(function(){
    $('#une').text('');
    return false;
  });
  $('#template1').change(function(){
    $('#template2').text('');
    return false;
  });
  $("#myfile1").change(function(){
  $('#uneifer').text('');
    return false;
 });
  $("#myfile2").change(function(){
    $('#uneifera').text('');
    return false;
  });
  $("#template").change(function(){
    $('#body').text('');
    return false;
  });
  $("#to").change(function(){
    $('#to').text('');
    return false;
  });
  $("#from").change(function(){
    $('#from').text('');
    return false;
  });
});
    
function regvalidate()

{   
   
 
if((document.myform.subject.value==""))
 {
  document.getElementById('une').innerHTML = "Please Enter subject";
  myform.subject.focus();
  return(false);
 }
 
 
 if((document.myform.template1.value==""))
 {
   document.getElementById('template2').innerHTML = "Please Enter Reply";
   myform.template1.focus();
   return(false);
 
} 
 if((document.myform.to.value==""))
 {
  document.getElementById('to').innerHTML = "Please Enter Atleast One Value";
  myform.to.focus();
  return(false);
 }
 if((document.myform.from.value==""))
 {
  document.getElementById('from').innerHTML = "Please Enter Atleast One Value";
  myform.from.focus();
  return(false);
 }
 var file_size=0;
 $('.fileupload').each(function() { 
	file_size=file_size+(this.files[0].size/1048576);
 });
 if(file_size > 20)
 {
	alert("Attachment size can not more than 20 MB.");
	return false;
 }
 var usermessage = document.forms["myform"]["template"].value;
    if(usermessage === '')
    {
       document.getElementById('body').innerHTML = "Please Enter Message";
       document.getElementById('template').focus();
       return false;
    }
    else
    {
        return true;
    }
 }
</script>
<script>
function showDiv() {
 document.getElementById('location').style.display = "none";

 // var id = document.getElementById("file").value;
   document.getElementById('welcomeDiv').style.display = "block";

}
</script>
<script>
function here_hide() {
 // var id = document.getElementById("file").value;
   document.getElementById('welcomeDiv').style.display = "none";

}
</script>
<style>
#mceu_22  {
display :none;
}
#mceu_38-body  {
display :none;
}
#mceu_42-body{
display :none;
}
.card-block table:nth-child(odd) {
    background: #e3f1f2;
    border:1px solid #b7e2db;
     box-shadow: 0 0 10px #b7e2db; 
    
}
.card-block table:nth-child(even) {
    background: #efe6e6;
     border:1px solid #dbc0c0;
      box-shadow: 0 0 10px #dbc0c0;
}
.card-block table:nth-child(even) tbody tr td {
    background: #efe6e6;
     border:1px solid #dbc0c0;
      
}
.card-block table:nth-child(odd) tbody tr td {
    background: #e3f1f2;
    border:1px solid #b7e2db;
    
}
.table td {
    vertical-align: top;
}
p {
    color: black;
}
</style>
<? endblock() ?>
<? startblock('page-title') ?><?= $page_title; ?>

<? endblock() ?>
<? startblock('content') ?>


<div class="">
	<section class="slice color-three">
		<div class="w-section inverse pb-4">
			<div class="content">
				<div class="col-md-12 p-0">
					<? if(isset($error) && !empty($error)) {
						echo "<span class='row alert alert-danger m-2'>".$error."</span>";
					}?>

					<div class="pop_md col-12">
						<form method="post" action="<?= base_url()?>supportticket/supportticket_details_save/" enctype="multipart/form-data" name="myform" id="myform" onsubmit="return(regvalidate())">
							<div class="col-md-12 col-sm-6 mx-auto card p-0" id="floating-label">
								<h3 class="bg-secondary text-light p-3 rounded-top">Edit Ticket</h3>
								<input type="hidden" name="id" size="80" value="<?php echo $support_ticket['id']; ?>"/>
								<input type='hidden' value="<?php echo $support_ticket['subject']; ?>" name='subject'>
								<input type='hidden' value="<?php echo $support_ticket['priority']; ?>" name='priority'>

								<div style="margin-left:12%;">  
									<?php $system_config = common_model::$global_config['system_config'];
									$ticket = $system_config['ticket_digits'];  ?>

								</div>
								<div style="margin-left:225px;">
									<font color='red'> <DIV id="from"> </DIV> </font>
								</div>   
								<div>
									<div>
										<div class="col-md-12">
											<div class="row pt-4">
												<div class="col-md-4 h-100">
													<div class="row">
														<div class="col-2 float-left"><label class="control-label"><?php echo gettext("Subject"); ?>:&nbsp;</label></h5></div>
														<div class="col p-0 float-left"><span><?php echo $support_ticket['subject']; ?></span></div>
													</div>
												</div> 

												<?php 	if ($this->session->userdata('logintype') == '-1' || $this->session->userdata('logintype') == '2') {
													?> 
													
													<?php 	 	        $account_data = $this->session->userdata("accountinfo"); 
													$login_type=$account_data['type'];


													?>

													<div class="col-md-4 h-100">				


														<h5 class="float-right">  <?php if(astpp_eq0($support_ticket['priority'])){echo '<span class="btn badge-danger p-2">'.gettext("High").'</span>';}elseif($support_ticket['priority'] ==1){echo '<span class="btn badge-warning">'.gettext("Normal").'</span>';}else{ echo '<span class="btn badge-success p-2" >'.gettext("Low").'</span>';} ?> </h5> 

													</div>

													<div class="col-md-4 h-100">
													<select class='selectpicker form-control form-control-lg' data-live-search="true" name="ticket_type">

															<!-- Manish Issue 694 -->
															<?php if(astpp_eq0($support_ticket['ticket_type']) || $support_ticket['ticket_type'] == 1 || $support_ticket['ticket_type'] == 2 || $support_ticket['ticket_type'] == 3 || $support_ticket['ticket_type'] == 4 || $support_ticket['ticket_type'] == 5){?>
																	<option value="1" <?php if($support_ticket['ticket_type'] == 1){ echo "selected" ;} ?>><?php echo gettext("Answerd"); ?></option>
															<?php }?>
															<option value="0"><?php echo gettext("Open"); ?></option>
															<option value="2"><?php echo gettext("Customer-Reply"); ?></option>
															<option value="3"><?php echo gettext("On-hold"); ?></option>
															<option value="4"><?php echo gettext("Progress"); ?></option>
															<option value="5"><?php echo gettext("Close"); ?></option>
															<!-- End -->

														</select>

														<?php }else{
															if($ticket_lable== 'Answered'){ ?>
															<input type="hidden" name="ticket_type" value="1">	
															<?php	}else{ ?>
															<input type="hidden" name="ticket_type" value="2">	
															<?php	}
															?>

															<?php } ?>
														</div> 	
													

													<div class="col-md-12 form-group h-auto">
														<label class="control-label p-0"><?php echo gettext("Reply"); ?></label>

														<div class="col-12 p-0">
															<textarea input name='template1' id='template1' size = 0 class = "Emailtemplate ckeditor" value="" ></textarea>
														</div>
<!-- 														<label class="col-form-label col-sm-2 col-sm-12"> </label>
														<div class="col-sm-10 col-sm-12" style="margin-top:7px;margin-left:134px;">

															<font color='red'> <DIV id="template2"> </DIV> </font>
														</div>	
 -->													</div>

													<!-- <div class="col-md-12">
														<label class="col-md-2 col-sm-12"></label>
														<div class="col-sm-10 col-sm-12">

															<font color='red'> <DIV id="template2"> </DIV> </font>
														</div>
													</div>	

													<div class="col-md-12 no-padding error_div"> 
														<div class="col-md-2"> </div>
													</div> -->
													<input type="hidden" name="temp" value=""> 
												</div>
												</div>
											</div>


												<!-- <label class="col-md-1 col-sm-12 no-padding" style="margin-right:0px" >Attach files </label> -->
												<div class="col-md-12 mb-4">
													<table id="mytab" class="col-12">
														<tbody class="col-12">
														 <tr class="col-12">
															<td bgcolor="#FFFFFF" class="col-12">
															  <div class="col-12 mt-4">
																<div class="col-md-6 float-left" data-ripple="">
																	<input type="file" name="file[]"  id="file" class="text field large upload_input fileupload custom-file-input"  style="" onclick="showDiv()" onchange='uploadfile()'>
																	<label class="custom-file-label btn-primary btn-file text-left" id="label_0" for="file"> </label>
																	<input type="hidden" name="add_new" id="add_new" value="0">            
																 </div>
																 
																 <div class="col-md-6 float-left py-2 px-2">
																	<span id="welcomeDiv"  style="display:none;" class="answer_list" onclick="here_hide();">
																		<button type="button" title="Remove" onClick="document.getElementsById('file').value=''" class="btn btn-danger " style="cursor: pointer;"><?php echo gettext("Remove"); ?></button>
																	</span>
																 </div>
																 <div class="col-md-12">		
																	<div style="color:red;" id="attach_file"></div> 

																	<div style="color:red;" id="location"></div> 
																</div>	
															 </td>
														</tr>
														</tbody>
													</table>
													<div>
														<span onclick="addFile()" style="cursor:pointer;" class='btn alert-success my-2'><i class="fa fa-plus"></i> <?php echo gettext("Attach more files"); ?></span> 
													</div>
												</div>  




								<?php 	if ($this->session->userdata('logintype') == '-1' || $this->session->userdata('logintype') == '2') {
								?> 
									
								<?php 	 	        $account_data = $this->session->userdata("accountinfo"); 
												$login_type=$account_data['type'];

								
								?>
								
								<?php }else{
									if($ticket_lable== 'Answered'){ ?>
										  <input type="hidden" name="ticket_type" value="1">	
								<?php	}else{ ?>
										  <input type="hidden" name="ticket_type" value="2">	
								<?php	}
								 ?>
									
								<?php } ?>
							
<!--//~ change by bansi faldu
	//~ issue:#67-->


	<div class="col-sm-12 pb-4">
		<center>
			<a href='".base_url()."email/email_client_get/'><input type='submit' class='btn btn-success'  value=<?php echo gettext('Submit'); ?>></a>
			<a href='/supportticket/supportticket_list/'><input type='button' class='btn btn-secondary ml-2'  value=<?php echo gettext("Cancel"); ?>></a>
		</center>
	</div>








</div>
</div>
</form>
</div>
</div>
</div>
</div>	
</section>
</div>
	

		<div class="col-md-12 mb-4 px-0">
			<div class="card p-4">
				<div class="card-block">
					<?php
					foreach($details_arr as $key=>$value) { ?>
						
						<?php	if($value['message'] != ''){
						$login_type=$this->common->get_field_name('type','accounts',array('id'=>$value['generate_account_id']));
						 	 
						if($login_type == 1){ ?>
						
						<table align="center" style="width:100%;   color:#000;" class="table table-bordered m-0">
						<?php }else if(astpp_eq0($login_type)){
							  
							 ?>
						<table align="center" style="width:100%;   color:#000;" class="table table-bordered m-0">
					<?php }else{ ?>
						<table align="center" style="width:100%;   color:#000;" class="table table-bordered m-0">
					<?php } ?>
						<tbody>
							<tr>
								 
								<td width="20%" align="right">  
									<p style="margin-top: -0x;color: black;"><b><?php echo $this->common->get_field_name_coma_new('first_name,last_name,number','accounts',$value['generate_account_id']); ?></b></p>
									<p style="padding-right: 10px;color: black;" >	<?php 
										if($login_type == 1){
											echo gettext("Reseller");
										}else if(astpp_eq0($login_type)){
											echo gettext("Customer");
										}else{
											echo gettext("Admin");
										}
										?>	
										</p>
								</td>
								<td  style="padding-left: 10px; width:80%">
										<p style="color: black;color:#536973 !important; font-size:11px !important;margin-top: 5px;"><?php echo $this->common->convert_GMT_to('','',$value['creation_date']); ?></p>
										<p style="color: black;"><?php echo nl2br($value['message']);  ?></p>
										<p style="color: black;"><?php $explode= explode(',',$value['attachment']);
										$i=1;
										if($explode[0] != ''){
											Attachment:
											foreach($explode as $exp_val){
												echo " ".$i.".";
												$url=base_url()."supportticket/supportticket_list_attachment/".$exp_val;
												$downlaod_url= "<a style='color:#0000FF;' href='$url'><b>".gettext('Download Here'). "</b></a>";
												echo $exp_val." ".$downlaod_url." ";
												$i++;
											}
										}
										?></p>
								</td>
							</tr>
							</tbody>		
					</table>
						<?php } ?>
				            
					<?php } ?>
			</div> 
		</div>  
	</div> 
  
<? endblock() ?>
<? startblock('sidebar') ?>
<? endblock() ?>
<? end_extend() ?>
