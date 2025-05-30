<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Locations extends MY_Controller 
{
	function __construct()
	{
		parent::__construct();
		$this->load->library(array('session'));
		$this->load->library(array('ion_auth','form_validation', 'grocery_CRUD'));
		
		$group = array('admin');
		if (!$this->ion_auth->in_group($group)) {
			$this->prepare_flashmessage(get_languageword('MSG_NO_ENTRY'),2);
			redirect(getUserType());
		}
	}
	
	/** Displays the Index Page**/
	function index()
	{
		$crud = new grocery_CRUD();
		$crud_state = $crud->getState();
		$crud->unset_jquery(); //As we are using admin lte we need to unset default jQuery

		$crud->set_table($this->db->dbprefix('locations'));
		$crud->set_relation('created_by',TBL_USERS, 'username');
		$crud->set_relation('updated_by',TBL_USERS, 'username');
		$crud->where('parent_location_id', 0);
		$crud->set_subject( get_languageword('location') );
		$crud->columns('id','location_name','code','status','created_by');

		$crud->add_fields(array('location_name', 'slug', 'code', 'status', 'parent_location_id', 'created_at'));
		$crud->edit_fields(array('location_name', 'slug', 'code', 'status', 'parent_location_id', 'updated_at'));
		//Add Hidden fields
		$crud->field_type('created_at', 'hidden', date('Y-m-d H:i:s')); //Add hidden field
		$crud->field_type('updated_at', 'hidden', date('Y-m-d H:i:s')); //Add hidden field
		$crud->field_type('parent_location_id', 'hidden', 0); //Add hidden field

		$crud->required_fields(array('location_name', 'slug', 'code', 'status'));
		$crud->display_as('location_name',get_languageword('location_Name'));

		$crud->unique_fields('location_name', 'code');

		$crud->unset_delete();
		$crud->unset_read();

		$crud->add_action('view sub locations', URL_ADMIN_IMAGES.'icon-location.png', 'locations/view_locations','ui-icon-plus'); //TO add custom action link

		$crud->callback_insert(array($this,'callback_loc_insert'));
		$crud->callback_update(array($this,'callback_loc_update'));

		$output = $crud->render();

		$this->data['activemenu'] = 'locations';
		$this->data['activesubmenu'] = 'locations';
		$this->data['pagetitle'] = get_languageword('locations');
		if($crud_state == 'read')
			$crud_state ='View';


		if ($crud_state=="list") {
			//update notification
			$view_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			update_notification($view_link);
		}

		if($crud_state != 'list')
		{
			if($crud_state == 'add')
			$this->data['activesubmenu'] = 'locations-add';
			$this->data['pagetitle'] = get_languageword($crud_state).' '.get_languageword('location');
			$this->data['maintitle'] = get_languageword('locaitons');
			$this->data['maintitle_link'] = URL_LOCATIONS_INDEX;
		}
		else
		{
			$this->data['activemenu'] = 'locations';
			$this->data['activesubmenu'] = 'locations';
			$this->data['pagetitle'] = get_languageword('locations');
		}
		$this->data['grocery_output'] = $output;
		$this->data['grocery'] = TRUE;
		$this->grocery_output($this->data);
	}


	function callback_loc_insert($post_array) {

		$post_array['slug'] = prepare_slug($post_array['slug'], 'slug', 'locations');

		$user_id = $this->ion_auth->get_user_id();
		$post_array['created_by'] = $user_id;

		$this->base_model->insert_operation($post_array, 'locations');

		return true;
	}

	function callback_loc_update($post_array, $primary_key) {

		$prev_name = $this->base_model->fetch_value('locations', 'slug', array('id' => $primary_key));

		//If updates the name
		if($prev_name != $post_array['slug']) {
			$post_array['slug'] = prepare_slug($post_array['slug'], 'slug', 'locations');
		}

		$user_id = $this->ion_auth->get_user_id();
		$post_array['updated_by'] = $user_id;

		if($this->base_model->update_operation($post_array, 'locations', array('id' => $primary_key)) && $post_array['parent_location_id'] == 0) {

			$main_loc_status = $post_array['status'];

			$up_data['status'] = $main_loc_status;

			$this->base_model->update_operation($up_data, 'locations', array('parent_location_id' => $primary_key));
		}

		return true;
	}

	function view_locations( $param )
	{
		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
			$this->prepare_flashmessage(get_languageword('Please login to access this area'));
			redirect('auth/login');
		}
		
		if(empty($param))
		{
			$this->prepare_flashmessage(get_languageword('Please select a location'));
			redirect('locations/index');
		}
		
		$user_id = $this->ion_auth->get_user_id();

		$locaiton_name = $this->base_model->fetch_records_from('locations', array('id' => $param));
		if(empty($locaiton_name))
		{
			$this->prepare_flashmessage(get_languageword('invalid location'));
			redirect('locations/index');
		}
		$locaiton_name = $locaiton_name[0]->location_name;
		
		$crud = new grocery_CRUD();
		$crud_state = $crud->getState();
		$crud->unset_jquery(); //As we are using admin lte we need to unset default jQuery
		$crud->set_table($this->db->dbprefix('locations'));
		$crud->set_relation('created_by',TBL_USERS, 'username');
		$crud->set_relation('updated_by',TBL_USERS, 'username');
		$crud->where('parent_location_id', $param);
		$crud->set_subject(get_languageword('Sub Location for ').$locaiton_name);
		$crud->columns('id','location_name','code','status', 'created_by');

		
		$crud->add_fields(array('location_name', 'slug', 'code', 'status', 'parent_location_id', 'created_at'));
		$crud->edit_fields(array('location_name', 'slug', 'code', 'status', 'parent_location_id', 'updated_at'));

		$crud->required_fields(array('location_name', 'code', 'slug', 'status'));
		$crud->unique_fields('location_name', 'code');

		$crud->display_as('location_name',get_languageword('location_Name'));
				
		
		$crud->field_type('parent_location_id', 'hidden', $param); //Add hidden field
		$crud->field_type('created_at', 'hidden', date('Y-m-d H:i:s')); //Add hidden field
		$crud->field_type('updated_at', 'hidden', date('Y-m-d H:i:s')); //Add hidden field
		
		$crud->unset_read();
		$this->data['activemenu'] = 'locations';
		$this->data['activesubmenu'] = 'subLocations';
		$this->data['maintitle'] = get_languageword('locaitons');
		$this->data['pagetitle'] = get_languageword('sub_Locations');
		$this->data['maintitle_link'] = base_url().'locations/index/';

		if($crud_state == 'read')
			$crud_state = 'View';

		if($crud_state != 'list')
		{
			if($crud_state == 'add')
			$this->data['activesubmenu'] = 'locations-add';
			$this->data['pagetitle'] = get_languageword($crud_state).' '.get_languageword('location');
			$this->data['maintitle'] = get_languageword('sub_Locaitons');
			$this->data['maintitle_link'] = base_url().'locations/view_locations/'.$param;
		}
		else
		{
			$this->data['activesubmenu'] = 'locations';
			$this->data['pagetitle'] = get_languageword('sub_Locations');
		}

		$crud->callback_insert(array($this,'callback_loc_insert'));
		$crud->callback_update(array($this,'callback_loc_update'));

		$output = $crud->render();		
		$this->data['activemenu'] = 'locations';
		$this->data['grocery_output'] = $output;
		$this->data['grocery'] = TRUE;
		$this->grocery_output($this->data);
	}
}
?>