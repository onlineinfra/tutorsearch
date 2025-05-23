<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Ion Auth
*
* Version: 2.5.2
*
* Author: Ben Edmunds
*		  ben.edmunds@gmail.com
*         @benedmunds
*
* Added Awesomeness: Phil Sturgeon
*
* Location: http://github.com/benedmunds/CodeIgniter-Ion-Auth
*
* Created:  10.01.2009
*
* Description:  Modified auth system based on redux_auth with extensive customization.  This is basically what Redux Auth 2 should be.
* Original Author name has been kept but that does not mean that the method has not been modified.
*
* Requirements: PHP5 or above
*
*/

class Ion_auth
{
	/**
	 * account status ('not_activated', etc ...)
	 *
	 * @var string
	 **/
	protected $status;

	/**
	 * extra where
	 *
	 * @var array
	 **/
	public $_extra_where = array();

	/**
	 * extra set
	 *
	 * @var array
	 **/
	public $_extra_set = array();

	/**
	 * caching of users and their groups
	 *
	 * @var array
	 **/
	public $_cache_user_in_group;

	/**
	 * __construct
	 *
	 * @return void
	 * @author Ben
	 **/
	public function __construct()
	{
		$this->load->config('ion_auth', TRUE);
		$this->load->library(array('email'));
		$this->lang->load('ion_auth');
		$this->load->helper(array('cookie', 'language','url'));

		$this->load->library('session');

		$this->load->model('ion_auth_model');

		$this->_cache_user_in_group =& $this->ion_auth_model->_cache_user_in_group;

		//auto-login the user if they are remembered
		if (!$this->logged_in() && get_cookie($this->config->item('identity_cookie_name', 'ion_auth')) && get_cookie($this->config->item('remember_cookie_name', 'ion_auth')))
		{
			$this->ion_auth_model->login_remembered_user();
		}

		$email_config = $this->config->item('email_config', 'ion_auth');

		if ($this->config->item('use_ci_email', 'ion_auth') && isset($email_config) && is_array($email_config))
		{
			$this->email->initialize($email_config);
		}

		$this->ion_auth_model->trigger_events('library_constructor');
	}

	/**
	 * __call
	 *
	 * Acts as a simple way to call model methods without loads of stupid alias'
	 *
	 **/
	public function __call($method, $arguments)
	{
		if (!method_exists( $this->ion_auth_model, $method) )
		{
			throw new Exception('Undefined method Ion_auth::' . $method . '() called');
		}
		if($method == 'create_user')
		{
			return call_user_func_array(array($this, 'register'), $arguments);
		}
		if($method=='update_user')
		{
			return call_user_func_array(array($this, 'update'), $arguments);
		}
		return call_user_func_array( array($this->ion_auth_model, $method), $arguments);
	}

	/**
	 * __get
	 *
	 * Enables the use of CI super-global without having to define an extra variable.
	 *
	 * I can't remember where I first saw this, so thank you if you are the original author. -Militis
	 *
	 * @access	public
	 * @param	$var
	 * @return	mixed
	 */
	public function __get($var)
	{
		return get_instance()->$var;
	}


	/**
	 * forgotten password feature
	 *
	 * @return mixed  boolian / array
	 * @author Mathew
	 **/
	public function forgotten_password($identity)    //changed $email to $identity
	{
		if ( $this->ion_auth_model->forgotten_password($identity) )   //changed
		{
			// Get user information
      $identifier = $this->ion_auth_model->identity_column; // use model identity column, so it can be overridden in a controller
      $user = $this->where($identifier, $identity)->where('active', 1)->users()->row();  // changed to get_user_by_identity from email

			if ($user)
			{
				$data = array(
					'identity'		=> $user->{$this->config->item('identity', 'ion_auth')},
					'forgotten_password_code' => $user->forgotten_password_code
				);

				if(!$this->config->item('use_ci_email', 'ion_auth'))
				{
					$this->set_message('forgot_password_successful');
					return $data;
				}
				else
				{
	
					$email_template = $this->db->query("SELECT * FROM ".TBL_PREFIX.TBL_EMAIL_TEMPLATES." WHERE email_template_id=3 AND template_status = 'Active' ")->result();

					if (!empty($email_template))
					{

						$email_template = $email_template[0];
						
						$logo_img='<img src="'.get_site_logo().'" class="img-responsive" width="120px" height="50px">';
						
				
						$content 	= $email_template->template_content;

						$content 	= str_replace("__SITE_LOGO__", $logo_img,$content);
					
						$content 	= str_replace("__SITE_TITLE__", $this->config->item('site_settings')->site_title,$content);
						
						$content 	= str_replace("__EMAIL__", $data['identity'], $content);

						$content 	= str_replace("___RESET_YOUR_PASSWORD___", '<a href="'.URL_RESET_PASSWORD.DS.$data['forgotten_password_code'].'">'.get_languageword('Reset_your_Password').'</a>',$content);
						
						$content 	= str_replace("__SITE_TITLE__", $this->config->item('site_settings')->site_title,$content);

						
						$from 	= $this->config->item('site_settings')->portal_email;
					
						$to 	 = $user->email;

						$subject = ucwords(str_replace('_',' ',$email_template->template_subject)).' '.$this->config->item('site_settings')->site_title;
						
						$msg 	 = $content;

						if(sendEmail($from,$to,$subject,$msg))
						{
							$this->set_message('forgot_password_successful');
							return TRUE;
						}
						else
						{
							$this->set_error('forgot_password_unsuccessful');
							return FALSE;
						}
					}
					else
					{
						/*$this->set_error('forgot_password_unsuccessful');
						return FALSE;*/

						$message = $this->load->view($this->config->item('email_templates', 'ion_auth').$this->config->item('email_forgot_password', 'ion_auth'), $data, true);
					
						$from=$this->config->item('site_settings')->portal_email;
						$to=$user->email;
						$subject=$this->config->item('site_title', 'ion_auth') . ' - ' . $this->lang->line('email_forgotten_password_subject');
						
						$msg = $message;
				
						if(sendEmail($from,$to,$subject,$msg))
						{
							$this->set_message('forgot_password_successful');
							return TRUE;
						}
						else
						{
							$this->set_error('forgot_password_unsuccessful');
							return FALSE;
						}
					}
				}
			}
			else
			{
				$this->set_error('user_notfound_notactivated');
				return FALSE;
			}
		}
		else
		{
			$this->set_error('forgot_password_unsuccessful');
			return FALSE;
		}
	}

	/**
	 * forgotten_password_complete
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function forgotten_password_complete($code)
	{
		$this->ion_auth_model->trigger_events('pre_password_change');

		$identity = $this->config->item('identity', 'ion_auth');
		$profile  = $this->where('forgotten_password_code', $code)->users()->row(); //pass the code to profile

		if (!$profile)
		{
			$this->ion_auth_model->trigger_events(array('post_password_change', 'password_change_unsuccessful'));
			$this->set_error('password_change_unsuccessful');
			return FALSE;
		}

		$new_password = $this->ion_auth_model->forgotten_password_complete($code, $profile->salt);

		if ($new_password)
		{
			$data = array(
				'identity'     => $profile->{$identity},
				'new_password' => $new_password
			);
			if(!$this->config->item('use_ci_email', 'ion_auth'))
			{
				$this->set_message('password_change_successful');
				$this->ion_auth_model->trigger_events(array('post_password_change', 'password_change_successful'));
					return $data;
			}
			else
			{
				$message = $this->load->view($this->config->item('email_templates', 'ion_auth').$this->config->item('email_forgot_password_complete', 'ion_auth'), $data, true);

				$this->email->clear();
				$this->email->from($this->config->item('admin_email', 'ion_auth'), $this->config->item('site_title', 'ion_auth'));
				$this->email->to($profile->email);
				$this->email->subject($this->config->item('site_title', 'ion_auth') . ' - ' . $this->lang->line('email_new_password_subject'));
				$this->email->message($message);

				if ($this->email->send())
				{
					$this->set_message('password_change_successful');
					$this->ion_auth_model->trigger_events(array('post_password_change', 'password_change_successful'));
					return TRUE;
				}
				else
				{
					$this->set_error('password_change_unsuccessful');
					$this->ion_auth_model->trigger_events(array('post_password_change', 'password_change_unsuccessful'));
					return FALSE;
				}

			}
		}

		$this->ion_auth_model->trigger_events(array('post_password_change', 'password_change_unsuccessful'));
		return FALSE;
	}

	/**
	 * forgotten_password_check
	 *
	 * @return void
	 * @author Michael
	 **/
	public function forgotten_password_check($code)
	{
		$profile = $this->where('forgotten_password_code', $code)->users()->row(); //pass the code to profile

		if (!is_object($profile))
		{
			$this->set_error('password_change_unsuccessful');
			return FALSE;
		}
		else
		{
			if ($this->config->item('forgot_password_expiration', 'ion_auth') > 0) {
				//Make sure it isn't expired
				$expiration = $this->config->item('forgot_password_expiration', 'ion_auth');
				if (time() - $profile->forgotten_password_time > $expiration) {
					//it has expired
					$this->clear_forgotten_password_code($code);
					$this->set_error('password_change_unsuccessful');
					return FALSE;
				}
			}
			return $profile;
		}
	}

	/**
	 * register
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function register($identity, $password, $email, $additional_data = array(), $group_ids = array(),$fb = FALSE) //need to test email activation
	{
		$this->ion_auth_model->trigger_events('pre_account_creation');
		
		/*
		if(!$fb)
		$email_activation = $this->config->item('email_activation', 'ion_auth');
		
		if($fb)
		$email_activation = FALSE;
	*/
	
		$email_activation = $this->config->item('email_activation', 'ion_auth');

        // print_r("email".$email_activation);
        // print_r("--------------------------");
		$id = $this->ion_auth_model->register($identity, $password, $email, $additional_data, $group_ids, $fb);
        // print_r("return ion_auth controller-");
        
		if (!$email_activation)
		{	
	
	       // print_r("under email_activation");
			if ($id !== FALSE)
			{
			    print_r("under email_activation - id");
				$this->set_message('account_creation_successful');
				$this->ion_auth_model->trigger_events(array('post_account_creation', 'post_account_creation_successful'));
				return $id;
			}
			else
			{
				$this->set_error('account_creation_unsuccessful');
				$this->ion_auth_model->trigger_events(array('post_account_creation', 'post_account_creation_unsuccessful'));
				return FALSE;
			}
		}
		else
		{
			if (!$id)
			{
				$this->set_error('account_creation_unsuccessful');
				return FALSE;
			}

			// deactivate so the user much follow the activation flow
			//$deactivate = $this->ion_auth_model->deactivate($id);
			// deactivate so the user much follow the activation flow
			if(!$fb)
			$deactivate = $this->ion_auth_model->deactivate($id);
			else
			$deactivate=true;

			// the deactivate method call adds a message, here we need to clear that
			$this->ion_auth_model->clear_messages();


			if (!$deactivate)
			{
				$this->set_error('deactivate_unsuccessful');
				$this->ion_auth_model->trigger_events(array('post_account_creation', 'post_account_creation_unsuccessful'));
				return FALSE;
			}

			$activation_code = $this->ion_auth_model->activation_code;
			$identity        = $this->config->item('identity', 'ion_auth');
			$user            = $this->ion_auth_model->user($id)->row();

			$data = array(
				'identity'   => $user->{$identity},
				'id'         => $user->id,
				'email'      => $email,
				'activation' => $activation_code,
				
			);
			
// 			print_r($data);
			if(!$this->config->item('use_ci_email', 'ion_auth'))
			{
				$this->ion_auth_model->trigger_events(array('post_account_creation', 'post_account_creation_successful', 'activation_email_successful'));
				$this->set_message('activation_email_successful');
				return $data;
			}
			else
			{

				if ($fb) { 
					$email_template = $this->db->query('SELECT * FROM '.$this->db->dbprefix('email_templates').' WHERE email_template_id = 19 AND template_status = "Active"')->result();
				} else {
					$email_template = $this->db->query('SELECT * FROM '.$this->db->dbprefix('email_templates').' WHERE email_template_id = 1 AND template_status = "Active"')->result();
				}



				if(!empty($email_template))
				{

					$logo_img='<img src="'.get_site_logo().'" class="img-responsive" width="120px" height="50px">';

					$email_template = $email_template[0];

					$content 	= $email_template->template_content;
					
					$name = $additional_data['first_name'].' '.$additional_data['last_name'];
					

					$content 	= str_replace("__SITE_LOGO__", $logo_img,$content);

					$content 	= str_replace("__SITE_TITLE__", $this->config->item('site_settings')->site_title,$content);	

					$content 	= str_replace("__USER__NAME__", $name,$content);

					$content 	= str_replace("__SITE_TITLE__", $this->config->item('site_settings')->site_title,$content);	

					$content 	= str_replace("__EMAIL__", $email,$content);

					$content 	= str_replace("__PASSWORD__", $password,$content);


					$content 	= str_replace("__ACCOUNT_ACTIVATOIN_LINK__", '<a href="'.SITEURL2.'/auth/activate/'.$id.'/'.$activation_code.'" target="_blank">Activate</a>' ,$content);					
					
					$content 	= str_replace("__ANDROID__", '<a href="'.$this->config->item('site_settings')->androd_app.'">Android</a>',$content);		

					$content 	= str_replace("__IOS__", '<a href="'.$this->config->item('site_settings')->ios_app.'">IOS</a>',$content);
						

					$content 	= str_replace("__SITE_TITLE__", $this->config->item('site_settings')->site_title,$content);		

								
					if($email_template->from_email != '')
					{
						$from = $email_template->from_email;
					}
					else
					{
					$from 	= $this->config->item('admin_email', 'ion_auth');
					}
					$to 	= $email;
					
					if($email_template->template_subject != '')
					{
						$subject = $email_template->template_subject;
					}
					else
					{
						$subject 	= $this->config->item('site_title', 'ion_auth') . ' - ' . "Welcome Message";
					}
					$msg 	= $content;
					

					if(sendEmail($from,$to,$subject,$msg))
					{
						$this->ion_auth_model->trigger_events(array('post_account_creation', 'post_account_creation_successful', 'activation_email_successful'));
						$this->set_message('activation_email_successful');
						return $id;
					}

				} else {
					return $id;
				}
			}

			$this->ion_auth_model->trigger_events(array('post_account_creation', 'post_account_creation_unsuccessful', 'activation_email_unsuccessful'));
			$this->set_error('activation_email_unsuccessful');
			return FALSE;
		}
	}

	/**
	 * logout
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function logout()
	{
		$this->ion_auth_model->trigger_events('logout');
		$identity = $this->config->item('identity', 'ion_auth');
		$tables  = $this->config->item('tables', 'ion_auth');
        if (substr(CI_VERSION, 0, 1) == '2')
		{
			$fields = array($identity => '', 'id' => '', 'user_id' => '');			
			foreach($this->db->list_fields($tables['users']) as $field)
			$fields[$field] = '';
			$this->session->unset_userdata( $fields );
		}
		else
		{
			$fields = array($identity, 'id', 'user_id');
			foreach($this->db->list_fields($tables['users']) as $field)
			array_push($fields, $field);
			$this->session->unset_userdata( $fields );
		}

		// delete the remember me cookies if they exist
		if (get_cookie($this->config->item('identity_cookie_name', 'ion_auth')))
		{
			delete_cookie($this->config->item('identity_cookie_name', 'ion_auth'));
		}
		if (get_cookie($this->config->item('remember_cookie_name', 'ion_auth')))
		{
			delete_cookie($this->config->item('remember_cookie_name', 'ion_auth'));
		}

		// Destroy the session
		$this->session->sess_destroy();

		//Recreate the session
		if (substr(CI_VERSION, 0, 1) == '2')
		{
			$this->session->sess_create();
		}
		else
		{
			session_start();
			$this->session->sess_regenerate(TRUE);
		}

		$this->set_message('logout_successful');
		return TRUE;
	}

	/**
	 * logged_in
	 *
	 * @return bool
	 * @author Mathew
	 **/
	public function logged_in()
	{
		$this->ion_auth_model->trigger_events('logged_in');

		return (bool) $this->session->userdata('identity');
	}

	/**
	 * logged_in
	 *
	 * @return integer
	 * @author jrmadsen67
	 **/
	public function get_user_id()
	{
		$user_id = $this->session->userdata('user_id');
		if (!empty($user_id))
		{
			return $user_id;
		}
		return null;
	}


	/**
	 * is_admin
	 *
	 * @return bool
	 * @author Ben Edmunds
	 **/
	public function is_admin($id=false)
	{
		$this->ion_auth_model->trigger_events('is_admin');

		$admin_group = $this->config->item('admin_group', 'ion_auth');

		return $this->in_group($admin_group, $id);
	}
	
	/**
	 * is_tutor
	 *
	 * @return bool
	 * @author Adi
	 **/
	public function is_tutor($id=false)
	{
		$this->ion_auth_model->trigger_events('tutor_group');
		$general_group = $this->config->item('tutor_group', 'ion_auth');
		return $this->in_group($general_group, $id);
	}
	
	/**
	 * is_student
	 *
	 * @return bool
	 * @author Adi
	 **/
	public function is_student($id=false)
	{
		$this->ion_auth_model->trigger_events('default_group');
		$general_group = $this->config->item('default_group', 'ion_auth');
		return $this->in_group($general_group, $id);
	}
	
	/**
	 * is_institute
	 *
	 * @return bool
	 * @author Adi
	 **/
	public function is_institute($id=false)
	{
		$this->ion_auth_model->trigger_events('institute_group');
		$general_group = $this->config->item('institute_group', 'ion_auth');
		return $this->in_group($general_group, $id);
	}
	
	/**
	 * is_member
	 *
	 * @return bool
	 * @author Ben Edmunds
	 **/
	public function is_member($id=false) //is_student also same
	{
		$this->ion_auth_model->trigger_events('member_group');
		$member_group = $this->config->item('member_group', 'ion_auth');		
		return $this->in_group($member_group, $id);
	}
	
		
	/**
	 * in_group
	 *
	 * @param mixed group(s) to check
	 * @param bool user id
	 * @param bool check if all groups is present, or any of the groups
	 *
	 * @return bool
	 * @author Phil Sturgeon
	 **/
	public function in_group($check_group, $id=false, $check_all = false)
	{
		$this->ion_auth_model->trigger_events('in_group');

		$id || $id = $this->session->userdata('user_id');

		if (!is_array($check_group))
		{
			$check_group = array($check_group);
		}

		if (isset($this->_cache_user_in_group[$id]))
		{
			$groups_array = $this->_cache_user_in_group[$id];
		}
		else
		{
			$users_groups = $this->ion_auth_model->get_users_groups($id)->result();
			
			$groups_array = array();
			foreach ($users_groups as $group)
			{
				$groups_array[$group->id] = $group->name;
			}
			$this->_cache_user_in_group[$id] = $groups_array;
		}
		foreach ($check_group as $key => $value)
		{
			$groups = (is_string($value)) ? $groups_array : array_keys($groups_array);

			/**
			 * if !all (default), in_array
			 * if all, !in_array
			 */
			if (in_array($value, $groups) xor $check_all)
			{
				/**
				 * if !all (default), true
				 * if all, false
				 */
				return !$check_all;
			}
		}

		/**
		 * if !all (default), false
		 * if all, true
		 */
		return $check_all;
	}

}
