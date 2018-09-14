<?php


class loginController {
	function index() {
		global $User;

		if($User->IsUser() === true) {
			echo $User->GetInfo('email');
		} else {
			opentable("Login Form");
			echo Form::Open();
				echo Form::AddElement(array('element'=>'text', 'name'=>'username', 'label'=>'Username', 'placeholder'=>'Username'));

				echo Form::AddElement(array('element'=>'text', 'password'=>'', 'name'=>'password', 'label'=>'Password', 'placeholder'=>'Password'));

				echo Form::AddElement(array('element'=>'checkbox', 'name'=>'remember', 'checked'=>true, 'value'=>1, 'label'=>'Remember Me'));

				echo Form::AddElement(array('element'=>'submit', 'name'=>'login', 'value'=>'Login'));

			echo Form::Close();
			closetable();
		}
	}
}