<?php
defined('BASEPATH') or exit('No direct script access allowed');

include(APPPATH . 'libraries/GroceryCrudEnterprise/autoload.php');

use GroceryCrud\Core\GroceryCrud;

class Librarian extends CI_Controller
{
	public function __construct()
    {
        parent::__construct();
        is_logged_in(2);
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
		$crud->setSubject('Anggota', 'Data Anggota');
		$crud->unsetColumns(['role_id','created_at','password']);
		$crud->unsetAddFields(['role_id','created_at', 'updated_at']);
		$crud->unsetEditFields(['role_id','password', 'created_at', 'updated_at']);
		$crud->setRelation('role_id', 'roles', 'name');
		$crud->where(['role_id' => 3]);
		$crud->setFieldUpload('photo', 'assets/uploads', '../../assets/uploads');
		$crud->displayAs([
			'role_id' => 'Role'
		]);
		$crud->callbackBeforeInsert(function ($s) {
			$s->data['role_id'] = 3;
			$s->data['password'] = password_hash($s->data['password'], PASSWORD_DEFAULT);
			return $s;
		});
		$output = $crud->render();
		$this->_example_output($output);
	}

	public function buku()
	{
		$crud = $this->_getGroceryCrudEnterprise();
		$crud->setTable('books');
		$crud->setSubject('Buku', 'Data Buku');
		$crud->unsetColumns(['created_at']);
		$crud->unsetFields(['created_at', 'updated_at']);
		$crud->setFieldUpload('cover', 'assets/uploads', '../../assets/uploads');
		$crud->setTexteditor(['description']);
		$crud->fieldType('publish_year','year');
		$crud->requiredFields(['title','writer','publisher','publish_year','description','cover']);
		$output = $crud->render();
		$this->_example_output($output);
	}

	public function peminjaman()
	{
		$crud = $this->_getGroceryCrudEnterprise();
		$crud->setTable('borrowings');
		$crud->setSubject('Peminjaman', 'Data Peminjaman Buku');
		$crud->unsetColumns(['created_at']);
		$crud->addFields(['user_id','start_date','end_date']);
		$crud->editFields(['user_id','start_date','end_date','status']);
		$crud->setRelation('user_id', 'users', 'name', ['role_id' => 3]);
		$crud->displayAs([
			'user_id' => 'Nama Anggota'
		]);
		$crud->setActionButton('Input Buku', 'fa fa-book', function ($row) {
			return '/peminjaman-buku/' . $row->id;
		}, false);
		$crud->callbackBeforeInsert(function ($s) {
			$s->data['status'] = 'Create';
			return $s;
		});
		$crud->defaultOrdering('borrowings.created_at', 'desc');
		$output = $crud->render();
		$this->_example_output($output);
	}

	public function peminjaman_detail($id)
	{
		$crud = $this->_getGroceryCrudEnterprise();
		$crud->setTable('borrowing_details');
		$crud->setSubject('Buku', 'Buku Pinjaman');
		$crud->unsetColumns(['borrowing_id','created_at']);
		$crud->where(['borrowing_id' => $id]);
		$crud->addFields(['book_id']);
		$crud->editFields(['book_id', 'status']);
		$crud->requiredFields(['book_id']);
		$crud->setRelation('book_id', 'books', 'title');
		$crud->displayAs([
			'book_id' => 'Judul Buku'
		]);
		$crud->callbackBeforeInsert(function ($s) use ($id) {
			$s->data['borrowing_id'] = $id;
			return $s;
		});
		$output = $crud->render();
		$this->_example_output($output);
	}
}
