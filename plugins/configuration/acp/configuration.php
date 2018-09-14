<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class configurationController {
	function index() {
		global $db, $config;

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$input['site_name'] = Io::GetVar("POST", "site_name");
			$input['site_url'] = Io::GetVar("POST", "site_url");

			foreach($input as $key => $val) {
				$db->query("UPDATE ".PREFIX."configuration SET label='{$key}', value='{$val}'");
				redirect(Url::link('configuration/configuration'));
			}
			
		} else {
			echo Form::open(FUSION_REQUEST, ['id'=>'form-conf']);
				opentable("<button type='submit' form='form-conf' data-toggle='tooltip' class='btn btn-primary btn-xs' data-original-title='Change'><i class='fa fa-save'></i> Change</button>\n");
		            echo "<ul class='nav nav-tabs' role='tablist'>";
		                echo "<li class='active'><a href='#general' data-target='#general' role='tab' data-toggle='tab'>General</a></li>";
		                echo "<li><a href='#localization' data-target='#localization' role='tab' data-toggle='tab'>Localization</a></li>";
		            echo "</ul>";

		            echo "<div class='tab-content'>";
		            	echo "<div class='tab-pane active' id='general'>";
		            		echo "<div class='col-sm-12'>";
		                        echo "<div class='row'>";
		                            echo "<div class='col-sm-6 form-group'>";
		                                echo "<label>Website Name</label>";
		                                echo "<input type='text' name='site_name' value='".$config['site_name']."' class='form-control'>";
		                                echo "<small class='help-block'>Site name details</small>";
		                            echo "</div>";
		                            echo "<div class='col-sm-6 form-group'>";
		                                echo "<label>Website Url</label>";
		                                echo "<input type='text' name='site_url' value='".$config['site_url']."' class='form-control'>";
		                                echo "<small class='help-block'>Site Url detail</small>";
		                            echo "</div>";
		                        echo "</div>";
		            		echo "</div>";
		            	echo "</div>";

		            	echo "<div class='tab-pane' id='localization'>localization";

		            	echo "</div>";
		            echo "</div>";
		        closetable();
			echo Form::close();
		}
		add_to_footer("<script>$('#myTab a').on('click',function(){ $('.tab-pane').hide(); $($(this).attr('href')).show(); });</script>");
	}
}