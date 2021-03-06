<?php
defined('BASEPATH') or exit('Error!');


/*
*  NEKO SIMPLE CMS v1.0.3 R1
* @ Developer: Novi
* @ Email: novhz0514@gmail.com
* @ Github: github.com/novhex
* @ Copyright (c) 2015-2016
* @ License MIT
*/
class Admin extends CI_Controller{
	

    private $file ;
	private $dir_path="";
	private $project_url;
	
	public function __construct(){
		# code...
		parent::__construct();
		$this->dir_path='./images/';
		$this->load->library('session');
		$this->load->helper(array('url','adminvalidation','form'));
		$this->load->library(array('adminlib','twitterbootstrap','form_validation','session','pagination'));
		$this->load->model(array('users_model','page_model','category_model','blog_model'));
		
		
	}
	

	public function index(){

		if($this->session->userdata('site_user')==''){

		$this->form_validation->set_rules(login_validators());
		$this->form_validation->set_error_delimiters("<p style='color:red;'>* ","</p>");

		if($this->form_validation->run()==FALSE){

			$data['page_title'] = 'Admin Login';
			$data['css_files'] = $this->twitterbootstrap->load_css_files();
			$data['js_files'] = $this->twitterbootstrap->load_js_files();
			$data['message_count']=$this->page_model->unread_message_count();
			$this->load->view('admin_login',$data);
			
		}else{
			$response = $this->users_model->_authenticate($this->input->post('txtusername',TRUE), $this->input->post('txtpassword',TRUE));
			
			if($response==1){
				$this->session->set_userdata('site_user',$this->input->post('txtusername',TRUE));
				$this->session->set_userdata('site_user_role',$this->users_model->_getUserRole($this->input->post('txtusername',TRUE)));
				$this->session->set_userdata('site_user_id',$this->users_model->_getUserID($this->input->post('txtusername',TRUE)));
				$this->users_model->_lastLogged($this->input->post('txtusername',TRUE));
				redirect(base_url('admin/dashboard'));
			}else{
				 $this->session->set_flashdata('auth_error','Invalid Username or Password');
				 redirect(base_url('admin'));
			}
		}
		}else{
			redirect(base_url('admin/dashboard'));
		}

	}



	public function add_category(){

		$this->form_validation->set_rules('page_category','Category Name','trim|required|min_length[4]|max_length[255]|is_unique[categories.category_name]');
		$this->form_validation->set_rules('page_list','Parent Page','trim|required');
		$this->form_validation->set_error_delimiters("<p style='color:red;'>* ","</p>");
		
		if($this->form_validation->run()==FALSE){

			$data['css_files'] = $this->twitterbootstrap->load_css_files();
			$data['js_files'] = $this->twitterbootstrap->load_js_files();
			$data['parent_pages'] = $this->page_model->_getPagesData('pages',NULL);
			$data['message_count']=$this->page_model->unread_message_count();
			$data['page_title'] = 'Add Category';
			$this->load->view('tpl/head',$data);
			$this->load->view('admin-addcategory');
			$this->load->view('tpl/footer',$data);
		}else{
			$response =$this->category_model->add_category(
				array('category_name'=>ucwords($this->input->post('page_category',TRUE)),
				'category_slug'=>url_title($this->input->post('page_category'), 'dash', TRUE),
				'parent_page'=> $this->input->post('page_list',TRUE)
				));
			if($response){
				$this->session->set_flashdata('addcategory_ok','Category added successfully.');
				redirect(base_url('admin/add-category'));
			}
		}

	}


	public function add_page_category(){

		$this->form_validation->set_rules('page_category_name','Page Category Name','trim|required|max_length[100]|min_length[3]');
		$this->form_validation->set_rules('cb_parentpage','Parent Page','trim|required');
		$this->form_validation->set_error_delimiters("<p style='color:red;'>* ","</p>");

		if($this->form_validation->run()==FALSE){
		
		$data['css_files'] = $this->twitterbootstrap->load_css_files();
		$data['js_files'] = $this->twitterbootstrap->load_js_files();
		$data['message_count']=$this->page_model->unread_message_count();
		$data['page_title'] = 'Add Page Category';
		$data['parent_pages'] = $this->page_model->_getPagesData('pages',NULL);
		$this->load->view('tpl/head',$data);
		$this->load->view('tpl/navbar');
		$this->load->view('admin_addpagecategory',$data);	
		$this->load->view('tpl/footer',$data);

		}else{

			$response = $this->page_model->add_page_category(
				array(
					'category_slug'=>url_title($this->input->post('page_category_name'), 'dash', TRUE),
					'category_name'=>$this->input->post('page_category_name'),
					'parent_page'=>$this->input->post('cb_parentpage')
					));
			if($response==1){
				$this->session->set_flashdata('addpagecategory_ok','Page Category Successfully Added');
				redirect(base_url('admin/add-page-category'));
			}

		}


	}
	



	public function add_user(){
		$this->form_validation->set_rules(add_username_validators());
		$this->form_validation->set_error_delimiters("<p style='color:red;'>* ","</p>");

		if($this->form_validation->run()==FALSE){

		$data['css_files'] = $this->twitterbootstrap->load_css_files();
		$data['js_files'] = $this->twitterbootstrap->load_js_files();
		$data['message_count']=$this->page_model->unread_message_count();
		$data['page_title'] = 'Add User';
		$this->load->view('tpl/head',$data);
		$this->load->view('tpl/navbar');
		$this->load->view('admin_adduser',$data);
		$this->load->view('tpl/footer',$data);

	  }else{

	  		$response =  $this->users_model->_addUsersData(

	  			array(
	  				'usrs_username'		=> strtolower($this->input->post('txt_username',TRUE)),
	  				'usrs_full_name'	=> $this->input->post('txt_user_fullname',TRUE),
	  				'usrs_pw'			=> $this->adminlib->hashPassword($this->input->post('txt_user_password',TRUE)),
	  				'usrs_email'		=> strtolower($this->input->post('txt_user_mail',TRUE)),
	  				'usrs_role'			=> $this->input->post('usr_role',TRUE),
	  				'usrs_date_added'	=> date('Y-m-d'),
	  				
	  			)
	  		 );

	  		if($response==1){
	  			redirect(base_url('admin/user-list'));
	  		}

	  }
	}





	public function blog_post(){
		
           $offset=0;
           $config['total_rows'] =  $this->blog_model->countpost_from_category($this->session->userdata('site_user_id'),$this->session->userdata('site_user_role'));
			$data['css_files'] = $this->twitterbootstrap->load_css_files();
			$data['js_files'] = $this->twitterbootstrap->load_js_files();
			$data['message_count']=$this->page_model->unread_message_count();
			$data['page_title'] = 'Blog Post(s)';
			$data['user_posts'] =$this->blog_model->user_blogs($this->session->userdata('site_user_id'),$this->session->userdata('site_user_role'),$offset,$config['total_rows']);
			$this->load->view('tpl/head',$data);
			$this->load->view('tpl/navbar');
			$this->load->view('admin-viewpost',$data);
			$this->load->view('tpl/footer',$data);

	}


	public function categories(){

		$data['css_files'] = $this->twitterbootstrap->load_css_files();
		$data['js_files'] = $this->twitterbootstrap->load_js_files();
		$data['message_count']=$this->page_model->unread_message_count();
		$data['page_title'] = 'Categories';
		$data['categories']= $this->category_model->get_all_categories();
		$this->load->view('tpl/head',$data);
		$this->load->view('tpl/navbar');
		$this->load->view('admin_pagecategories',$data);
		$this->load->view('tpl/footer',$data);

	}
	
	//EDIT CATEGORY 
	public function edit_category($category){
		$this->form_validation->set_rules('txt_category','Page Category','required|min_length[4]|trim|max_length[45]');
		$this->form_validation->set_error_delimiters("<p style='color:red;'>* ","</p>");

		if($this->form_validation->run()===FALSE){
			$data['css_files'] = $this->twitterbootstrap->load_css_files();
			$data['js_files'] = $this->twitterbootstrap->load_js_files();
			$data['message_count']=$this->page_model->unread_message_count();
            $data['page_title'] = 'Edit Page Category';
			$data['category_to_edit']= $this->category_model->get_category($category);
            $this->load->view('tpl/head',$data);
			$this->load->view('tpl/navbar');
            $this->load->view('admin_edit_category',$data);
            $this->load->view('tpl/footer',$data);
		}
		else{

			$response = $this->category_model->update_category(array('category_name'=>$this->input->post('txt_category',TRUE),'category_slug'=>url_title($this->input->post('txt_category'), 'dash', TRUE)),$category);

			if($response==1){
				redirect(base_url('admin/categories'));
			}

		}
		
	}
	//END



	public function dashboard(){
			
			$data['css_files'] = $this->twitterbootstrap->load_css_files();
			$data['js_files'] = $this->twitterbootstrap->load_js_files();
			$data['message_count']=$this->page_model->unread_message_count();
			$data['page_title'] = 'Dashboard';
			$this->load->view('tpl/head',$data);
			$this->load->view('tpl/navbar');
			$this->load->view('admin_dashboard',$data);
			$this->load->view('tpl/footer',$data);

	}



	public function edit_blog($slug){

			$this->form_validation->set_rules('title','Title','trim|required|min_length[6]|max_length[255]');
			$this->form_validation->set_rules('content','Content','trim|required|min_length[6]');
			$this->form_validation->set_error_delimiters("<p style='color:red;'>* ","</p>");

			if($this->form_validation->run()==FALSE){

			$data['css_files'] = $this->twitterbootstrap->load_css_files();
			$data['js_files'] = $this->twitterbootstrap->load_js_files();
			$data['message_count']=$this->page_model->unread_message_count();
			$data['page_title'] = 'Edit Blog';
			$data['blog']=$this->blog_model->get_blog($slug);
			$this->load->view('tpl/head',$data);
			$this->load->view('tpl/navbar');
			$this->load->view('admin_editblog',$data);
			$this->load->view('tpl/footer',$data);

		}else{

			$response = $this->blog_model->update_blog(array('title'=>$this->input->post('title',TRUE),'content'=>$this->input->post('content'),'slug'=>url_title($this->input->post('title'), 'dash', TRUE)),$slug);

			if($response==1){
				redirect(base_url('admin/blog-post'));
			}

		}

	}

	public function edit_frontend_html($tpl_to_edit=NULL){
			
			
			$this->form_validation->set_rules('phpcontent','Content','required');


			if($this->form_validation->run()==FALSE){
				
			if($tpl_to_edit==NULL){

				$data['file_contents'] = file_get_contents(APPPATH.'modules/home/views/tpl/head.php');
				$this->file = APPPATH.'modules/home/views/tpl/head.php'; 	


			}else if($tpl_to_edit=='navbar'){
				
				$data['file_contents'] = file_get_contents(APPPATH.'modules/home/views/tpl/navbar.php');
				$this->file = APPPATH.'modules/home/views/tpl/navbar.php';	
		
			}else if($tpl_to_edit=='footer'){
				
				$data['file_contents'] = file_get_contents(APPPATH.'modules/home/views/tpl/footer.php');
				$this->file = APPPATH.'modules/home/views/tpl/footer.php';

			}else if($tpl_to_edit=='article'){
				
				$data['file_contents'] = file_get_contents(APPPATH.'modules/home/views/article.php');
				$this->file = APPPATH.'modules/home/views/article.php';	
			}
			else if($tpl_to_edit=='categorypost'){
				
				$data['file_contents'] = file_get_contents(APPPATH.'modules/home/views/category-post.php');	
				$this->file = APPPATH.'modules/home/views/category-post.php';
			}
			else if($tpl_to_edit=='home'){
				
				$data['file_contents'] = file_get_contents(APPPATH.'modules/home/views/home.php');	
				$this->file = APPPATH.'modules/home/views/home.php';
			}


				$data['css_files'] = $this->twitterbootstrap->load_css_files();
				$data['js_files'] = $this->twitterbootstrap->load_js_files();
				$data['message_count']=$this->page_model->unread_message_count();
				$data['page_title'] = 'Edit Front End HTML';
				$data['file_to_edit'] = $this->file;
				$this->load->view('tpl/head',$data);
				$this->load->view('tpl/navbar');
				$this->load->view('admin_editfrontend',$data);
				$this->load->view('tpl/footer',$data);

			}else{

				$file_handler = fopen($this->input->post('filetoedit'),'w') or die('Fuckin Error!');

				fwrite($file_handler, $this->input->post('phpcontent'));

				fclose($file_handler);

				$x=1;

				if($x==1){
					$this->session->set_flashdata('frontend_edit_ok','File Successfully Edited');
					redirect(base_url('admin/edit-frontend-html').'/'.$this->file);
				}

			}


		 
	}

		public function edit_page($page){
		$this->form_validation->set_rules('txt_pagetitle','Page Name','required|min_length[4]|trim|max_length[45]');
		$this->form_validation->set_error_delimiters("<p style='color:red;'>* ","</p>");

		if($this->form_validation->run()===FALSE){
			$data['css_files'] = $this->twitterbootstrap->load_css_files();
			$data['js_files'] = $this->twitterbootstrap->load_js_files();
			$data['message_count']=$this->page_model->unread_message_count();
            $data['page_title'] = 'Edit Page';
			$data['page_to_edit']= $this->page_model->get_page($page);
            $this->load->view('tpl/head',$data);
			$this->load->view('tpl/navbar');
            $this->load->view('admin_edit_page',$data);
            $this->load->view('tpl/footer',$data);
		}
		else{

			$response = $this->page_model->update_page(array('page_name'=>$this->input->post('txt_pagetitle',TRUE),'page_slug'=>url_title($this->input->post('txt_pagetitle'), 'dash', TRUE)),$page);

			if($response==1){
				redirect(base_url('admin/parent-pages'));
			}

		}
		
	}

	public function edit_user($user_id){
		$response =0;

			$validators = array(
				array(
					'label'=>'Full Name',
					'field'=>'txt_user_fullname',
					'rules'=>'trim|required|min_length[2]|max_length[100]'
					),
				
				array(
					'label'=>'Username',
					'field'=>'txt_username',
					'rules'=>'trim|required|min_length[6]|max_length[20]',
					),

				array(
					'label'=>'Email',
					'field'=>'txt_user_mail',
					'rules'=>'trim|required|valid_email',
					)
		);


			if($this->session->userdata('site_user_role')!='admin'){
				if($user_id != $this->session->userdata('site_user_id')){
					echo 'You cannot edit other users account!';
				}else{


			if($this->session->userdata('site_user_role')=='admin'){
				array_push($validators,array(
						'label'=>'User Role',
						'field'=>'usr_role',
						'rules'=>'trim|required',
						));
			}

			if(strlen($this->input->post('txt_user_password'))>0){
				array_push($validators, 
				array(
						'label'=>'Password',
						'field'=>'txt_user_password',
						'rules'=>'trim|required|min_length[6]',
						),

					array(
						'label'=>'Password Confirmation',
						'field'=>'txt_user_password_cf',
						'rules'=>'trim|required|matches[txt_user_password]',
						)
				);
			}	
				$this->form_validation->set_rules($validators);
				$this->form_validation->set_error_delimiters("<p style='color:red;'>* ","</p>");

				if($this->form_validation->run()==FALSE){

					$data['css_files'] = $this->twitterbootstrap->load_css_files();
					$data['js_files'] = $this->twitterbootstrap->load_js_files();
					$data['message_count']=$this->page_model->unread_message_count();
					$data['user_info'] = $this->users_model->_getUsersData('users',array(array('field'=>'usrs_ID','parameter'=>$user_id)));
					$data['page_title'] = 'Edit User';
					$this->load->view('tpl/head',$data);
					$this->load->view('tpl/navbar');
					$this->load->view('admin_edituser',$data);
					$this->load->view('tpl/footer',$data);
				}else{

				if(strlen($this->input->post('txt_user_password'))>0){

					$response =$this->users_model->_updateUserDetails(
						array(
							'usrs_username'=>$this->input->post('txt_username',TRUE),
							'usrs_full_name'=>$this->input->post('txt_user_fullname',TRUE),
							'usrs_email'=>$this->input->post('txt_user_mail',TRUE),
							'usrs_pw'=>$this->adminlib->hashPassword($this->input->post('txt_user_password',TRUE)),
							'usrs_role'=>$this->session->userdata('site_user_role')
							),
						$user_id
						);
					}else{

					$response =$this->users_model->_updateUserDetails(
						array(
							'usrs_username'=>$this->input->post('txt_username',TRUE),
							'usrs_full_name'=>$this->input->post('txt_user_fullname',TRUE),
							'usrs_email'=>$this->input->post('txt_user_mail',TRUE),
							'usrs_role'=>$this->session->userdata('site_user_role')
							),
						$user_id
						);
					}
				}

			}
		}else{


			if($this->session->userdata('site_user_role')=='admin'){
				array_push($validators,array(
						'label'=>'User Role',
						'field'=>'usr_role',
						'rules'=>'trim|required',
						));
			}

			if(strlen($this->input->post('txt_user_password'))>0){
				array_push($validators, 
				array(
						'label'=>'Password',
						'field'=>'txt_user_password',
						'rules'=>'trim|required|min_length[6]',
						),

					array(
						'label'=>'Password Confirmation',
						'field'=>'txt_user_password_cf',
						'rules'=>'trim|required|matches[txt_user_password]',
						)
				);
			}	
				$this->form_validation->set_rules($validators);
				$this->form_validation->set_error_delimiters("<p style='color:red;'>* ","</p>");

				if($this->form_validation->run()==FALSE){

					$data['css_files'] = $this->twitterbootstrap->load_css_files();
					$data['js_files'] = $this->twitterbootstrap->load_js_files();
					$data['message_count']=$this->page_model->unread_message_count();
					$data['user_info'] = $this->users_model->_getUsersData('users',array(array('field'=>'usrs_ID','parameter'=>$user_id)));
					$data['page_title'] = 'Edit User';
					$this->load->view('tpl/head',$data);
					$this->load->view('tpl/navbar');
					$this->load->view('admin_edituser',$data);
					$this->load->view('tpl/footer',$data);
				}else{
					
					if(strlen($this->input->post('txt_user_password'))>0){

					$response =$this->users_model->_updateUserDetails(
						array(
							'usrs_username'=>$this->input->post('txt_username',TRUE),
							'usrs_full_name'=>$this->input->post('txt_user_fullname',TRUE),
							'usrs_email'=>$this->input->post('txt_user_mail',TRUE),
							'usrs_pw'=>$this->adminlib->hashPassword($this->input->post('txt_user_password',TRUE)),
							'usrs_role'=>$this->input->post('usr_role',TRUE)
							),
						$user_id
						);
					}else{

					$response = $this->users_model->_updateUserDetails(
						array(
							'usrs_username'=>$this->input->post('txt_username',TRUE),
							'usrs_full_name'=>$this->input->post('txt_user_fullname',TRUE),
							'usrs_email'=>$this->input->post('txt_user_mail',TRUE),
							'usrs_role'=>$this->input->post('usr_role',TRUE)
							),
						$user_id
						);
					}


				}
		}

		if($response==1){
			$this->session->set_flashdata('account_updated','Account successfully updated.');
			redirect(base_url('admin/user-list'));
		}

	}


	public function forbidden_page(){

		$data['css_files'] = $this->twitterbootstrap->load_css_files();
		$data['js_files'] = $this->twitterbootstrap->load_js_files();
		$data['page_title'] = 'Error 403';
		$this->load->view('admin_forbidden-page',$data);
	}


	public function frontend_themes(){

		$data['css_files'] = $this->twitterbootstrap->load_css_files();
		$data['js_files'] = $this->twitterbootstrap->load_js_files();
		$data['message_count']=$this->page_model->unread_message_count();
		$data['page_title'] = 'Front End Themes';
		$this->load->view('tpl/head',$data);
		$this->load->view('tpl/navbar');
		$this->load->view('admin_themes',$data);
		$this->load->view('tpl/footer',$data);
	}


	public function logout(){
		$this->session->unset_userdata('site_user');
		$this->session->unset_userdata('site_user_role');
		redirect(base_url('admin'));
	
	}


	public function parent_pages(){

		$data['css_files'] = $this->twitterbootstrap->load_css_files();
		$data['js_files'] = $this->twitterbootstrap->load_js_files();
		$data['message_count']=$this->page_model->unread_message_count();
		$data['page_title'] = 'Parent Pages';
		$data['parent_pages'] = $this->page_model->_getPagesData('pages',NULL);
		$this->load->view('tpl/head',$data);
		$this->load->view('tpl/navbar');
		$this->load->view('admin_parent_pages',$data);
		$this->load->view('tpl/footer',$data);

	}

	public function user_list(){

		$data['css_files'] = $this->twitterbootstrap->load_css_files();
		$data['js_files'] = $this->twitterbootstrap->load_js_files();
		$data['message_count']=$this->page_model->unread_message_count();
		$data['page_title'] = 'User List';
		$data['users']	= $this->users_model->_getUsersData('users',NULL);
		$this->load->view('tpl/head',$data);
		$this->load->view('tpl/navbar');
		$this->load->view('admin_users',$data);
		$this->load->view('tpl/footer',$data);
	}

	public function write_blog(){

		$data['css_files'] = $this->twitterbootstrap->load_css_files();
		$data['js_files'] = $this->twitterbootstrap->load_js_files();
		$data['message_count']=$this->page_model->unread_message_count();
		$data['page_title'] = 'Write a new post';	
		$data['categories']= $this->category_model->get_all_categories();	

		$this->form_validation->set_rules(add_blog_validators());
		$this->form_validation->set_error_delimiters("<p style='color:red;'>* ","</p>");
		if($this->form_validation->run()==FALSE){
			
			$this->load->view('tpl/head',$data);
			$this->load->view('tpl/navbar');
			$this->load->view('admin_write-blog');
			$this->load->view('tpl/footer',$data);

		}else{
			$response =  $this->blog_model->add_blog(
				array(
					'postID'=>($this->blog_model->get_lastblogId()+1),
					'title'=>$this->input->post('title',TRUE),
					'slug'=>url_title($this->input->post('title'), 'dash', TRUE),
					'date_posted'=>date('Y-m-d'),
					'posted_by'=>$this->session->userdata('site_user_id'),
					'parent_category'=>$this->input->post('blog_categ',TRUE),
					'content'=>$this->input->post('content')
				  )
				);

			if($response==1){
				$this->session->set_flashdata('saveblog_ok','Blog successfully posted');
				redirect(base_url('admin/blog-post'));
			}
		}


	}
	
	public function site_settings(){
		$data['css_files'] = $this->twitterbootstrap->load_css_files();
		$data['js_files'] = $this->twitterbootstrap->load_js_files();
		$data['page_title']='Dashboard - Site Settings';
		
		$this->form_validation->set_rules('txt_site_owner','Site Owner','required|trim|max_length[45]');
		$this->form_validation->set_rules('txt_site_title','Site Name','required|trim|max_length[45]');
		$this->form_validation->set_rules('site_meta','Site Meta','required|trim|max_length[250]');
		$this->form_validation->set_rules('site_metakw','Site Meta Keywords','required|trim');
		$this->form_validation->set_rules('site_footer','Footer','required|trim|max_length[80]');
		
		if($this->form_validation->run()===FALSE){

			$data['site_meta']=$this->users_model->getsite_meta_description();
			$data['site_owner']=$this->users_model->getsite_owner();
			$data['site_title']=$this->users_model-> getsite_title();
			$data['site_metakw'] = $this->users_model->getsite_meta_keywords();
			$data['footer'] = $this->users_model->getsite_footer();
			$data['message_count']=$this->page_model->unread_message_count();
			$this->load->view('tpl/head',$data);
			$this->load->view('tpl/navbar');
			$this->load->view('admin_settings', $data);
			$this->load->view('tpl/footer',$data);
		}else{
			$this->users_model->updatesiteInfo();
			$this->session->set_flashdata('changes1','Changes has been saved.');
			redirect(base_url().'admin/site-settings');
		}

	}
	
	public function uploadPhoto($source_file,$file_name,$project_url){
		if($source_file!=NULL){
		$rand_name=md5(mt_rand(1,999999999));
			if(isset($file_name)&&isset($source_file))
			{
				for($i=0; $i<=1;$i++)
					{
						if(getimagesize($source_file[$i])>0) {
						$uploaded_state=move_uploaded_file($source_file[$i],$this->dir_path.$rand_name.$file_name[$i]);
						//update photo
						$this->users_model->updateUserPic($project_url.str_replace('./','',$this->dir_path).$rand_name.$file_name[$i]);
					}
				}
			}
		}
	}
	
	public function newsletter() {
		$data['css_files'] = $this->twitterbootstrap->load_css_files();
		$data['js_files'] = $this->twitterbootstrap->load_js_files();
		$data['message_count']=$this->page_model->unread_message_count();
		$data['page_title'] = 'Newsletter';
		$this->load->view('tpl/head',$data);
		$this->load->view('tpl/navbar');
		$this->load->view('admin_newsletter',$data);
		$this->load->view('tpl/footer',$data);
	}
	
	public function send() {
		$subscribers = $this->page_model->get_subscriber_data();
		foreach($subscribers as $subscriber)
    {
		$this->load->library('email');
        $this->load->helper('typography');

        //Format email content using an HTML file
        $mes = $this->input->post('content');
		$subj = $this->input->post('subject');

        $config['mailtype'] = 'html';
        $this->email->initialize($config);

        $this->email->from('no-reply@neko.com', 'Neko Admin');
        $this->email->to($subscriber->email);
        $this->email->subject($subj);
        $this->email->message($mes);

        $this->email->send();
		echo $this->email->print_debugger();
		$this->email->clear();
    }
	
	if($this->email->send())
        {       
            //echo 'Your email was sent.';
			$this->session->set_flashdata('success', 'Newsletterr Sent');
			$this->load->view('admin_newsletter', $data);
        }
	
	}
	
	public function comments () {	
		$data['css_files'] = $this->twitterbootstrap->load_css_files();
		$data['js_files'] = $this->twitterbootstrap->load_js_files();
		$data['message_count']=$this->page_model->unread_message_count();
		$data['page_title'] = 'Comments';
		$data['comments']=$this->blog_model->getBlogComments();
		$this->load->view('tpl/head',$data);
		$this->load->view('tpl/navbar');
		$this->load->view('admin_comments',$data);
		$this->load->view('tpl/footer',$data);  
	}
	
	public function commentaction(){
		$action = $this->input->post('comment_action');
        $comment_id =$this->input->post('comment_id');
		echo $this->blog_model->comment_action($action,$comment_id);
	}
	
	public function viewcomment(){
		$c_id = $this->input->post('comment_id');
		$comment_data['contents'] = $this->blog_model->getBlogCommentbyId($c_id);
		$this->load->view('admin_commentpopup',$comment_data);
	}

	public function inbox($offset=0){
    
		$uri_segment = 3;
		$offset = $this->uri->segment($uri_segment);
		$config['base_url'] = base_url().'admin/inbox';
		$config['total_rows'] =$this->page_model->unread_message_count();
		$config['per_page'] = 10;
		$config['prev_link'] = '&laquo;';
		$config['next_link'] = '&raquo;';
		$config['full_tag_open'] = '<ul class="pagination">';
		$config['full_tag_close'] = '</ul>';
		$config['prev_link'] = '&laquo;';
		$config['prev_tag_open'] = '<li>';
		$config['prev_tag_close'] = '</li>';
		$config['next_tag_open'] = '<li>';
		$config['next_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li class="active"><a href="#">';
		$config['cur_tag_close'] = '</a></li>';
		$config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';
		$config['uri_segment'] = $uri_segment;
		$this->pagination->initialize($config);
		$data['message_count']=$this->page_model->unread_message_count();
		$data['message_list']=$this->page_model->fetch_unread_messages(10,$offset);
		$data['css_files'] = $this->twitterbootstrap->load_css_files();
		$data['js_files'] = $this->twitterbootstrap->load_js_files();
		$data['page_title'] = 'Inbox';
		$this->load->view('tpl/head',$data);
		$this->load->view('tpl/navbar');
		$this->load->view('admin_inbox');
		$this->load->view('tpl/footer',$data);
	}
	
	public function showmessage($msgid){
		if(empty($msgid))
		{
			redirect('admin/inbox');
		} else{
			if($this->page_model->viewmsg($msgid)!=NULL){
				$data['msg_content']=$this->page_model->viewmsg($msgid);
				$data['message_count']=$this->page_model->unread_message_count();
				$data['css_files'] = $this->twitterbootstrap->load_css_files();
				$data['js_files'] = $this->twitterbootstrap->load_js_files();
				$data['page_title'] = 'Inbox';
				$this->load->view('tpl/head',$data);
				$this->load->view('tpl/navbar');
				$this->load->view('admin_viewmessage');
				$this->load->view('tpl/footer',$data);
			}else{
				redirect('admin/inbox');
			}
		}
	}
	
	public function deletemessage($msgid){
		$this->page_model->delete_message($msgid);
		$this->session->set_flashdata('msgdelete_success','Message Succesfully Deleted');
		redirect(base_url().'admin/inbox');
	}


}