<?php
#[\AllowDynamicProperties]
class Demo extends MX_Controller {
	function __construct() {
		parent::__construct ();
		if ($this->session->userdata ( 'user_login' ) == FALSE)
			redirect ( base_url () . '/astpp/login' );
	}
	function demo_list(){
		$this->load->view ( 'view_demo', "" );
	}
}
?>
