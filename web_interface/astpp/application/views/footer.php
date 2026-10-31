</section>
</span></span>
<div class="overlay">
    <div id="loading-img"></div>
</div>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.dlmenu.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        var jqueryarray = <?php echo json_encode($this->tooltip_data); ?>;
        //console.log(jqueryarray);
        if(jqueryarray && jqueryarray !=''){
          var form_name = $('form').attr("name");
          $.each(jqueryarray, function (key, val) {
            key = key.replace(form_name+'_','');
            // console.log(key);
            /*If condition used for select,input and textarea tag purpose*/
            if(key == 'customer_type' || key == 'country_id[]' || key == 'accounts_id[]' || key == 'fields_in_csv[]' || key == 'recuring_type' || key == 'product_id' || key == 'product_category' || key == 'product_rate_group' || key == 'product_rate_group[]' ||key == 'debit_amt' || key == 'email_notify' || key == 'despostion[]' || key == 'template' || key == 'call_forwarding_flag' || key == 'on_busy_flag' || key == 'no_answer_flag' || key == 'not_register_flag' ){

              $('select[name="'+key+'"] , input[name="'+key+'"] , textarea[name="'+key+'"]').parent().parent().find('label').attr({'data-toggle':'tooltip', 'data-html':"true",'data-original-title':val, 'data-placement' : 'right'}); 
            }else{

               $('select[name="'+key+'"] , input[name="'+key+'"]  , textarea[name="'+key+'"]').parent().find('label').attr({'data-toggle':'tooltip', 'data-html':"true",'data-original-title':val, 'data-placement' : 'right'});
             }
          });
        }
    });
  </script>
<script>
	$(document).ready(function() {

  //ASTPPENT-1172 Start
  if($('body .sidebar').length > 0)
  {
    $('body').addClass('with-sidebar');
  }
  else
  {
    $('body').addClass('no-sidebar');
  }
  //ASTPPENT-1172 End    

  $('[data-toggle="tooltip"]').tooltip(
     { boundary: 'window' }
  );  
  $('.btn-quick > div:first-child').on('click', function(){
    $('.btn-quick').toggleClass('open');
  })
      $(document).on("click", function(event){
        if(!$(event.target).is(".btn-quick, .btn-quick > div, .btn-quick > div > i, .btn-quick div label"))
            $('.btn-quick').removeClass('open');
        })
})
</script>

<script type="text/javascript" language="javascript">
$('.dropdown-menu a.dropdown-toggle').on('click', function(e) {
  if (!$(this).next().hasClass('show')) {
    $(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
  }
  var $subMenu = $(this).next(".dropdown-menu");
  $subMenu.toggleClass('show');

  $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
    $('.dropdown-submenu .show').removeClass("show");
  });

  return false;
});
</script>



<script type="text/javascript" language="javascript">
$(document).ready(function() {
  var cnt = $("#ListForm").contents();
  $("#ListForm").replaceWith(cnt);
   
  $('.multiselectable').on('focus blur', function (e) {
        $(this).parents('.form-group').toggleClass('focused');
  }).trigger('blur');

    $("input[type='hidden']").parents('li.form-group').addClass("d-none");
  
  $('.selectpicker').selectpicker();
  
  $("button, a, .btn").attr("data-ripple"," "); 
   $("textarea").parents('li.form-group').addClass("h-auto");
   $(".fileinput-new").parents('span.btn').css("position","relative");
   

  
  $(".tooltips.error_div").on('click', function() {
      $("div.tooltips span").addClass("tooltips_selected");
   });
  
  $("#reseller_batch_update .col-md-4.input-group .gj-datepicker").addClass("col-md-6 p-0");
  $("#customer_batch_update .col-md-4.input-group .gj-datepicker").addClass("col-md-6 p-0");
  $("#language_drp_sown").on('change',function(){
  var language=$("#language_drp_sown").val();
  get_lang(language);
  });

  $("no-ripple").removeAttr("data-ripple"," "); 
 
 
});

</script>
 <script>
 jQuery(document).ready(function($) {
  $('a[rel*=facebox]').facebox();
  <?php // ASTPPCOM-941 Start ?>
  $("#report_change").on('change',function(){
    var report_change=$("#report_change").val();
    window.location.href = report_change;
  });
  <?php //ASTPPCOM-941 END ?>
}) 
</script>
    <script type="text/javascript" charset="utf-8">

    function css_browser_selector(u){var ua=u.toLowerCase(),is=function(t){return ua.indexOf(t)>-1},g='gecko',w='webkit',s='safari',o='opera',m='mobile',h=document.documentElement,b=[(!(/opera|webtv/i.test(ua))&&/msie\s(\d)/.test(ua))?('ie ie'+RegExp.$1):is('firefox/2')?g+' ff2':is('firefox/3.5')?g+' ff3 ff3_5':is('firefox/3.6')?g+' ff3 ff3_6':is('firefox/3')?g+' ff3':is('gecko/')?g:is('opera')?o+(/version\/(\d+)/.test(ua)?' '+o+RegExp.$1:(/opera(\s|\/)(\d+)/.test(ua)?' '+o+RegExp.$2:'')):is('konqueror')?'konqueror':is('blackberry')?m+' blackberry':is('android')?m+' android':is('chrome')?w+' chrome':is('iron')?w+' iron':is('applewebkit/')?w+' '+s+(/version\/(\d+)/.test(ua)?' '+s+RegExp.$1:''):is('mozilla/')?g:'',is('j2me')?m+' j2me':is('iphone')?m+' iphone':is('ipod')?m+' ipod':is('ipad')?m+' ipad':is('mac')?'mac':is('darwin')?'mac':is('webtv')?'webtv':is('win')?'win'+(is('windows nt 6.0')?' vista':''):is('freebsd')?'freebsd':(is('x11')||is('linux'))?'linux':'','js']; c = b.join(' '); h.className += ' '+c; return c;}; css_browser_selector(navigator.userAgent);
    </script>
<script>
$(document).bind('reveal.facebox', function() { 
    $(".main").removeClass("d-none");
    $("body").remove('#facebox_overlay');
    $(".main").append('<div id="facebox_overlay" class="facebox_hide" />');  
   
})
$(document).bind('close.facebox', function() {
    $(".main").addClass("d-none");
  })
</script>
<script>
function createOptions(number) {
  var options = [], _options;

  for (var i = 0; i < number; i++) {
    var option = '<option value="' + i + '">Option ' + i + '</option>';
    options.push(option);
  }

  _options = options.join('');
  
  $('.multiselectable')[0].innerHTML = _options;
}

</script>

<script>

jQuery(function($) {

  $(document).on("mousedown", "[data-ripple]", function(e) {
    
    var $self = $(this);
    
    if($self.is(".btn-disabled")) {
      return;
    }
    if($self.closest("[data-ripple]")) {
      e.stopPropagation();
    }
    
    var initPos = $self.css("position"),
        offs = $self.offset(),
        x = e.pageX - offs.left,
        y = e.pageY - offs.top,
        dia = Math.min(this.offsetHeight, this.offsetWidth, 100), 
        $ripple = $('<div/>', {class : "ripple",appendTo : $self });
    
    if(!initPos || initPos==="static") {
      $self.css({position:"relative"});
    }
    
    $('<div/>', {
      class : "rippleWave",
      css : {
        background: $self.data("ripple"),
        width: dia,
        height: dia,
        left: x - (dia/2),
        top: y - (dia/2),
      },
      appendTo : $ripple,
      one : {
        animationend : function(){
          $ripple.remove();
        }
      }
    });
  });

});

</script>  


  <?php 


  $language_name_query=$this->common_model->get_language_list();

?>
<footer class="site-footer border_box"> 
   <div class="col text-center">
   <div class="row">
	
  <?php
 
    $this->db->select('*');
	$this->db->where('domain',$_SERVER['HTTP_HOST']);
	$result=$this->db->get('invoice_conf');
	if($result->num_rows() > 0){
		$result=$result->result_array();
		$footer = $result[0]['website_footer'];
	}else{
		$footer = '';
	}

  	 // start ASTPPENT-3818
    if($this->session->userdata['logintype'] == 2 || $this->session->userdata['logintype'] == -1 || $this->session->userdata['logintype'] == 1 || $this->session->userdata['logintype'] == 0 ){
     $user_footer = $this->session->userdata('user_footer');  
     if(isset($user_footer) && $user_footer != '') {  
      if ($user_footer == 'Inextrix Technologies Pvt. Ltd All Rights Reserved.'){
        $user_footer = '';
         } ?>
       <div  class="pull-left col-3 text-left">
       <!--  <a href="https://inextrix.com" target="_blank">
        <span class="text-warning">
            <strong><?=gettext($user_footer); ?></strong>
          </span>
      </a><br> -->
     <strong> <span class="text-warning"><?php echo gettext('ASTPP - #1 Open Source VoIP Solution<br>
      Powered by Inextrix Technologies Pvt. Ltd.<br>')?> </span></strong>
      </div>
      <?php } ?>
      <!-- // end ASTPPENT-3818 -->

		<div class="col py-2">
  	   		 <label class="text-light" style="margin-top:3px;"><i> <?php echo gettext('Follow us on:')?> </i></label>
  	   		 <div class="social-media">
  	   		  <a target="_blank" href="https://www.facebook.com/astpp.official/" title="Facebook"> <i class="facebook fa fa-facebook"></i></a>
  	   		  <a target="_blank" href="https://in.linkedin.com/company/astpp-billing" title="Linkedin"> <i class="linkin fa fa-linkedin"></i></a>
  	   		  <a target="_blank" href="https://twitter.com/astpp_official" title="Twitter"> <i class="twitter fa fa-twitter "></i></a>
  	   		 
  	   		</div>
		 </div>

	<?php	} else  if($this->session->userdata['logintype'] ==0 || $this->session->userdata['logintype'] == 1){
		
		$user_footer = $this->session->userdata('user_footer');	
		if ($user_footer != '') { 
		
			?>
     <div class="col-md-4 py-2 px-2 text-md-left text-light"><?=$user_footer ?></div>
     <div class="col-md-4 py-2 px-2 text-md-left text-light"></div>
    <?} else {  ?>
      <div class="col-md-5 py-2 px-2 text-md-left text-light">Copyright @ <?php echo date("Y"); ?> <a class="text-warning" href="http://www.inextrix.com" target="_blank"> <?php echo gettext("Inextrix Technologies Pvt. Ltd.")?></a>. <?php echo gettext("All Rights Reserved.")?>
     <div class="col-md-3 py-2 px-2 text-md-left text-light"></div>
	    
	    </div>
	    
	<? } } else{ ?>
			 <div  class="col-md-5 py-2 px-2 text-md-left">
			<span class="text-light"><?php echo gettext('Powered by ')?> </span>
			<a href="https://inextrix.com/" target="_blank">
				<span class="text-warning">
					<strong><?php echo gettext("Inextrix Technologies Pvt. Ltd.")?></strong>
				</span>
			</a>
	    </div>
		

		<div class="col-md-3 py-2 text-md-center">

  	   		 <div class="social-media">
  	   		  <a target="_blank" href="https://www.facebook.com/astppbilling" title="Facebook"> <i class="facebook fa fa-facebook"></i></a>
  	   		  <a target="_blank" href="https://in.linkedin.com/in/astpp-opensource-voip-billing-bb9301b5" title="Linkedin"> <i class="linkin fa fa-linkedin"></i></a>
  	   		  <a target="_blank" href="https://twitter.com/astppbilling" title="Twitter"> <i class="twitter fa fa-twitter "></i></a>
  	   		
  	   		</div>
		 </div>

    
    <?php } ?>
  
   <div class="col-md-4 py-2 px-2 text-md-right" id="floating-label">
     <select class="col-lg-3 col-md-6 form-control selectpicker language" data-live-search='false' data-live-search-style='begins' id="language_drp_sown" onchange="get_lang(this.value)">
  <?php

  
foreach ($language_name_query as $key => $value) {
$selected_locale = $this->session->userdata ( 'user_language');
if (isset($selected_locale) && $value['locale'] == $selected_locale) {
  $selected = 'selected="selected"';
}else {
  $selected='';
}
?>
<option title="<?php echo $value['name']; ?>" id="close-image" name="<?php echo $value['locale']; ?>" value="<?php echo $value['locale']; ?>" <?= $selected ?>><?php echo $value['name']; ?></option>
<?
} ?> 
        </select>



<div class="col-md-12 float-right pt-4 pr-0 text-light text-md-right version_size"><?php echo gettext("Version")?>  <?php echo common_model::$global_config['system_config']['version'] .' '.gettext('Community') ;?>
<?php

?>

  	</div>
	</div>
	
   </div>
   </div>
</footer>
</body>
</body>
</html>
 

