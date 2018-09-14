<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class languageController {
	function index() {
		global $db, $config;

		
		if (isset($_GET['del']) && $_GET['del'] != '') {			
			$langs = json_decode($config['multilang_country'], true);
			if (array_key_exists($_GET['del'], $langs)) {
				unset($langs[$_GET['del']]);
				$langs = json_encode($langs);

				$db->query("UPDATE ".PREFIX."configuration SET value='{$langs}' WHERE label='multilang_country' LIMIT 1");
				redirect(Url::link('configuration/language'));
			}
		}

		opentable("<i class='fa fa-flag'></i> Multilingual");
		echo "<div class='row'>";
			echo "<div class='col-sm-8'>";
				if (isset($_POST['change'])) {

					$input['multilang_enable'] = Io::GetVar("POST", "multilang_enable");
					$input['multilang_default'] = Io::GetVar("POST", "multilang_default");

					foreach($input as $key => $val) {
						$db->query("UPDATE ".PREFIX."configuration SET value='{$val}' WHERE label='{$key}'");
					}
					redirect(Url::link('configuration/language'));

				} else {
					echo Form::open(FUSION_REQUEST, ['id'=>'form-lang']);
						opentable("Settings Multilanguage <div class='pull-right'><button type='submit' form='form-lang' name='change' data-toggle='tooltip' class='btn btn-primary btn-xs' data-original-title='Change'><i class='fa fa-save'></i> Change</button></div>");

							$list_lang = json_decode($config['multilang_country'], true);

							echo "<div class='col-sm-6 form-group'>";
		                        echo "<label>Enable Multilanguage</label>";
		                        echo "<div class='input-group'>";
									$chkd = ($config['multilang_enable'] == 'on') ? 'checked="checked"' : '';
									echo "<input type='hidden' name='multilang_enable' value='off' />";
									echo "<input type='checkbox' class='minimal' name='multilang_enable' " .$chkd. " value='on' /> Enable Multilanguage ?";
		                            //echo "<input type='checkbox' name='multilang_enable' rel='tooltip' title='Check here if you want to use URL' " .$chkd. "> Enable Multilanguage ?";
		                        echo "</div>";

		                        echo "<small class='help-block'>Check this if you want to enable multilanguage</small>";
							echo "</div>";
		                    echo "<div class='col-sm-6 form-group'>";
		                        echo "<label>Default Language</label>";
		                        echo "<select name='multilang_default' class='form-control'>";
		                            foreach ($list_lang as $key => $value) {
		                                $sel = ($key == $config['multilang_default']) ? 'selected' : '';
		                                echo "<option value='{$key}' $sel>".$value['country']."</option>";
		                            }
		                        echo "</select>";
		                        echo "<small class='help-block'>Multilanguage default country. Choose one.</small>";
		                    echo "</div>";

							echo "<div class='col-sm-12 form-group'>";
								echo "<label>Available Language</label>";
								echo "<div class='row'><div class='col-sm-12'>";
									echo "<ul class='list-group'>";									
										if (count($list_lang) > 0) {
											foreach ($list_lang as $key => $value) {
												$flag = MB::strtolower($value['flag']);
												echo "<li class='list-group-item col-xs-6 col-sm-6 col-md-6'>";
													echo "<span class='flag-icon flag-icon-{$flag}'></span> ".$value['country']." (".$key.")";
													echo "<a href='".Url::link('configuration/language&amp;del='.$key)."' class='pull-right'><i class='fa fa-remove'></i></a>";
												echo "</li>";
											}
										}
									echo "</ul>";
								echo "</div>\n</div>";
							echo "</div>";
						closetable();

					echo Form::close();
				}
			echo "</div>";
			echo "<div class='col-sm-4'>";
				opentable("Add Language <div class='pull-right'><button type='submit' form='form-addlang' name='addlang' data-toggle='tooltip' class='btn btn-primary btn-xs' data-original-title='Add Lang'><i class='fa fa-save'></i> Add Lang</button></div>");

					if (isset($_POST['addlang'])) {
						$errors = array();
						$multilang_country_code = Io::GetVar("POST", "multilang_country_code");
						$multilang_country_name = Io::GetVar("POST", "multilang_country_name");
						$multilang_system_lang  = Io::GetVar("POST", "multilang_system_lang");
						$multilang_country_flag = Io::GetVar("POST", "multilang_country_flag");

						if (empty($multilang_country_name)) $errors[] = "Country Name field is required";
						if (empty($multilang_country_code)) $errors[] = "Country Code not selected";
						if (empty($multilang_country_flag)) $errors[] = "Country Flag field is required";

						if (!sizeof($errors)) {
				            $lang = array(
				                $multilang_country_code => array(
				                        'country' => $multilang_country_name,
				                        'system_lang' => $multilang_system_lang,
				                        'flag' => $multilang_country_flag,
				                    ),
				            );
				            
				            $langs = json_decode($config['multilang_country'], true);
				            $langs = array_merge((array) $langs, $lang);
				            $langs = json_encode($langs);
							$db->query("UPDATE ".PREFIX."configuration SET value='{$langs}' WHERE label='multilang_country' LIMIT 1");
							redirect(Url::link('configuration/language'));
						} else {
							debug($errors);
						}

					} else {
						echo Form::Open(FUSION_REQUEST, ['id'=>'form-addlang']);

				            echo "<div class='form-group'>";
				                echo "<label>Country Name</label>";
				               	echo "<input type='text' name='multilang_country_name' class='form-control'>";
				                echo "<small class='help-block'>Type Full country language, eg: English, Somali, Arabic, etc.</small>";
				            echo "</div>";

				            echo "<div class='form-group'>";
				                echo "<label>Country Language Code</label>";
				               	echo "<input type='text' name='multilang_country_code' class='form-control'>";
				                echo "<small class='help-block'>Set the country code, in lowecase. eg: en, so, ar, etc.</small>";
				            echo "</div>";

				            echo "<div class='form-group'>";
				                echo "<label>Country Flag</label>";
				               	echo "<select name='multilang_country_flag' class='form-control'>";
				               		echo Language::optCountry();
				               	echo "</select>";
				                echo "<small class='help-block'>Set the country flag code, in lowecase. eg: en, so, ar, etc.</small>";
				            echo "</div>";

				            echo "<div class='form-group'>";
				                echo "<label>System Language</label>";
				               	echo "<select name='multilang_system_lang' class='form-control'>";
				               		echo Language::optDropdown();
				               	echo "</select>";
				                echo "<small class='help-block'>Choose the system language for prefered language</small>";
				            echo "</div>";

					      	echo "<div class='form-group'>";
					        	echo "<button type='submit' class='btn btn-success' name='addlang'>Add Language</button>";
					      	echo "</div>";
						echo Form::close();
					}
				closetable();

			echo "</div>";
		echo "</div>";
		closetable();
	}
}