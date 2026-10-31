<?php  (! defined('BASEPATH')) and exit('No direct script access allowed');
class purge extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		// Cron controller: only reachable from this server (CLI, or wget from cron_settings)
		if (! astpp_internal_request ()) {
			show_404 ();
		}
                $this->load->model('common_model');
                $this->load->library('common');
                $this->load->model('db_model');
                $this->load->model('Astpp_common');
	}
	function ProcessPurge(){

	    $currentdate = gmdate("Y-m-d H:i:s");
	    $start_date = date("Y-m-d 00:00:01",strtotime($currentdate. "-$previous_month months") -1);
		
		$get_value = $this->db_model->getselect('value,name','system',array('group_title'=>'purge'));
		$system_config=array();
		if($get_value->num_rows()>0){
			$result=$get_value->result_array();
			foreach($result as $val){
				$system_config[$val['name']]=$val['value'];
			}
			if($system_config['purge_accounts_expired']!="" && $system_config['purge_accounts_expired']!="-1" && is_numeric($system_config['purge_accounts_expired'])){
				$end_date =date("Y-m-d 00:00:00",strtotime($currentdate. "-".$system_config['purge_accounts_expired']." days") -1);
				$where ="expiry <= '".$end_date."' and expiry!='0000-00-00 00:00:00' and type!=-1";
				$query = "delete from accounts where ".$where; 
				$this->db->query($query);
			}
			if($system_config['purge_cdrs']!="" && $system_config['purge_cdrs']!="-1" && is_numeric($system_config['purge_cdrs'])){
				$end_date =date("Y-m-d 00:00:00",strtotime($currentdate. "-".$system_config['purge_cdrs']." days") -1);
				$where ="callstart <= '".$end_date."'";
				$query = "delete from cdrs where ".$where; 
				$this->db->query($query);
				$query = "delete from reseller_cdrs where ".$where; 
				$this->db->query($query);
			}
			if($system_config['purge_invoices']!="" && $system_config['purge_invoices']!="-1" && is_numeric($system_config['purge_invoices'])){
				$end_date =date("Y-m-d 00:00:00",strtotime($currentdate. "-".$system_config['purge_invoices']." days") -1);
				$where ="invoice_date <= '".$end_date."'";
				$query = "delete from invoice_details where invoiceid in( select id from invoices where ".$where.")"; 
				$this->db->query($query);
				$query = "delete from invoices where ".$where; 
				$this->db->query($query);
			}
			if($system_config['purge_emails']!="" && $system_config['purge_emails']!="-1" && is_numeric($system_config['purge_emails'])){
				$end_date =date("Y-m-d 00:00:00",strtotime($currentdate. "-".$system_config['purge_emails']." days") -1);
				$where ="date <= '".$end_date."'";
				$query = "delete from mail_details where ".$where; 
				$this->db->query($query);
			}
			if($system_config['purge_audio_log']!="" && $system_config['purge_audio_log']!="-1" && is_numeric($system_config['purge_audio_log'])){
				$end_date =date("Y-m-d 00:00:00",strtotime($currentdate. "-".$system_config['purge_audio_log']." days") -1);
				$where ="timestamp <= '".$end_date."'";
				$query = "delete from usertracking where ".$where; 
				$this->db->query($query);
			}
			if($system_config['purge_accounts_deleted']!="" && $system_config['purge_accounts_deleted']!="-1" && is_numeric($system_config['purge_accounts_deleted'])){
				$end_date =date("Y-m-d 00:00:00",strtotime($currentdate. "-".$system_config['purge_accounts_deleted']." days") -1);
				$where ="deleted_date <= '".$end_date."' and deleted_date!='0000-00-00 00:00:00' and deleted=1";

				$query = "delete from cdrs where accountid in(select id from accounts where ".$where.")"; 
				$this->db->query($query);
				$query = "delete from reseller_cdrs where accountid in(select id from accounts where ".$where.")"; 
				$this->db->query($query);
				$query = "delete from accounts where ".$where; 
				$this->db->query($query);
			}
			// ASTPPCOM-1299 Start
			if($system_config['automated_report_attachment_deleted']!="" && $system_config['automated_report_attachment_deleted']!="-1" && is_numeric($system_config['automated_report_attachment_deleted'])) {
				$where    = "purge_date < '".gmdate('Y-m-d')."'";
				$attachement_data = "select filename from automated_report_log where ".$where;
				$attachement_files = $this->db->query($attachement_data);
				$attachement_files_array = $attachement_files->result_array();
				foreach($attachement_files_array as $val) 
				{
					$path_to_file = getcwd()."/attachments/";
					$file = $path_to_file.$val['filename'];
					unlink($file);
				}
				$query    = "delete from automated_report_log where ".$where;
				$this->db->query($query);
			}
			// ASTPPCOM-1299 Start
			exit;		
		}
	}
}
?>

