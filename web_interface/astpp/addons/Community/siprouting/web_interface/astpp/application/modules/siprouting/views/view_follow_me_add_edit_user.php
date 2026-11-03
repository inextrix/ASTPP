<? extend('master.php') ?>
<?php error_reporting(E_ERROR); ?>

<? startblock('extra_head') ?>
<?php endblock() ?>

<?php startblock('page-title') ?>
<?= $page_title ?>
<?php endblock() ?>

<?php startblock('content') ?>
<style>
    .form-label {
        font-weight: bold;
        color: black;
    }

    .margin-t-10 {
        margin-top: 10px;
    }

    .margin-l-25 {
        margin-left: 25%;
    }

    .margin-l-8 {
        margin-left: 8px;
    }

    .margin-l-7 {
        margin-left: -7px;
    }

    .margin-l-12 {
        margin-left: 12px;
    }

    .margin-b-10 {
        margin-bottom: 10px;
    }

    .box {
        padding: 0;
        background-color: #F7FAFD;
        box-shadow: 0 0 5px #CEE0F0;
    }

</style>


<script>
    function call_forwarding_enabled_disabled(){
        var radioValue = $("input[name='call_forwarding_flag']:checked").val();
        if(radioValue == 0){
            $('#extension_call_forward_disabled').show();
        }else{
            $('#extension_call_forward_disabled').hide();
        }
    }

    function on_busy_enabled_disabled(){
        var on_busy = $("input[name='on_busy_flag']:checked").val();
        if(on_busy == 0){
            $('#on_busy_disabled').show();
        }else{
            $('#on_busy_disabled').hide();
        }
    }

    function no_answer_disabled_enabled(){
        var no_answer = $("input[name='no_answer_flag']:checked").val();
        if(no_answer == 0){
            $('#no_answer_disabled').show();
        }else{
            $('#no_answer_disabled').hide();
        }
    }

    function not_register_disabled_enabled(){
        var not_register = $("input[name='not_register_flag']:checked").val();
        if(not_register == 0){
            $('#not_register_disabled').show();
        }else{
            $('#not_register_disabled').hide();
        }
    }

    function follow_me_enabled_disabled(){
        var follow_me = $("input[name='follow_me_flag']:checked").val();
        if(follow_me == 0){
            $('#dropdown_id').show();
            $('#ignore_busy').show();
            $('#do_not_disturb').show();
        }else{
            $('#ingnore_busy_enabled').prop('checked', false);
            $('#do_not_disturb_enabled').prop('checked', false);
            $('#dropdown_id').hide();
            $('#ignore_busy').hide();
            $('#do_not_disturb').hide();
        }
    }

    function validateForm() {
        var err='';
        if (document.forms["follow_me_form"]["call_forwarding_flag"].value == 0 && document.forms["follow_me_form"][
            "call_forwarding_destination"
            ].value == "") {
            $('#error_call_forwarding_destination').html("Please Enter Destination Number");
        err='123';
    } else {
        $('#error_call_forwarding_destination').html("");
    }
    if (document.forms["follow_me_form"]["on_busy_flag"].value == 0 && document.forms["follow_me_form"][
        "on_busy_destination"
        ].value == "") {
        $('#error_on_busy_destination').html("Please Enter Destination Number");
    err='123';

} else {
    $('#error_on_busy_destination').html("");
}

if (document.forms["follow_me_form"]["no_answer_flag"].value == 0 && document.forms["follow_me_form"][
    "no_answer_destination"
    ].value == "") {
    $('#error_no_answer_destination').html("Please Enter Destination Number");
err='123';

} else {
    $('#error_no_answer_destination').html("");
}

if (document.forms["follow_me_form"]["not_register_flag"].value == 0 && document.forms["follow_me_form"][
    "not_register_destination"
    ].value == "") {
    $('#error_not_register_destination').html("Please Enter Destination Number");
err='123';

} else {
    $('#error_not_register_destination').html("");
}
if(err != '')
{
    return false;
}
}

$(document).ready(function() {
    call_forwarding_enabled_disabled();
    on_busy_enabled_disabled();
    no_answer_disabled_enabled();
    not_register_disabled_enabled();
    follow_me_enabled_disabled();
    $('#call_forwarding_destination').selectpicker();
});
</script>
<script>
    function change_extension_call_forward(exten_num) {

        if (exten_num != "") {
            exten_num = exten_num
        } else {
            exten_num = ""
        }
        var extension_call_forward = $("#extension_call_forward").val();
        var accountid = $("#accountid").val();
        var name_id = "call_forwarding_destination";
        $.ajax({
            type: "POST",
            url: "<?php echo base_url();?>user/user_fssipdevices_build_extension_dropdown",
            data: {
                name: name_id,
                extension_value: extension_call_forward,
                accountid: accountid,
                exten_num: exten_num
            },
            success: function(response) {
                $(".call_forwarding_destination").replaceWith(response);
                $('.selectpicker').selectpicker('refresh');
            }
        });
    }

    function change_extension_on_busy(exten_num) {

        if (exten_num != "") {
            exten_num = exten_num
        } else {
            exten_num = ""
        }
        var extension_on_busy = $("#extension_on_busy").val();
        var accountid = $("#accountid").val();
        var name_id = "on_busy_destination";
        $.ajax({
            type: "POST",
            url: "<?php echo base_url();?>user/user_fssipdevices_build_extension_dropdown",
            data: {
                name: name_id,
                extension_value: extension_on_busy,
                accountid: accountid,
                exten_num: exten_num
            },
            success: function(response) {
                $(".on_busy_destination").replaceWith(response);
                $('.selectpicker').selectpicker('refresh');
            }
        });
    }

    function change_extension_no_answer(exten_num) {
        if (exten_num != "") {
            exten_num = exten_num
        } else {
            exten_num = ""
        }
        var extension_on_busy = $("#extension_no_answer").val();
        var accountid = $("#accountid").val();
        var name_id = "no_answer_destination";
        $.ajax({
            type: "POST",
            url: "<?php echo base_url();?>user/user_fssipdevices_build_extension_dropdown",
            data: {
                name: name_id,
                extension_value: extension_on_busy,
                accountid: accountid,
                exten_num: exten_num
            },
            success: function(response) {
                $(".no_answer_destination").replaceWith(response);
                $('.selectpicker').selectpicker('refresh');
            }
        });
    }

    function change_extension_not_registered(exten_num) {
        if (exten_num != "") {
            exten_num = exten_num
        } else {
            exten_num = ""
        }
        var extension_on_busy = $("#extension_not_registered").val();
        var accountid = $("#accountid").val();
        var name_id = "not_register_destination";
        $.ajax({
            type: "POST",
            url: "<?php echo base_url();?>user/user_fssipdevices_build_extension_dropdown",
            data: {
                name: name_id,
                extension_value: extension_on_busy,
                accountid: accountid,
                exten_num: exten_num
            },
            success: function(response) {
                $(".not_register_destination").replaceWith(response);
                $('.selectpicker').selectpicker('refresh');
            }
        });
    }

    $(document).ready(function() {
        var extension_num_on_busy = "<?php echo $edit_array['on_busy_destination']; ?>";
        if (extension_num_on_busy != "") {
            extension_num_on_busy = extension_num_on_busy
        } else {
            extension_num_on_busy = ""
        }
        change_extension_on_busy(extension_num_on_busy);
        var extension_num_call_forward = "<?php echo $edit_array['call_forwarding_destination']; ?>";
        if (extension_num_call_forward != "") {
            extension_num_call_forward = extension_num_call_forward
        } else {
            extension_num_call_forward = ""
        }
        change_extension_call_forward(extension_num_call_forward);
        var extension_num_no_answer = "<?php echo $edit_array['no_answer_destination']; ?>";
        if (extension_num_no_answer != "") {
            extension_num_no_answer = extension_num_no_answer
        } else {
            extension_num_no_answer = ""
        }
        change_extension_no_answer(extension_num_no_answer);
        var extension_num_not_register = "<?php echo $edit_array['not_register_destination']; ?>";
        if (extension_num_not_register != "") {
            extension_num_not_register = extension_num_not_register
        } else {
            extension_num_not_register = ""
        }
        change_extension_not_registered(extension_num_not_register);

    });
</script>
<script>
    $(document).ready(function() {
        $('#call_forwarding_destination').selectpicker();
    });
</script>
<section class="p-0">
    <div class="slice color-three">
        <div class="w-section inverse p-0">
          <form method="post" name='follow_me_form' id="follow_me_form_id" enctype="multipart/form-data"
          action="<?= base_url()?>user/user_fssipdevices_routing_save">
          <div class="slice color-three portlet-content">
            <div id="floating-label" class="col-md-12 p-0">
                <ul class="p-0">
                    <div class="col-md-12 p-0">
                        <div class="">
                            <div class="col-md-12 my-4 p-0">  
                                <div class="row">
                                    <li class="col-md-3 ps-0 pr-lg-2">
                                        <div class="card col-md-12 p-0">
                                            <div class="alert-white align-items-center p-3 col-md-12 d-flex justify-content-between">
                                                <label class="form-label"><?php echo gettext("Always"); ?></label>
                                                <div class="text-right">
                                                    <label class="switch">
                                                        <input type="checkbox" name="call_forwarding_flag" id="call_forwarding_flag_enabled" class="onoffswitch-checkbox" onclick="call_forwarding_enabled_disabled();" value="0" <?php if(astpp_eq0($edit_array['call_forwarding_flag'])){ echo "checked=checked";}  ?>>
                                                        <span class="slider round"></span>
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-12"  id="extension_call_forward_disabled">
                                                <div class="row p-4">
                                                    <label>Extension</label>
                                                    <select name="extension_call_forward" onchange="return change_extension_call_forward()" id="extension_call_forward" class="form-control form-control-lg col-md-6 float-left selectpicker call_forwarding_destination" data-live-search="true" disabled>
                                                        
                                                        <option value="2" <?php if($edit_array['extension_call_forward'] == '2'){echo "selected=selected";} ?>> <?php echo gettext("Extension"); ?> </option>
                                                    </select>
                                                </div>
                                            </div>                                  

                                            <span class="popup_error no-padding" style="color:red;margin-left:35%;" id="error_call_forwarding_destination"></span>                                      
                                        </div>                                  
                                    </li>
                                    

                                    <li class="col-md-3 pl-lg-2 pr-lg-2">
                                        <div class="card col-md-12 p-0">
                                            <div class="">
                                                <div class="alert-white align-items-center p-3 col-md-12 d-flex justify-content-between">   
                                                    <label class="form-label"><?php echo gettext("On Busy"); ?></label>
                                                    <div>
                                                        <label class="switch">
                                                            <input type="checkbox" name="on_busy_flag" class="onoffswitch-checkbox" onclick="on_busy_enabled_disabled()" value="0" <?php if(astpp_eq0($edit_array['on_busy_flag'])){ echo "checked=checked";}  ?>>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-12" id="on_busy_disabled">
                                                    <div class="row p-4">
                                                        <label>Extension</label>
                                                        <select name="extension_on_busy" id="extension_on_busy" onchange="return change_extension_on_busy()" class="form-control form-control-lg col-md-6 float-left selectpicker on_busy_destination" data-live-search="true" disabled>
                                                            <option value="2" <?php if($edit_array['extension_on_busy'] == '2'){echo "selected=selected";} ?>> <?php echo gettext("Extension"); ?> </option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <span class="popup_error no-padding" style="color:red;margin-left:35%;" id="error_on_busy_destination"></span> 
                                                
                                            </div>                                  
                                        </li>

                                        <li class="col-md-3 pl-lg-2 pr-lg-2">
                                            <div class="card col-md-12 p-0">
                                                <div class="">
                                                    <div class="alert-white align-items-center p-3 col-md-12 d-flex justify-content-between">     
                                                        <label class="form-label"><?php echo gettext("No Answer"); ?></label>
                                                        <div>
                                                            <label class="switch">
                                                                <input type="checkbox" name="no_answer_flag" onclick="no_answer_disabled_enabled()" class="onoffswitch-checkbox" value="0" <?php if(astpp_eq0($edit_array['no_answer_flag'])){ echo "checked=checked";}  ?>>
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </div>  
                                                    </div>  
                                                    <div class="col-md-12" id="no_answer_disabled">
                                                        <div class="row p-4">
                                                            <label>Extension</label>
                                                            <select name="extension_no_answer" onchange="return change_extension_no_answer()" id="extension_no_answer" class="form-control form-control-lg col-md-6 float-left selectpicker no_answer_destination" data-live-search="true" disabled>
                                                                <option value="2" <?php if($edit_array['extension_no_answer'] == '2'){echo "selected=selected";} ?>> <?php echo gettext("Extension"); ?> </option>
                                                            </select>  
                                                        </div>
                                                    </div>
                                                    <span class="popup_error no-padding" style="color:red;margin-left:35%;" id="error_no_answer_destination"></span>  
                                                </div>
                                            </div>
                                        </li>

                                        <li class="col-md-3 pl-lg-2">
                                            <div class="card col-md-12 p-0">
                                                <div class="">
                                                    <div class="alert-white align-items-center p-3 col-md-12 d-flex justify-content-between">  
                                                        <label class="form-label">
                                                            <?php echo gettext("Not Registered"); ?>
                                                        </label>
                                                        <div>
                                                            <label class="switch">
                                                                <input type="checkbox" name="not_register_flag" onclick="not_register_disabled_enabled()" class="onoffswitch-checkbox" value="0" <?php if(astpp_eq0($edit_array['not_register_flag'])){ echo "checked=checked";}  ?>>
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12" id="not_register_disabled">
                                                        <div class="row p-4">
                                                            <label>Extension</label>
                                                            <select name="extension_not_registered" onchange="return change_extension_not_registered()" id="extension_not_registered" class="form-control form-control-lg col-md-6 float-left selectpicker not_register_destination" data-live-search="true" disabled>
                                                                0                                       <option value="1" <?php if($edit_array['extension_not_registered'] == '1'){echo "selected=selected";} ?>> <?php echo gettext("PSTN"); ?> </option>
                                                                <option value="2" <?php if($edit_array['extension_not_registered'] == '2'){echo "selected=selected";} ?>> <?php echo gettext("Extension"); ?> </option>
                                                            </select>
                                                        </div>
                                                    </div> 
                                                    <span class="popup_error no-padding" style="color:red;margin-left:35%;" id="error_not_register_destination"></span>

                                                </div>
                                            </div>
                                        </li>
                                    </div>
                                </div></div>
                                
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-6 p-0 pr-lg-1">
                                        <div class="card p-3">  
                                           <div class="pb-4">
                                            <label class="col-md-3 p-0 form-label">
                                                <?php echo gettext("Allow Recording"); ?>
                                            </label>
                                            <select name="is_recording" id="is_recording" class="dropdown bootstrap-select form-control form-control-lg col-md-12 float-left selectpicker" data-live-search="true" tabindex="-98">
                                                <option value="1" <?php if($edit_array['is_recording'] == '1'){echo "selected=selected";} ?>><?php echo gettext("No"); ?></option>
                                                <option value="0" <?php if($edit_array['is_recording'] == '0'){echo "selected=selected";} ?>><?php echo gettext("Yes"); ?></option>
                                            </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 ">
                                        <input type="hidden" name="id" class="col-md-5 form-control" value="<?= $edit_array['id']; ?>">  
                                        <input type="hidden" name="redirect_url" class="col-md-5 form-control" value="<?= $_SERVER['HTTP_REFERER']; ?>">  
                                        <input type="hidden" name="accountid" id="accountid" class="col-md-5 form-control" value="<?= $accountid; ?>">
                                        <div class="">  
                                            <div class="row">  
                                                

                                                
                                            </div></div>

                                            <input type="hidden" name="sip_device_id" class="col-md-5 form-control" value="<?=$sip_device_id?>">    
                                        </div>
                                    </div>              
                                </ul>
                                
                            </div>
                        </div>
                        
                        <div class="text-center col-12 my-4">
            <!-- <a id="submit" class="btn btn-success" onclick="validateForm()"><?php echo gettext('Save'); ?>
            <input class="btn btn-primary  margin-x-10" onclick="closepopup()" name="action" value="<?php echo gettext('Cancel'); ?>" type="button"> -->
            <button name="action" type="submit" value="save" id="submit" class="btn btn-primary" onclick="return validateForm();"><?php echo gettext('Save'); ?></button>
            <a href="javascript:history.go(-1)" class="btn text-primary margin-x-10" type="button"><?php echo gettext('Cancel'); ?></a>
            
            
        </form>
    </div>
</div>
</section>

<? endblock() ?>   
<? end_extend() ?> 



