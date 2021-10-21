<?php
defined('BASEPATH') or exit('No direct script access allowed');

include(APPPATH . 'libraries/GroceryCrudEnterprise/autoload.php');

use GroceryCrud\Core\GroceryCrud;

class Anggota extends CI_Controller
{
	public function __construct()
    {
        parent::__construct();
        is_logged_in(3);
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

	public function buku()
	{
		$crud = $this->_getGroceryCrudEnterprise();
		$crud->setTable('books');
		$crud->setSubject('Buku', 'Data Buku');
		$crud->columns(['title','writer','publish_year','cover']);
		$crud->unsetAdd()->unsetEdit()->unsetDelete()->unsetDeleteMultiple()->unsetDeleteSingle()->setRead();
		$crud->setFieldUpload('cover', 'assets/uploads', '../../assets/uploads');
		$crud->setTexteditor(['description']);
		$crud->fieldType('publish_year','year');
		$output = $crud->render();
		$this->_example_output($output);
	}

	public function peminjaman()
	{
		$crud = $this->_getGroceryCrudEnterprise();
		$crud->setTable('borrowings');
		$crud->setSubject('Peminjaman', 'Data Peminjaman Buku');
		$crud->unsetColumns(['user_id','created_at']);
		$crud->addFields(['start_date']);
		$crud->editFields(['start_date']);
		$crud->setRelation('user_id', 'users', 'name', ['role_id' => 3]);
		$crud->displayAs([
			'user_id' => 'Nama Anggota'
		]);
		$crud->where([
			'user_id' => $this->session->id,
		]);
		$crud->where('status in ("Create","Out")');
		$crud->setActionButton('Input Buku', 'fa fa-book', function ($row) {
			return '/peminjaman/' . $row->id;
		}, false);
		$crud->callbackBeforeInsert(function ($s) {
			$s->data['status'] = 'Create';
			$s->data['user_id'] = $this->session->id;
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
		$crud->columns(['book_id','status']);
		$crud->where([
			'borrowing_id' => $id,
			'user_id' => $this->session->id
		]);
		$crud->fields(['book_id']);
		$crud->requiredFields(['book_id']);
		$crud->setRelation('book_id', 'books', 'title');
		$crud->setRelation('borrowing_id', 'borrowings', 'user_id');
		$crud->displayAs([
			'book_id' => 'Judul Buku',
		]);
		$crud->callbackBeforeInsert(function ($s) use ($id) {
			$s->data['borrowing_id'] = $id;
			$s->data['status'] = 'Create';
			$s->data['user_id'] = $this->session->id;
			return $s;
		});
		$output = $crud->render();
		$this->_example_output($output);
	}

	public function histori_peminjaman()
	{
		$crud = $this->_getGroceryCrudEnterprise();
		$crud->setTable('borrowings');
		$crud->setSubject('Peminjaman', 'Data Peminjaman Buku');
		$crud->unsetColumns(['user_id','created_at']);
		$crud->addFields(['start_date']);
		$crud->editFields(['start_date']);
		$crud->unsetAdd()->unsetEdit()->unsetDelete()->unsetDeleteMultiple()->unsetDeleteSingle();
		$crud->setRelation('user_id', 'users', 'name', ['role_id' => 3]);
		$crud->displayAs([
			'user_id' => 'Nama Anggota'
		]);
		$crud->where([
			'user_id' => $this->session->id,
			'status' => 'In'
		]);
		$crud->setActionButton('Detail Buku', 'fa fa-book', function ($row) {
			return '/histori-peminjaman/' . $row->id;
		}, false);
		$crud->defaultOrdering('borrowings.created_at', 'desc');
		$output = $crud->render();
		$this->_example_output($output);
	}
	public function histori_peminjaman_detail($id)
	{
		$crud = $this->_getGroceryCrudEnterprise();
		$crud->setTable('borrowing_details');
		$crud->setSubject('Buku', 'Buku Pinjaman');
		$crud->columns(['book_id','status']);
		$crud->where([
			'borrowing_id' => $id,
			'user_id' => $this->session->id
		]);
		$crud->fields(['book_id']);
		$crud->requiredFields(['book_id']);
		$crud->setRelation('book_id', 'books', 'title');
		$crud->setRelation('borrowing_id', 'borrowings', 'user_id');
		$crud->displayAs([
			'book_id' => 'Judul Buku',
		]);
		$crud->unsetAdd()->unsetEdit()->unsetDelete()->unsetDeleteMultiple()->unsetDeleteSingle();
		$output = $crud->render();
		$this->_example_output($output);
	}
}
