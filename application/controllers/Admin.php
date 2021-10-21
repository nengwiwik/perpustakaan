<?php
defined('BASEPATH') or exit('No direct script access allowed');

include(APPPATH . 'libraries/GroceryCrudEnterprise/autoload.php');

use GroceryCrud\Core\GroceryCrud;

class Admin extends CI_Controller
{
	public function __construct()
    {
        parent::__construct();
        is_logged_in(1);
    }

	private function _getDbData()
	{
		$db = [];
		include(APPPATH . 'config/database.php');
		return [
			'adapter' => [
				'driver' => 'Pdo_Mysql',
				'host'     => $db['default']['hostname'],
				'database' => $db['default']['database'],
				'username' => $db['default']['username'],
				'password' => $db['default']['password'],
				'charset' => 'utf8'
			]
		];
	}

	private function _getGroceryCrudEnterprise($bootstrap = true, $jquery = true)
	{
		$db = $this->_getDbData();
		$config = include(APPPATH . 'config/gcrud-enterprise.php');
		$groceryCrud = new GroceryCrud($config, $db);
		$groceryCrud->unsetSettings();
		return $groceryCrud;
	}

	function _example_output($output = null)
	{
		if (isset($output->isJSONResponse) && $output->isJSONResponse) {
			header('Content-Type: application/json; charset=utf-8');
			echo $output->output;
			exit;
		}

		$this->load->view('grocery.php', $output);
	}

	public function users()
	{
		$crud = $this->_getGroceryCrudEnterprise();
		$crud->setTable('users');
		$crud->setSubject('User', 'Users');
		$crud->unsetColumns(['role_id','created_at','password']);
		$crud->unsetAddFields(['role_id','created_at', 'updated_at']);
		$crud->unsetEditFields(['role_id','password', 'created_at', 'updated_at']);
		$crud->setRelation('role_id', 'roles', 'name');
		$crud->where(['role_id' => 2]);
		$crud->setFieldUpload('photo', 'assets/uploads', '../../assets/uploads');
		$crud->displayAs([
			'role_id' => 'Role'
		]);
		$crud->callbackBeforeInsert(function ($s) {
			// Your code here
			$s->data['role_id'] = 2;
			$s->data['password'] = password_hash($s->data['password'], PASSWORD_DEFAULT);
			return $s;
		});
		$output = $crud->render();
		$this->_example_output($output);
	}
}
