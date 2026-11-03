<?php
if(!defined( 'BASEPATH' ) ) exit( 'No direct script access allowed' );

#[\AllowDynamicProperties]
class Router {
      public static $_domain    = '';
      public function route() {
        if(isset($_SERVER["HTTP_HOST"]) && $_SERVER["HTTP_HOST"] != ''){
          require_once(BASEPATH.'database/DB.php');
          $db = DB(); // getting hold of a DAO instance
          $db->select("domain");
	
	  $db_conn = $db->conn_id;
	  $_SERVER['HTTP_HOST'] = mysqli_real_escape_string($db_conn,$_SERVER['HTTP_HOST']);
 	
          $db->like("domain",$_SERVER["HTTP_HOST"]);
          $res = $db->get("invoice_conf");
          $domain = $res->result();
          if(!empty($domain) && $domain[0]->domain != ""){
            if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on"){
                self::$_domain = "https://".$_SERVER["HTTP_HOST"]."/";
            }else{
                self::$_domain = "http://".$_SERVER["HTTP_HOST"]."/";
            }
          }else{
//Multidomain..
          $db->select("*");
          $db->where(array("package_name"=>"pbx"));
          $pbx_res = $db->get("addons");
          $pbx_res = $pbx_res->result();

          if(!empty($pbx_res) && $pbx_res[0]->package_name != ""){
		  $db->select("*");
		  $db->where(array("domain"=>$_SERVER["HTTP_HOST"]));
		  $res = $db->get("domains");
		  $cust_domain = $res->result();
		  if(!empty($cust_domain) && $cust_domain[0]->domain != ""){

		//     self::$_domain_multi = $cust_domain[0]->domain;

		    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on"){
		        self::$_domain = "https://".$cust_domain[0]->domain."/";
		    }else{
		        self::$_domain = "http://".$cust_domain[0]->domain."/";
		    }

		   }
		  }
	}
        }
      }

      public function config() {
          if(self::$_domain != ""){
	   // echo $_domain;exit;
            $RTR = load_class('Router','core');
            $RTR->config->config['base_url'] = self::$_domain;
            return true;
          }
      }
}
?>
