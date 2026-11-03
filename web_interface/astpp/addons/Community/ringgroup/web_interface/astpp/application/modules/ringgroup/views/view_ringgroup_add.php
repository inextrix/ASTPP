<? extend('master.php') ?>
<?php error_reporting(E_ERROR); ?>
<? startblock('extra_head') ?>
<script type="text/javascript">
  function validateForm() {
   $('.overlay').show();

   flag =1;
   var err='';
   if (document.forms["ringgroup_form"]["name"].value == "") {
    $('.overlay').hide();

    document.getElementById('name_error_div').innerHTML = "<i style='color:#D95C5C; padding-right: 6px; padding-top: 10px;' class='fa fa-exclamation-triangle'></i><span class='popup_error error  p-0'>Name is Required.</span>";
    $('#name_error_div').show();
    return false;
  } else {
    $('#error_name').html("");
  }
}
</script>
<script type="text/javascript">


  var accountid = '';
  $(document).ready(function() { 
    accountid=$("#accountid").val(); 

    if(accountid != ''){
      account_change(accountid);
    }  
    $("#accountid").change(); 
    $("#accountid").change(function() { 
      
     accountid = this.value;     
     account_change(accountid);  
   });

  });
  
  function account_change(accountid) {
    $.ajax({
      type: "POST",
      url: "<?= base_url()?>/ringgroup/ringgroup_type_change/"+accountid,
      data:'',
      success:function(alt) {

        $(".no_answer_call_type").replaceWith(alt);
        $('.selectpicker').selectpicker('refresh');
      }
    });

    $.ajax({
      type: "POST",
      url: "<?= base_url()?>ringgroup/ringgroup_announcement_change/"+accountid,
      data:'',
      success:function(alt) {

        $(".announcementid").replaceWith(alt);
        $('.selectpicker').selectpicker('refresh');
      }
    });
    $.ajax({
      type: "POST",
      url: "<?= base_url()?>ringgroup/ringgroup_ringback_change/"+accountid,
      data:'',
      success:function(alt) {
        $(".ringbackid").replaceWith(alt);
        $('.selectpicker').selectpicker('refresh');
      }
    });
    var i='';
    for (i = 1; i <=rowCount; i++) { 
      jQuery('#rowCount'+i).remove();
    }
  }
</script>

<script type="text/javascript">

  function customer_account_change(val){
    $.ajax({
      type: "POST",
      url: "<?= base_url()?>/ringgroup/customer_account_change/"+val,
      data:'',
      success:function(alt) { 
       $("#accountid").html(alt); 
       $('.selectpicker').selectpicker('refresh');
       $("#accountid").change();
     }
   });

  }
</script>
<script type="text/javascript">
  function jsfunction(){
    var id=document.getElementById('no_answer_call_type').value;
    if(id == 'pstn_1')
    {
      $(".no_answer_call_type_value").removeClass("d-none");
      $(".no_answer_call_type").removeClass("col-md-12").addClass("col-md-6");
      $(".no_answer_call_type" ).addClass("col-md-6");
    }else{
     $(".no_answer_call_type").removeClass("col-md-6").addClass("col-md-12");
     $(".no_answer_call_type_value" ).addClass("d-none");
   }
 }

</script>
<script type="text/javascript">

 $(document).ready(function(){

  $('.no_answer_call_type_value' ).addClass("d-none");
  $('#ringgroup_advance').hide();
  $("#advance_id").click(function(){
   $("#ringgroup_advance").slideToggle();
 });
});
</script>
<?php endblock() ?>
<?php startblock('page-title') ?>
<?= $page_title ?>
<?php endblock() ?>
<?php startblock('content') ?>
<script>
 var rowCount   = "<?= $count ?>";
 function addMoreRows(frm,val="") {
   var type       = $("#accountid").val();
   rowCount ++;
   $.ajax({
    type: "POST",
    url: "<?= base_url()?>ringgroup/ringgroup_field_add/"+rowCount+"/"+val+"/"+type,
    data:'',
    success:function(alt) {
      jQuery('#addedRows').append(alt);
    }
  });
 }
 function removeRow(removeNum) {
  jQuery('#rowCount'+removeNum).remove();
}
</script>

<section class="slice color-three m-0">
  <div class="w-section inverse p-0">
    
    <div class="pop_md col-md-12 p-0">
      <form action="<?php echo base_url(); ?>ringgroup/ringgroup_save/" accept-charset="utf-8" id="ringgroup_form" method="POST" name="ringgroup_form">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="pb-4" id="floating-label">
                <h3 class="bg-secondary text-light p-3 rounded-top"><?php echo gettext("Basic Settings"); ?></h3>
                <input type="hidden" name="id" value="" />
                <?php 
                $accountinfo=$this->session->userdata('accountinfo');
                $reseller_id='';
                $reseller_id=$accountinfo['type'] == 1?$accountinfo['id']:0;
                if ($accountinfo['type']=='-1' || $accountinfo['type'] == '2') {
                  $whr= array("reseller_id" => 0, "status" => "0", "deleted" => "0", "type" => '1');
                  $reseller_data = $this->db_model->getSelect("*", "accounts", $whr);
                  $whr= array("reseller_id" => 0, "status" => "0", "deleted" => "0", "type" => '0');
                  $account = $this->db_model->getSelect("*", "accounts", $whr);
                }else{
                  $whr= array("reseller_id" => $reseller_id,"status" => "0", "deleted" => "0", "type" => "0");
                  $reseller = $this->db_model->getSelect("*", "accounts", $whr);
                  $whr= array("reseller_id" => $reseller_id, "status" => "0", "deleted" => "0", "type" => '0');
                  $account = $this->db_model->getSelect("*", "accounts", $whr);
                }
                
                ?>

                <div class="col-md-12">
                  <div class="row">
                   <?php if(astpp_eq0($accountinfo['type']) || $accountinfo['type'] == 1){ ?>
                    <div class="col-md-6 form-group">
                      <label class="p-0 control-label"><?php echo gettext("Name"); ?><span style="color:black;">*</span></label>
                      <input type="text" id="name" maxlength="20" name="name" class="col-md-12 form-control form-control-lg" value="<?php echo isset($edit_array['name'])?$edit_array['name']:''; ?>">  
                      <div class="tooltips error_div pull-left no-padding" id="name_error_div" style="display: none;"><i class="fa fa-exclamation-triangle error_triangle"></i><span class="popup_error error p-0" id="name_error"></span></div>  
                    </div>
                  <?php }else{ ?>
                    <div class="col-md-6 form-group">
                      <label class="p-0 control-label"><?php echo gettext("Name"); ?><span style="color:black;">*</span></label>
                      <input type="text" id="name" maxlength="20" name="name" class="col-md-12 form-control form-control-lg" value="<?php echo isset($edit_array['name'])?$edit_array['name']:''; ?>">  
                      <div class="tooltips error_div pull-left no-padding" id="name_error_div" style="display: none;"><i class="fa fa-exclamation-triangle error_triangle"></i><span class="popup_error error p-0" id="name_error"></span></div>  
                    </div>
                    

                    <div class="col-md-6 form-group">
                      <label class="p-0 control-label" data-toggle="tooltip" data-html="true" data-original-title= "Select Reseller Account" data-placement="top"><?php echo gettext("Reseller"); ?></label>
                      <select name="reseller_id " id="reseller_id" class='col-md-12 form-control selectpicker form-control-lg' onchange="customer_account_change(this.value)" data-live-search="true">
                        <option value="0"><?php echo gettext('Admin'); ?></option>
                        <?php
                        foreach ($reseller_data->result_array() as $value)
                          {?>
                            <? if(isset($value['company_name']) && $value['company_name'] != '') {?>
                              <option value="<?php echo $value['id']; ?>"><?php echo $value['company_name'] .'('.$value['number'].')'; ?></option>
                            <? } else {?>
                              <?php 
                              if($value['id']==$accountinfo['id']){
                                $selected="selected";
                              }else{
                                $selected="";
                              }?>
                              <option value="<?php echo $value['id']; ?>"><?php echo $value['first_name'].' '.$value['last_name'] .'('.$value['number'].')'; ?></option>
                            <?php }?>
                          <? } ?>
                        </select>
                      </div>
                    <?php } ?>


                    <div class="col-md-6 form-group">
                      <label class="p-0 control-label"><?php echo gettext("Ring Strategy"); ?></label> 

                      <select name="strategy" class='col-md-12 form-control selectpicker form-control-lg' data-live-search="true" onChange="changeTest(this.value)">
                       <option value="sequence"><?php echo gettext("Sequence"); ?></option>
                       <option value="simultaneous"><?php echo gettext("Simultaneous"); ?></option>
                     </select>
                     <div class="col-md-12 p-0 error_div"></div>
                     <div class="col-md-3">&nbsp;</div>
                   </div>

                   <div class="col-md-6 form-group">
                    <?php if($accountinfo['type'] == '-1' || $accountinfo['type'] == '2') {?>
                      <label class="p-0 control-label" data-toggle="tooltip" data-html="true" data-original-title = "Listing of Accounts here"><?php echo gettext("Account"); ?></label>
                      <select name="accountid" id="accountid" class='col-md-12 form-control selectpicker form-control-lg'  data-live-search="true">
                       <?php
                       foreach ($account->result_array() as $value) {?>
                        <? if(isset($value['company_name']) && $value['company_name'] != '') {?>
                          <option value="<?php echo $value['id']; ?>"><?php echo $value['company_name'] .'('.$value['number'].')'; ?></option>
                        <? } else {?>
                          <?php 
                          if($value['id']==$accountinfo['id']){
                           $selected="selected";
                         }else{
                           $selected="";
                         }?>

                         <option value="<?php echo $value['id']; ?>"><?php echo $value['first_name'].' '.$value['last_name'] .'('.$value['number'].')'; ?></option>
                       <?php }  ?>
                     <? } ?>
                   </select>

                   <?php 
                 }else{?>
                  <?php if($accountinfo['type'] == '1')
                  {
                   ?>
                   <label class="col-md-12 p-0 control-label"><?php echo gettext("Account"); ?></label>
                   <select name="accountid" id="accountid" class='col-md-12 form-control selectpicker form-control-lg'  data-live-search="true">
                     <?php

                     foreach ($reseller->result_array() as $value) {?>
                      <? if(isset($value['company_name']) && $value['company_name'] != '') {?>
                        <option value="<?php echo $value['id']; ?>"><?php echo $value['company_name'] .'('.$value['number'].')'; ?></option>
                      <? } else {?>
                        <?php 
                        if($value['id']==$accountinfo['id']){
                          $selected="selected";
                        }else{
                          $selected="";
                        }?>

                        <option value="<?php echo $value['id']; ?>"><?php echo $value['first_name'].' '.$value['last_name'] .'('.$value['number'].')'; ?></option>
                      <?php }  ?>
                    <?php } ?>
                  </select>
                <?php }
              }
              ?>
            </div>
            <div class="col-md-12 pt-4">
              <div class="col-md-12">
                <div class="row">
                 <div class="col-9 p-0"><label class="control-label" data-toggle="tooltip" data-html="true" data-original-title= "Extension : If destination number is Extension then system will call local extension. PSTN : If destination number is PSTN then system will call PSTN number using gateway and call charges will be applicable on customer." data-placement="top"><?php echo gettext("Destination");?></label></div>
                 
                 <div class="col-md-2"><label class="control-label" data-toggle="tooltip" data-html="true" data-original-title= "Set call ring timeout for extension calls (Only work for Sequence ring strategy)" data-placement="top"><?php echo gettext("Timeout (Seconds)");?></label></div>
                 
                 <span class="btn btn-success" id="addmore" onclick="addMoreRows(this.form,this.value);"><?php echo gettext("Add More"); ?></span>
               </div>
             </div>
             <div class="col-md-12 p-0 error_div"></div>
           </div>

           <div id="addedRows" class="col-md-12"></div>

           <li class="col-md-12 form-group h-auto pt-4">
            <label class="p-0 control-label"><?php echo gettext("Description"); ?></label>
            <textarea  name="description" id="description" class="col-md-12 form-control form-control-lg mit-20"><?= $edit_array['description']; ?></textarea>
            <div class="col-md-12 p-0 error_div"></div>
            <div class="col-md-3">&nbsp;</div>
          </li>

          <div class="col-md-6 form-group mt-4">
           <label class="col-md-12 p-0 control-label"><?php echo gettext("Status"); ?> </label>  
           <select name="status" class="col-md-12 form-control selectpicker form-control-lg" data-live-search="true">
            <option value="0" selected="select">Active</option>
            <option value="1">Inactive</option>
          </select>
        </div>

      </div>
    </div>
  </div>


</div>
<div class="col-md-12 mt-4 text-center">
  <button name="action" type="submit" value="save" id="submit" class="btn btn-success" onclick="return validateForm();"><?php echo gettext("Save"); ?></button>
  <button name="action" type="button" value="cancel" class="btn btn-secondary ml-2" onclick="return redirect_page('/ringgroup/ringgroup_list/')"><?php echo gettext("Close"); ?></button>
</div>
</form>
<?php 
if (isset($validation_errors) && $validation_errors != '') {
 ?>
 <script>
  var ERR_STR = '<?php echo $validation_errors; ?>';
  print_error(ERR_STR);
</script>
<? } ?>
</div>
</div>
</section>
<? endblock() ?>
<? startblock('sidebar') ?>
<? endblock() ?>
<? end_extend() ?>

