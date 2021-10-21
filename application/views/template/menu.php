<?php
if ($this->session->role_id == 1) {
	$this->load->view('template/menu/super');
} else if ($this->session->role_id == 2) {
	$this->load->view('template/menu/admin');
} else {	
	$this->load->view('template/menu/member');
}

?>
