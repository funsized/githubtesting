<?php

class users_controller extends base_controller {


	public function __construct() {
		parent::__construct();
	}


	/*-------------------------------------------------------------------------------------------------
	
	-------------------------------------------------------------------------------------------------*/
	public function signup() {

		# Set up template
		$this->template->content = View::instance("v_users_signup");

		# Render the template
		echo $this->template;

	}


	/*-------------------------------------------------------------------------------------------------
	
	-------------------------------------------------------------------------------------------------*/
	public function p_signup() {

		# What data was submitted
		print_r($_POST);

	//	# Encrypt password
		$_POST['password'] = sha1(PASSWORD_SALT.$_POST['password']);

	//	# Create and encrypt token
		$_POST['token']    = sha1(TOKEN_SALT.$_POST['email'].Utils::generate_random_string());

	//	# Store current timestamp 
		$_POST['created']  = Time::now(); # This returns the current timestamp
		$_POST['modified'] = Time::now();

		# Insert 
		DB::instance(DB_NAME)->insert('users', $_POST);

		echo "You're registered! Now go <a href='/users/login'>login</a>";

	}


	/*-------------------------------------------------------------------------------------------------
	
	-------------------------------------------------------------------------------------------------*/
	public function login() {

		# Load the template
		$this->template->content = View::instance("v_users_login");

		# Render the template
		echo $this->template;

	}


	/*-------------------------------------------------------------------------------------------------
	
	-------------------------------------------------------------------------------------------------*/
	public function p_login() {

		$_POST['password'] = sha1(PASSWORD_SALT.$_POST['password']);

		# Prevent SQL injection attacks
		$_POST = DB::instance(DB_NAME)->sanitize($_POST);

		$q = "SELECT token
			FROM users
			WHERE email = '".$_POST['email']."'
			AND password = '".$_POST['password']."'
			";

		$token = DB::instance(DB_NAME)->select_field($q);

		# Login failed
		if($token == "") {
			Router::redirect("/users/login");
			
		}
		# Login passwed
		else {
			setcookie("token", $token, strtotime('+2 weeks'), '/');

			Router::redirect("/users/profile");
			//echo "Login success";
		}

	}


	/*-------------------------------------------------------------------------------------------------
	
	-------------------------------------------------------------------------------------------------*/
	public function logout() {
	
	# Generate and save a new token for next login
	$new_token = sha1(TOKEN_SALT.$this->user->email.Utils::generate_random_string());
	
	# Create the data array we'll use with the update method
	# In this case, we're only updating one field, so our array only has one entry
	$data = Array("token" => $new_token);
	
	# Do the update
	DB::instance(DB_NAME)->update("users", $data, "WHERE token = '".$this->user->token."'");
	
	# Delete their token cookie - effectively logging them out
	setcookie("token", "", strtotime('-1 year'), '/');
	
	# Send them back to the main landing page
	//Router::redirect("/");
	echo "You've logged out!";

}


	/*-------------------------------------------------------------------------------------------------
	
	-------------------------------------------------------------------------------------------------*/
	public function profile() {

			# If user is blank, they're not logged in, show message and don't do anything else
			if(!$this->user) {
				echo "Members only. <a href='/users/login'>Login</a>";
				
				# Return will force this method to exit here so the rest of 
				# the code won't be executed and the profile view won't be displayed.
				return false;
			}

			# Setup view
			$this->template->content = View::instance('v_users_profile');
			$this->template->title   = "Profile of".$this->user->first_name;
				
			# Render template
			echo $this->template;
		}	
	

}