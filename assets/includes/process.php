<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php' );

if (!session_id()) {
    session_start();
}

$errors         = array();  	// array to hold validation errors
$data 			= array(); 		// array to pass back data
// validate the variables ======================================================
	// if any of these variables don't exist, add an error to our $errors array
	if (empty($_POST['tbsl-chname']))
		$errors['name'] = 'Name is required.';
		
// return a response ===========================================================
	// if there are any errors in our errors array, return a success boolean of false
	if ( ! empty($errors)) {
		// if there are items in our errors array, return those errors
		$data['success'] = false;
		$data['errors']  = $errors;
	} else {
		// if there are no errors process our form, then return a message
		// DO ALL YOUR FORM PROCESSING HERE
		// THIS CAN BE WHATEVER YOU WANT TO DO (LOGIN, SAVE, UPDATE, WHATEVER)
		// show a message of success and provide a true success variable
		$name = $_POST['tbsl-chname'];
		
		$cleanName = sanitize_text_field( $name );
		$cleanName = str_replace(' ', '', $cleanName); 
    	$clientId = 'tdtn2k12gw0xuru04uzx2crou3fsf8r';             
		if($json_array_offline = json_decode(@file_get_contents('https://api.twitch.tv/kraken/channels/'.strtolower($cleanName).'?client_id='.$clientId), true)){
			
			global $wpdb;
			$table_name = $wpdb->prefix . 'tbsl_channels';
			$duplicates = $wpdb->get_results("SELECT name FROM ".$table_name." WHERE name = '".$cleanName."'");
			foreach ($duplicates as $duplicate){
			if($duplicate->name == $cleanName){
				$location = wp_get_referer();
				wp_safe_redirect($location);
				$_SESSION['error'] = 'Channel Name has already been added.';
				die();
			}
			}
			
				$wpdb->insert(
					$table_name,
					array(
						'name' => $cleanName,
						'active' => "0",
						'sorting' => "0"
					)
				);
			
			$data['success'] = true;
			$data['message'] = 'Success!';
					
		}else{
			
			$_SESSION['error'] = 'Channel Name could not be found, please check spelling and try again.';
			
		}
	}
	// return all our data to an AJAX call
	$result = json_encode($data);
	
	$location = wp_get_referer();
	wp_safe_redirect($location);
	
	?>
