<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class clientController {
	function index() {
		global $db;

		$id = Io::GetVar("GET", "id", "int");

		echo "<div class='col-sm-6'>";
			if(isset($_POST['submit'])){
				$input['name'] = Io::GetVar('POST','name','fullhtml');
				$input['email'] = Io::GetVar('POST', 'email', 'nohtml');
				$input['website'] = Io::GetVar('POST', 'website', 'nohtml');
				$input['message'] = Io::GetVar('POST','message','fullhtml');

				$errors = array();
				if (empty($input['name'])) $errors[] = 'Title field is empty.';

				if (!Utils::CheckToken()) {
					$errors[] = 'Error token';
				}

				if (!sizeof($errors)) {
					if(empty($id)) {
						dbquery_insert(PREFIX.'ads_client', $input, 'save');
					} else {
						dbquery_insert(PREFIX.'ads_client', $input, 'update', 'id='.intval($id));
					}
					redirect(Url::link('advertisement/client'));					
				} else {
					Error::Trigger('INFO', implode('<br />', $errors));
				}
			} else {
				if(!empty($id)) {
					$data = $db->query("SELECT * FROM ".PREFIX."ads_client WHERE id=".intval($id))->row;
				} else {
					// Data from empty
					$data = null;
				}
				opentable();
				echo Form::Open();
					echo Form::AddElement(array('element'=>'text', 'label'=>'Name', 'name'=> 'name', 'value'=> $data['name'] ));
					echo Form::AddElement(array('element'=>'text', 'label'=>'Email', 'name'=> 'email', 'value'=> $data['email'] ));
					echo Form::AddElement(array('element'=>'text', 'label'=>'Website', 'name'=> 'website', 'value'=> $data['website'] ));
					echo Form::AddElement(array('element'=>'textarea', 'label'=>'Message', 'name'=> 'message', 'value'=> $data['message'] ));

					echo Form::AddElement(array('element'=>'submit', 'name'=> 'submit', 'value'=> 'Save' ));
				echo Form::Close();
				closetable();
			}
		echo "</div>";

		echo "<div class='col-sm-6'>";
			opentable();
			$result = $db->query("SELECT * FROM ".PREFIX."ads_client ORDER BY name ASC")->rows;
			$i=0;
			echo "<table class='table-condensed table-hover'>";
			foreach($result as $row) {
				$class = (($i++%2)!=0) ? "odd" : "even";
				echo "<tr class='$class'>";
					echo "<td>{$row['name']}</td>";
					echo "<td width='1%' align='right' nowrap>";
						echo "<a href='".Url::link('advertisement/client&amp;id='.intval($row['id']))."' class='btn btn-primary btn-xs'><i class='fa fa-pencil'></i></a> ";
						echo "<a href='".Url::link('advertisement/client&amp;delete='.intval($row['id']))."' class='btn btn-danger btn-xs'><i class='fa fa-remove'></i></a>";
					echo "</td>";
				echo "</tr>";
			}
			echo "</table>";
			closetable();
		echo "</div>";

		if(isset($_GET['delete'])) {
			$data = $db->query("SELECT * FROM ".PREFIX."ads_client WHERE id=".intval($_GET['delete']))->row;
			debug($data);
		}
	}
}