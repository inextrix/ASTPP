<?php  if ( ! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

function create_csv_string($data) {
	if (!$fp = fopen('php://temp', 'w+')) {
		return FALSE;
	}
	foreach ($data as $line) {
		fputcsv($fp, $line);
	}
	rewind($fp);
	return stream_get_contents($fp);
}

function send_csv_mail ($csvData, $body, $to, $subject,$file_name) {
	//print_r($csvData);
	$where = array('group_title' =>'email');
		$query = $this->CI->db_model->getSelect("*", "system", $where);
		$query = $query->result_array();
	foreach($query as $key=>$val){
		$from=$val['value'];
	}
	$multipartSep = '-----'.md5(time()).'-----';
	$headers = array(
	"From: $from",
	"Reply-To: $from",
	"Content-Type: multipart/mixed; boundary=\"$multipartSep\""
	);
	$attachment = chunk_split(base64_encode(create_csv_string($csvData)));
	$body = "--$multipartSep\r\n"
	. "Content-Type: text/plain; charset=ISO-8859-1; format=flowed\r\n"
	. "Content-Transfer-Encoding: 7bit\r\n"
	. "\r\n"
	. "$body\r\n"
	. "--$multipartSep\r\n"
	. "Content-Type: text/csv\r\n"
	. "Content-Transfer-Encoding: base64\r\n"
	. "Content-Disposition: attachment; filename=\"$file_name\"\r\n"
	. "\r\n"
	. "$attachment\r\n"
	. "--$multipartSep--";
	mail($to, $subject, $body, astpp_implode("\r\n", $headers));
}

?>
