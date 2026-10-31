</section>
</span></span>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.dlmenu.js"></script>
<script>
	$(document).ready(function() {
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
   
   $('.form-control').on('focus blur', function (e) {
        $(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
  }).trigger('blur');

  $('.multiselectable').on('focus blur', function (e) {
        $(this).parents('.form-group').toggleClass('focused');
  }).trigger('blur');

    $("input[type='hidden']").parents('li.form-group').addClass("d-none");
  
  $('.selectpicker').selectpicker();
  
  $("button, a, .btn").attr("data-ripple"," "); 
   $("textarea").parents('li.form-group').addClass("h-auto");
   $(".fileinput-new").parents('span.btn').css("position","relative");
      
  $(".language").on('change',function(){
	var language=$(".language").val();
	get_lang(language);
  });

  $("no-ripple").removeAttr("data-ripple"," "); 
  
});
</script>
 <script>
 jQuery(document).ready(function($) {
  $('a[rel*=facebox]').facebox()
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
        dia = Math.min(this.offsetHeight, this.offsetWidth, 100), // start diameter
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
  	if($footer != '' && ($this->session->userdata['logintype'] == 2 || $this->session->userdata['logintype'] ==-1)){ ?>
			 <div  class="pull-left col-3">
			<span><?php echo gettext("Powered by")?> </span>
			<a href="http://www.astppbilling.org" target="_blank">
				<span style="color: #216397;text-shadow: 0px 1px 1px #FFF;">
					<strong>ASTPP</strong>
				</span>
			</a>
	    </div>
		
		<div class="col py-2">
  	   		 <label class="text-light" style="margin-top:3px;"><i> <?php echo gettext('Follow us on:')?> </i></label>
  	   		 <div class="social-media">
  	   		  <a target="_blank" href="https://www.facebook.com/astpp.official/" title="Facebook"> <i class="facebook fa fa-facebook"></i></a>
            <a target="_blank" href="https://in.linkedin.com/company/astpp-billing" title="Linkedin"> <i class="linkin fa fa-linkedin"></i></a>
            <a target="_blank" href="https://twitter.com/astpp_official" title="Twitter"> <i class="twitter fa fa-twitter "></i></a>
  	   		
  	   		</div>
		 </div>

	<span class="pull-right pr-4 pl-3"> <?php echo gettext('Version')?>  <?php echo common_model::$global_config['system_config']['version']; ?>  &nbsp; 
	</span>
	<?php	} else  if($this->session->userdata['logintype'] ==0 || $this->session->userdata['logintype'] == 1){
		
		$user_footer = $this->session->userdata('user_footer');	
		if ($user_footer != '') { 
		
			?>
		 <div class="col-md-offset-4 col-md-4"><?=$user_footer ?> </div>
		<?} else {  ?>
	    <div class="col-md-offset-4 col-md-4">Copyright @ <?php echo date("Y"); ?> <a style="color:#3989c0;" href="http://www.inextrix.com" target="_blank"> Inextrix Technologies Pvt. Ltd</a>. All Rights Reserved.
	    
	    </div>
	    
	<? } } else{ ?>
			 <div  class="col-md-4 py-2 px-2 text-md-left">
			<span class="text-light"><?php echo gettext('Powered by')?> </span>
			<a href="http://www.astppbilling.org" target="_blank">
				<span class="text-warning">
					<strong>ASTPP</strong>
				</span>
			</a>
	    </div>
		

		<div class="col-md-4 py-2 text-md-center">
  	   		 <div class="social-media">
  	   		  <a target="_blank" href="https://www.facebook.com/astpp.official/" title="Facebook"> <i class="facebook fa fa-facebook"></i></a>
            <a target="_blank" href="https://in.linkedin.com/company/astpp-billing" title="Linkedin"> <i class="linkin fa fa-linkedin"></i></a>
            <a target="_blank" href="https://twitter.com/astpp_official" title="Twitter"> <i class="twitter fa fa-twitter "></i></a>
  	   		
  	   		</div>
		 </div>

    
    <?php } ?>
  
   <div class="col-md-4 py-2 px-2 text-md-right" id="floating-label">
    <select class="col-lg-3 col-md-6 form-control selectpicker language" data-live-search='false' data-live-search-style='begins'>
      <option title="English" id="close-image" name="en_EN" value="en_EN">English</option>
      <option title="Español" id="close-image" name="es_ES" value="es_ES">Spanish</option>
      <option title="Français" id="close-image" name="fr_FR" value="fr_FR">French</option>
      <option title="Português" id="close-image" name="pt_BR" value="pt_BR">Portuguese</option>

    </select>
       
  	<div class="col-md-12 float-right pt-4 pr-0 text-light text-md-right version_size"> Version  <?php echo common_model::$global_config['system_config']['version']; ?>  

  	</div>
	</div>
	
   </div>
   </div>
</footer>
</body>
</body>
</html>
 

