<?php
/*
*  NEKO SIMPLE CMS v1.0.3 R1
* @ Developer: Novi
* @ Email: novhz0514@gmail.com
* @ Github: github.com/novhex
* @ Copyright (c) 2015-2016
* @ License MIT
*/

defined('BASEPATH') or exit('Error!');

class Category_model extends CI_Model{
	public function __construct(){
		parent::__construct();
		$this->load->database();
	}

	public function add_category($data){
		return $this->db->insert('categories',$data);
	}

	public function get_all_categories(){
		return $this->db->get('categories')->result_array();
	}
}