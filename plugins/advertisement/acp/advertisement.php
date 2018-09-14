<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class advertisementController {
	function index() {
		global $db;

		$id = Io::GetVar("GET", "id", "int");

		echo "<div class='col-sm-6'>";
			if(isset($_POST['submit'])){
				$input['name'] = Io::GetVar('POST','name','fullhtml');
				$input['client'] = Io::GetVar('POST', 'client', 'int');
				$input['status'] = Io::GetVar('POST', 'status', false, 'active');
				$input['start_date'] = Io::GetVar('POST','start_date',false,true,'2001-01-01');
				$input['end_date'] = Io::GetVar('POST','end_date',false,true,'2199-01-01');

				$errors = array();
				if (empty($input['name'])) $errors[] = 'Title field is empty.';

				if (!Utils::CheckToken()) {
					$errors[] = 'Error token';
				}

				if (!sizeof($errors)) {
					if(empty($id)) {
						dbquery_insert(PREFIX.'ads_ads', $input, 'save');
					} else {
						dbquery_insert(PREFIX.'ads_ads', $input, 'update', 'id='.intval($id));
					}
					redirect(Url::link('advertisement/advertisement'));
				} else {
					Error::Trigger('INFO', implode('<br />', $errors));
				}
			} else {
				if(!empty($id)) {
					$data = $db->query("SELECT * FROM ".PREFIX."ads_ads WHERE id=".intval($id))->row;
				} else {
					$data = null;
				}
				?>
				<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

				<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
				<script>
				$( function() {
					$( "#start_date" ).datepicker({ dateFormat: 'yy/mm/dd' });
					$( "#end_date" ).datepicker({ dateFormat: 'yy/mm/dd' });
				} );
				</script>
				<?php
				opentable();
				echo Form::Open();
					echo Form::AddElement(array('element'=>'text', 'label'=>'Title', 'name'=> 'name', 'value'=> $data['name'] ));

					$select = array();
					$result = $db->query("SELECT * FROM ".PREFIX."ads_client")->rows;
					foreach($result as $row) $select[$row['name']] = $row['id'];
					echo Form::AddElement(array('element'=>'select', 'label'=>'Client', 'name'=> 'client', 'values'=>$select, 'selected'=> $data['client'] ));
					echo Form::AddElement(array('element'=>'select', 'label'=>'Status', 'name'=> 'status', 'values'=>array('Active'=>'active'), 'selected'=> $data['status'] ));
					echo Form::AddElement(array('element'=>'text', 'label'=>'Start Date', 'name'=> 'start_date', 'value'=> $data['start_date'], 'id'=>'start_date' ));
					echo Form::AddElement(array('element'=>'text', 'label'=>'End Date', 'name'=> 'end_date', 'value'=> $data['end_date'], 'id'=>'end_date' ));

					echo Form::AddElement(array('element'=>'submit', 'name'=> 'submit', 'value'=> 'Save' ));
				echo Form::Close();
				closetable();
			}
		echo "</div>";

		echo "<div class='col-sm-6'>";
		opentable();
		$result = $db->query("SELECT * FROM ".PREFIX."ads_ads ORDER BY name ASC")->rows;
		echo "<table class='table-condensed table-hover'>";
		$i=0;
		foreach($result as $row) {
			$class = (($i++%2)!=0) ? "odd" : "even";
			echo "<tr class='$class'>";
				echo "<td>{$row['name']}</td>";
				echo "<td width='1%' align='right' nowrap>";
					echo "<a href='".Url::link('advertisement/advertisement&amp;id='.intval($row['id']))."' class='btn btn-primary btn-xs'><i class='fa fa-pencil'></i></a> ";
					echo "<a href='".Url::link('advertisement/advertisement&amp;delete='.intval($row['id']))."' class='btn btn-danger btn-xs'><i class='fa fa-remove'></i></a>";
				echo "</td>";
			echo "</tr>";
		}
		echo "</table>";
		closetable();
		echo "</div>";


		echo "<div class='col-sm-12'>";
		opentable();
			$today = date('Y-m-d');
			$noticedate = date('Y-m-d', strtotime($today." + 1 days" )); //Delay BEFORE payment is due
			$latedate = date('Y-m-d', strtotime($today." - 1 days" )); // Delay AFTER payment is late

			if($result = $db->query("SELECT start_date,end_date,
					a.id ad_id,a.name ad_name,a.client ad_client,c.id client_id,c.name client_name,c.email client_email,c.website client_website,c.message client_note, c.options
					FROM ".PREFIX."ads_ads AS a JOIN ".PREFIX."ads_client AS c ON a.client=c.id 
					WHERE end_date='$noticedate' OR end_date='$latedate'")){
				$data = array();
				foreach($result->rows as $row) {
					$data[$row['ad_id']]['end_date'] = $row;
				}

				$index = array();
				foreach($data as $id => $val) {
					$key = (in_array($today,$data[$id]['end_date']) ? array_search($today,$data[$id]['end_date']) : 0);

					//Payment due Today
					if(in_array($today,$data[$id]['end_date']) && $data[$id]['end_date'][$key] == 0){
						$index['ad_name']	  = "Payment due Today for ".$data[$id]['end_date']['ad_name'];
						$index['ad_due_date'] = $data[$id]['end_date']['end_date'];
						$index['client_ref']  = $data[$id]['end_date']['ad_id'];
						$index['client_name'] = $data[$id]['end_date']['client_name'];
						$index['client_mail'] = $data[$id]['end_date']['client_email'];
						$index['client_note'] = "This is a courtesy reminder that your payment is due today.<br />
				        Please make your payment as soon as possible.";						
				    //Payment Reminder
				    }elseif(in_array($noticedate,$data[$id]['end_date'])){
					    $index['ad_name']	  = "Payment Reminder for ".$data[$id]['end_date']['ad_name'];
					    $index['ad_due_date'] = $data[$id]['end_date']['end_date'];
						$index['client_ref']  = $data[$id]['end_date']['ad_id'];
						$index['client_name'] = $data[$id]['end_date']['client_name'];
						$index['client_mail'] = $data[$id]['end_date']['client_email'];
						$index['client_note'] = Io::Output($data[$id]['end_date']['client_note']);						
				    //Late Payment Reminder
				    }elseif(in_array($latedate,$data[$id]['end_date'])){
					    $index['ad_name']	  = "Late Payment Reminder for ".$data[$id]['end_date']['ad_name'];
					    $index['ad_due_date'] = $data[$id]['end_date']['end_date'];
						$index['client_ref']  = $data[$id]['end_date']['ad_id'];
						$index['client_name'] = $data[$id]['end_date']['client_name'];
						$index['client_mail'] = $data[$id]['end_date']['client_email'];
						$index['client_note'] = "This is a courtesy reminder that your payment is Past Due.  It was due to be paid ". $index['ad_due_date'] . ".<br />
				        Please make your payment as soon as possible.";						
				    }

				    addNotice('danger', $index);
				    debug($index);
					//$headers = 'From: afaaro@outlook.com' . "\r\n" . 'Reply-To: afaaro@outlook.com' . "\r\n" .'X-Mailer: PHP/' . phpversion();
				    // $sendEmail = sendMail($index['client_mail'], $index['ad_name'], $index['client_note'], "Ahmed", "afaaro@outlook.com",1);
				}

				
			}
		closetable();
		echo "</div>";
	}
}