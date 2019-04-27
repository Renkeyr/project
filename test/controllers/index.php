<?php
Class Controller_Index Extends Controller_Base {
public $layouts = "first_layouts";
function index() {
	$model = new Model_Users();
	$userInfo = $model->getUser();
	$this->template->vars('userInfo', $userInfo);
	$this->template->view('index');
	}
	}