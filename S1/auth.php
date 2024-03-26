<?php 
if ($option == 'login') {
	if (!empty($_POST)) {
	    if (empty($_POST['username']) || empty($_POST['password'])) {
	        $errors[] = "Please check your details";
	    } else {
	        $username        = secure($_POST['username']);
	        $password        = secure($_POST['password']);

	        $getUser = $db->where("(username = ? or email = ?)", array(
	            $username,
	            $username
	        ))->getOne(T_USERS, ["password", "id", "active"]);

	        if (empty($getUser)) {
	        	$errors[] = "Incorrect username or password";
	        } else if (!password_verify($password, $getUser->password)) {
	        	$errors[] = "Incorrect username or password";
	        
		            $music->loggedin = true;
		            $music->user = userData($getUser->id);
		            unset($music->user->password);
	                $data = array(
			            'status' => 200,
			            'access_token' => $_SESSION['user_id'],
			            'data' => $music->user
			        );
	            }  
	        }
	    }
	}




if ($option == 'reset-password') {
	if (!empty($_POST)) {
	    if (empty($_POST['password']) || empty($_POST['c_password']) || empty($_POST['email_code'])) {
	        $errors[] = "Please check your details";
	    } else {
	        $password        = secure($_POST['password']);
	        $c_password  = secure($_POST['c_password']);
	        $old_email_code = secure($_POST['email_code']);

	        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
	        if ($password != $c_password) {
	            $errors[] = "Passwords don't match";
	        } else if (strlen($password) < 4 || strlen($password) > 32) {
	            $errors[] = "Password is too short";
	        }

	        
	    }
	}
}

if ($option == 'signup') {
	if (!empty($_POST)) {
	    if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['email']) || empty($_POST['c_password']) || empty($_POST['name'])) {
	        $errors[] = "Please check your details";
	    } else {
	        $username        = secure($_POST['username']);
	        $name            = secure($_POST['name']);
	        $password        = secure($_POST['password']);
	        $c_password      = secure($_POST['c_password']);
	        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
	        $email           = secure($_POST['email']);
	        if (UsernameExits($_POST['username'])) {
	            $errors[] = "This username is already taken";
	        }
	        if (strlen($_POST['username']) < 4 || strlen($_POST['username']) > 32) {
	            $errors[] = "Username length must be between 5 / 32";
	        }
	        if (!preg_match('/^[\w]+$/', $_POST['username'])) {
	            $errors[] = "Invalid username characters";
	        }
	        if (EmailExists($_POST['email'])) {
	            $errors[] = "This e-mail is already taken";
	        }
	        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
	            $errors[] = "This e-mail is invalid";
	        }
	        if ($password != $c_password) {
	            $errors[] = "Passwords don't match";
	        }
	        if (strlen($password) < 4) {
	            $errors[] = "Password is too short";
	        }

	        
	            $insert_data['language'] = $music->config->language;
	            if (!empty($_SESSION['lang'])) {
	                if (in_array($_SESSION['lang'], $langs)) {
	                    $insert_data['language'] = $_SESSION['lang'];
	                }
	            }
	            $user_id             = $db->insert(T_USERS, $insert_data);
	            
	        }
	    }
	}
}

?>