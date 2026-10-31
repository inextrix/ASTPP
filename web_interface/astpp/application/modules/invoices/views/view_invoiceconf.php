<? extend('master.php') ?>
<? startblock('extra_head') ?>

<?php endblock() ?>

<?php startblock('page-title') ?>
<?= $page_title ?>
<?php endblock() ?>
<?php startblock('content')?>
<script type="text/javascript" language="javascript">
$(document).ready(function() {
    $("input[type='hidden']").parents('li.form-group').addClass("d-none");
    $("span.fileinput-filename").parent().removeClass("form-control");

    $("#uploadFile").change(function() {
        var fuData = document.getElementById('uploadFile');
        var FileUploadPath = fuData.value;
        if (FileUploadPath != '') {
            var Extension = FileUploadPath.substring(FileUploadPath.lastIndexOf('.') + 1).toLowerCase();
            if ( Extension == "jpeg" || Extension == "jpg" || Extension == "png") {
                    if (fuData.files && fuData.files[0]) {
                        var size = fuData.files[0].size;
                        if(size > 1000000){
                            alert("<?php echo gettext('Maximum Logo upload size is 1MB'); ?>");
                            $("#uploadFile").val('');
                            $('#company_logo').attr('src', '');
                        }else{
                            
                            readURL(this,'logo');
                        }
                    }

            }else {
                alert("Logo only allows file types of JPG and JPEG. ");
                $("#uploadFav").val('');
                $('#company_logo').attr('src', '');
            }
        }

    });
    $("#uploadFav").change(function() {
        
        var fuData = document.getElementById('uploadFav');
        var FileUploadPath = fuData.value;
        if (FileUploadPath != '') {
            var Extension = FileUploadPath.substring(FileUploadPath.lastIndexOf('.') + 1).toLowerCase();
            if (Extension == "ico" || Extension == "png" || Extension == "jpeg" || Extension == "jpg") {
                    if (fuData.files && fuData.files[0]) {
                        var size = fuData.files[0].size;
                        if(size > 1000000){
                            alert("Maximum Favicon upload size is 1MB");
                            $("#uploadFav").val('');
                        }else{
                            readURL(this,'fav');
                        }
                    }
            }else {
                 alert("Favicon only allows file types of ICO, PNG, JPG and JPEG. ");
                $("#uploadFav").val('');
                $('#company_fav').attr('src', '');
            }
        }
    });
    $("#logo_delete").click(function(){
        var id=$("#invoice_conf_form input[name='id']").first().val(); // was the first hidden field of the page (now the CSRF token)
        var image=$('#company_logo').prop('src');
        var confirm_string = 'Are you sure want to remove Logo?';
	    var answer = confirm(confirm_string);
        if(answer)
        {
            $.ajax({ 
                type: 'POST', 
                url: "<?php echo base_url(); ?>invoices/invoice_list_image_delete",
                data: { id: id ,logo:'logo'}, 
                dataType: 'json',
                success: function (data) { 
                    $('#company_logo').attr('src', '');
                    
                    window.location.reload();
                }
            });
        }else{
		    return false;
	    }
    });

    $("#fav_delete").click(function(){
       
        var id=$("#invoice_conf_form input[name='id']").first().val(); // was the first hidden field of the page (now the CSRF token)
        var image=$('#company_fav').prop('src');
       
       var confirm_string = 'Are you sure want to remove Favicon?';
	    var answer = confirm(confirm_string);
        if(answer)
        {
            $.ajax({ 
                type: 'POST', 
                url: "<?php echo base_url(); ?>invoices/invoice_list_image_delete",
                data: { id: id ,logo:'fav' }, 
                dataType: 'json',
                success: function (data) { 
                    $('#company_fav').attr('src', '');
                    window.location.reload();
                }
            });
        }else{
            return false;
        }
    });

    var error_fav = "<?php echo isset($error_fav) ? $error_fav : ''; ?>";
    var error_file = "<?php echo isset($error_file) ? $error_file : ''; ?>";
    if(error_fav != '')
    {
        $("#uploadFav").before("<div style='top: 60px;position: absolute;font-size:11px;font-weight: 600;color:red;'>"+error_fav+"</div>");
    }
    if(error_file != '')
    {
        $("#uploadFile").before("<Span style='top: 60px;position: absolute;font-size:11px;font-weight: 600;color:red;'>"+error_file+"</span>");
    }

$("#uploadFile").removeClass("col-md-12 form-control form-control-lg").addClass("custom-file-input mit-20");

$("#uploadFile").after("<label class='custom-file-label btn-primary btn-file text-left mit-20 mx-4' for='file'> </label>");
$("#uploadFav").parents("li.col-md-12.form-group").addClass("mt-5");
$("#uploadFav").removeClass("col-md-12 form-control form-control-lg").addClass("custom-file-input custom_class mit-20");
$("#uploadFav").after("<label class='custom-file-label btn-primary btn-file text-left mit-20 mx-4' for='file'> </label>");


});


function readURL(input,type) {

    if (input.files && input.files[0]) {
    var reader = new FileReader();

    reader.onload = function(e) {
        if(type=='fav')
        {
            $('#company_fav').attr('src', e.target.result);
        }else{
            $('#company_logo').attr('src', e.target.result);
        }
    }

    reader.readAsDataURL(input.files[0]);
    }
}

</script>


<div class="p-0">
	<section class="slice color-three m-0">
		<div class="w-section inverse p-0">
            <?php echo $form; ?>
            <?php
            if (isset($validation_errors) && $validation_errors != '') {
                ?>
                <script>
                var ERR_STR = '<?php echo $validation_errors; ?>';
                print_error(ERR_STR);
                </script>
            <?php } ?>
          </div>
	</section>

</div>

<style>
#imagePreview {
	width: 264px;;
	height: 104px;
	background-position: right right;
	background-size: cover;
	-webkit-box-shadow: 0 0 1px 1px rgba(0, 0, 0, .3);
	display: inline-block;
}

#imagePreview:before {
	content: "×";
	float: right;
	margin-top: -10px;
	margin-left: 150px;
	color: black;
	margin-right: -30px;
	cursor: pointer;
	font-size: 31px;
	font-weight: bold;
	display: inline-block;
	line-height: 0px;
	padding: 8px 3px 0px 5px;
}

#uploadFile, #uploadFav {
	content: 'Select some files';
	display: inline-block;
	border: 1px solid #E4E4E4;
	border-radius: 3px;
	padding: 0px 0px;
	overflow-x: hidden;
	outline: none;
	white-space: nowrap;
	-webkit-user-select: none;
	cursor: pointer;
	color: transparent;
	background: -webkit-linear-gradient(top, #e3e3e3, #f9f9f9);
	font-weight: 400;
	font-size: 10pt;
}
</style>


<? endblock() ?>
<? startblock('sidebar') ?>
<? endblock() ?>
<? end_extend() ?>


