<?php
/*
Plugin Name: Team Broadcast Status List
Description: Team Broadcast Status List for Twitch displays the current online/offline status of a group of twitch accounts.  It will display it in a nice list style similar to friend lists within gaming applications.  You can easily enter as many twitch channel names to follow as you would like.  When the user is online it will move them to the top of the status list showing them online along with the current game they are playing.  Note: If you have upgraded from 1.1.1 to 1.2.0 or newer, you will need to deactivate and activate it so the database components install properly.
Version: 1.2.4
Author: Grant S. & Ben C.
*/
/* Start Adding Functions Below this Line */
// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');	

if (!session_id()) {
    session_start();
}
	
class TBSLSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;


    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    	add_action( 'admin_enqueue_scripts', 'mw_enqueue_color_picker' );
		
		function mw_enqueue_color_picker( $hook_suffix ) {
		// first check that $hook_suffix is appropriate for your admin page
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'my-script-handle', plugins_url('my-script.js', __FILE__ ), 
		array( 'wp-color-picker' ), false, true );
		}
	}



    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Team Broadcast Status List Settings', 
            'TBSL Settings', 
            'manage_options', 
            'tbsl-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }



    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'tbsl_option_name' );
        ?>
		<div class="wrap">
		<h2>Team Broadcast Status List Settings</h2>

		<script type="text/javascript">
		
			function changeTab($el){
			jQuery("#tbsl-tab1").css("display","none");
			jQuery("#tbsl-tab2").css("display","none");
			jQuery("#tbsl-btn1").css("border-bottom","1px solid #23282d");
			jQuery("#tbsl-btn2").css("border-bottom","1px solid #23282d");
			$currenttab = "#tbsl-tab" + $el;
			$currentbtn = "#tbsl-btn" + $el;
			jQuery($currenttab).css("display","block");
			jQuery($currentbtn).css("border-bottom","2px solid #6441a5");
			}
		
		</script>

		<div id="tbsl-btn-wrapper" style="position:absolute; top:20px; right:15px; height:30px;float:left;">
		
			<div id="tbsl-lspacer" style="width:325px; height:30px; border-bottom: 1px solid #23282d; display:block; float:left"></div>
			
			<div id="tbsl-btn2" style="height:26px; border-bottom: 2px solid #6441a5;float:left; padding: 2px 10px 2px 10px; line-height:25px; cursor:pointer;" onclick="changeTab('2')">Channels</div>
			<div id="tbsl-btn1" style="height:26px; border-bottom:1px solid #23282d; float:left; padding: 2px 10px 2px 10px; line-height:25px; cursor:pointer;" onclick="changeTab('1')">Customize</div>
			<div id="tbsl-rspacer" style="width:20px; height:30px; border-bottom: 1px solid #23282d; display:block; float:left"></div>
		
		</div>

		<div id="tbsl-wrapper">
		<div id="tbsl-tab1" style="display:none;">
		<form action="options.php" method="post">
		<?php
                // This prints out all hidden setting fields
                settings_fields( 'tbsl_option_group' );   
                do_settings_sections( 'tbsl-setting-admin' );
                submit_button(); 
            ?>
		</form>
		</div>
		
		
		<?php
		//Select, Update and Insert Functions
		
		function listChannels() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'tbsl_channels';

			$results = $wpdb->get_results("SELECT * FROM ".$table_name." ORDER BY sorting ASC");

			echo "<table cellpadding='5'><thead><th>Active</th><th>Channel Name</th><th>Sort</th><th>Delete</th></thead>";
			foreach ( $results as $result ) 
			{
				if($result->active == 0){
					$active = "<input type='checkbox' id='tbsl-activate' name='active". $result->id ."'";
				}else{
					$active = "<input type='checkbox' id='tbsl-activate' name='active". $result->id ."' checked='true'";
				}
				echo "<tr><td>" . $active . "</td>";
				echo "<td>" . $result->name . "</td>";
				echo "<td><input type='text' id='tbsl-sorvalue' name='sort". $result->id ."' value='" . $result->sorting . "' size='1'></td>";
				echo "<td><a href='' onclick='deleteChannel(". $result->id .")'>Delete</a></td>";
			}
			echo "</table>";
		}
		
		?>
        <script type="text/javascript">
		function deleteChannel(id){
			var ajaxRequest;  // The variable that makes Ajax possible!
		
			try{
				// Opera 8.0+, Firefox, Safari
				ajaxRequest = new XMLHttpRequest();
			} catch (e){
				// Internet Explorer Browsers
				try{
					ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
					try{
						ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
					} catch (e){
						// Something went wrong
						alert("Your browser broke!");
						return false;
					}
				}
			}
			// Create a function that will receive data sent from the server
			ajaxRequest.onreadystatechange = function(){
				if(ajaxRequest.readyState == 4){
					var ajaxDisplay = document.getElementById('err-response');
					ajaxDisplay.innerHTML = ajaxRequest.responseText;
					ajaxDisplay.style.display = "block";
				}
			}
			var queryString = "?id=" + id;
			ajaxRequest.open("GET", "<?php echo plugins_url ( 'assets/includes/delete.php', __FILE__ )?>" + queryString, true);
			ajaxRequest.send(null);
		}

		</script>

		<div id="tbsl-tab2">
		Manage channel settings below:<br /><br />

		<?php 
			if(isset($_SESSION['update'])){
				echo "<div id='message' class='updated notice is-dismissible'><p><strong>Settings saved.</strong></p></div>";
				unset($_SESSION['update']);
				}
				
			?>

		<form id="channel-name" action="<?php echo plugins_url ( 'assets/includes/process.php', __FILE__ )?>" method="POST">
		<label for="dname">Twitch Channel Name:</label>
		<input type="text" id="tbsl-chname" name="tbsl-chname" value="" />
		<input type="submit" id="tbsl-submit" class="button button-primary" name="submit" value="Add Channel" />
		</form>

		<div id="err-response" style="margin-top:10px; margin-bottom:20px; color:red;"><?php if(isset($_SESSION['error'])){echo $_SESSION['error'];unset($_SESSION['error']);} ?></div>
		<div id="tbsl-curchannels">
        
        <form action="<?php echo plugins_url ( 'assets/includes/update.php', __FILE__ )?>" method="post">
		
		<?php listChannels(); ?>
		<input type="submit" id="update-channels" name="update-channels" value="Save Changes" class="button button-primary" style="margin-top:20px;"/>
        </form>
        
		</div>

		</div>
		</div>
		<?php
		
		
    }




    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'tbsl_option_group', // Option group
            'tbsl_option_name', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            '', // Title
            array( $this, 'print_section_info' ), // Callback
            'tbsl-setting-admin' // Page
        );  

        add_settings_field(
            'tbsl_onhdr_color', // ID
            'Header Online Background Color', // Title 
            array( $this, 'tbsl_onhdr_color_set' ), // Callback
            'tbsl-setting-admin', // Page
            'setting_section_id' // Section           
        );      
        add_settings_field(
            'tbsl_offhdr_color', // ID
            'Header Offline Background Color', // Title 
            array( $this, 'tbsl_offhdr_color_set' ), // Callback
            'tbsl-setting-admin', // Page
            'setting_section_id' // Section           
        );
		        add_settings_field(
            'tbsl_onhdrfont_color', // ID
            'Header Online Font Color', // Title 
            array( $this, 'tbsl_onhdrfont_color_set' ), // Callback
            'tbsl-setting-admin', // Page
            'setting_section_id' // Section           
        );      
        add_settings_field(
            'tbsl_offhdrfont_color', // ID
            'Header Offline Font Color', // Title 
            array( $this, 'tbsl_offhdrfont_color_set' ), // Callback
            'tbsl-setting-admin', // Page
            'setting_section_id' // Section           
        );      
        add_settings_field(
            'tbsl_onchfont_color', // ID
            'Channel Online Font Color', // Title 
            array( $this, 'tbsl_onchfont_color_set' ), // Callback
            'tbsl-setting-admin', // Page
            'setting_section_id' // Section           
        );      
        add_settings_field(
            'tbsl_offchfont_color', // ID
            'Channel Offline Font Color', // Title 
            array( $this, 'tbsl_offchfont_color_set' ), // Callback
            'tbsl-setting-admin', // Page
            'setting_section_id' // Section           
        );
		add_settings_field(
            'tbsl_playcount', // ID
            'Maximum Characters Allowed for Currently Playing Line', // Title 
            array( $this, 'tbsl_playcount_set' ), // Callback
            'tbsl-setting-admin', // Page
            'setting_section_id' // Section           
        );  
        add_settings_field(
            'tbsl_noimage', // ID
            'Custom Image for Accounts Without', // Title 
            array( $this, 'tbsl_noimage_set' ), // Callback
            'tbsl-setting-admin', // Page
            'setting_section_id' // Section           
        );      
        add_settings_field(
            'tbsl_ononly', // ID
            'Show Online Portion Only', // Title 
            array( $this, 'tbsl_ononly_set' ), // Callback
            'tbsl-setting-admin', // Page
            'setting_section_id' // Section           
        );         
     
    }



    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
		
        $new_input = array();
        if( isset( $input['tbsl_onhdr_color'] ) && preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $input['tbsl_onhdr_color'] ) )
            $new_input['tbsl_onhdr_color'] = $input['tbsl_onhdr_color'];

        if( isset( $input['tbsl_offhdr_color'] ) && preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $input['tbsl_offhdr_color'] ) )
            $new_input['tbsl_offhdr_color'] = $input['tbsl_offhdr_color'];
			
        if( isset( $input['tbsl_onhdrfont_color'] ) && preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $input['tbsl_onhdrfont_color'] ) )
            $new_input['tbsl_onhdrfont_color'] = $input['tbsl_onhdrfont_color'];

        if( isset( $input['tbsl_offhdrfont_color'] ) && preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $input['tbsl_offhdrfont_color'] ) )
            $new_input['tbsl_offhdrfont_color'] = $input['tbsl_offhdrfont_color'];

        if( isset( $input['tbsl_onchfont_color'] ) && preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $input['tbsl_onchfont_color'] ) )
            $new_input['tbsl_onchfont_color'] = $input['tbsl_onchfont_color'];

        if( isset( $input['tbsl_offchfont_color'] ) && preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $input['tbsl_offchfont_color'] ) )
            $new_input['tbsl_offchfont_color'] = $input['tbsl_offchfont_color'];

        if( isset( $input['tbsl_playcount'] ) )
            $new_input['tbsl_playcount'] = intval( $input['tbsl_playcount'] );

        if( isset( $input['tbsl_noimage'] ) )
            $new_input['tbsl_noimage'] = sanitize_text_field( $input['tbsl_noimage'] );

        if( isset( $input['tbsl_ononly'] ) )
            $new_input['tbsl_ononly'] = sanitize_text_field( $input['tbsl_ononly'] );

        return $new_input;
    }



    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Customize the look of the widget below:';
    }



    /** 
     * Get the settings option array and print one of its values
     */
    public function tbsl_onhdr_color_set()
    {
        printf(
            '<script type="text/javascript">jQuery(document).ready(function($){$(\'.my-color-field\').wpColorPicker();});</script>						
			<input type="text" id="tbsl_onhdr_color" name="tbsl_option_name[tbsl_onhdr_color]" value="%s" class="my-color-field" maxlength="7"/>',
            isset( $this->options['tbsl_onhdr_color'] ) ? esc_attr( $this->options['tbsl_onhdr_color']) : ''
        );
    }
	
	public function tbsl_offhdr_color_set()
    {
        printf(
            '<input type="text" id="tbsl_offhdr_color" name="tbsl_option_name[tbsl_offhdr_color]" value="%s" class="my-color-field" maxlength="7"/>',
            isset( $this->options['tbsl_offhdr_color'] ) ? esc_attr( $this->options['tbsl_offhdr_color']) : ''
        );
    }
	
	public function tbsl_onhdrfont_color_set()
    {
        printf(
            '<input type="text" id="tbsl_onhdrfont_color" name="tbsl_option_name[tbsl_onhdrfont_color]" value="%s" class="my-color-field" maxlength="7"/>',
            isset( $this->options['tbsl_onhdrfont_color'] ) ? esc_attr( $this->options['tbsl_onhdrfont_color']) : ''
        );
    }

	public function tbsl_offhdrfont_color_set()
    {
        printf(
            '<input type="text" id="tbsl_offhdrfont_color" name="tbsl_option_name[tbsl_offhdrfont_color]" value="%s" class="my-color-field" maxlength="7"/>',
            isset( $this->options['tbsl_offhdrfont_color'] ) ? esc_attr( $this->options['tbsl_offhdrfont_color']) : ''
        );
    }

	public function tbsl_onchfont_color_set()
    {
        printf(
            '<input type="text" id="tbsl_onchfont_color" name="tbsl_option_name[tbsl_onchfont_color]" value="%s" class="my-color-field" maxlength="7"/>',
            isset( $this->options['tbsl_onchfont_color'] ) ? esc_attr( $this->options['tbsl_onchfont_color']) : ''
        );
    }

	public function tbsl_offchfont_color_set()
    {
        printf(
            '<input type="text" id="tbsl_offchfont_color" name="tbsl_option_name[tbsl_offchfont_color]" value="%s" class="my-color-field" maxlength="7"/>',
            isset( $this->options['tbsl_offchfont_color'] ) ? esc_attr( $this->options['tbsl_offchfont_color']) : ''
        );
    }
	
	public function tbsl_playcount_set()
    {
        printf(
            '<input type="text" id="tbsl_playcount" name="tbsl_option_name[tbsl_playcount]" value="%s" maxlength="3" size="1"/>',
            isset( $this->options['tbsl_playcount'] ) ? esc_attr( $this->options['tbsl_playcount']) : ''
        );
    }

	public function tbsl_noimage_set()
    {
		
		// jQuery
		wp_enqueue_script('jquery');
		// This will enqueue the Media Uploader script
		wp_enqueue_media();
        
		printf(
            '<input type="text" id="image_url" name="tbsl_option_name[tbsl_noimage]" value="%s" />',
            isset( $this->options['tbsl_noimage'] ) ? esc_attr( $this->options['tbsl_noimage']) : ''
        ); ?>
		
		<input type="button" name="upload-btn" id="upload-btn" class="button-secondary" value="Upload Image">

		<script type="text/javascript">
			jQuery(document).ready(function($){
				$('#upload-btn').click(function(e) {
					e.preventDefault();
					var image = wp.media({ 
					title: 'Upload Image',
					// mutiple: true if you want to upload multiple files at once
					multiple: false
				}).open()
					.on('select', function(e){
						// This will return the selected image from the Media Uploader, the result is an object
						var uploaded_image = image.state().get('selection').first();
						// We convert uploaded_image to a JSON object to make accessing it easier
						// Output to the console uploaded_image
						console.log(uploaded_image);
						var image_url = uploaded_image.toJSON().url;
						// Let's assign the url value to the input field
						$('#image_url').val(image_url);
					});
				});
			});
		</script>


		<?php
    }

	public function tbsl_ononly_set()
    {
	?>
		<?php $options = get_option( 'tbsl_option_name' ); ?>
		<input type="checkbox" id="tbsl_ononly" name="tbsl_option_name[tbsl_ononly]" value="1" <?php checked( isset( $options['tbsl_ononly'] ) ); ?> />
	
	<?php
	}
}


/**
 * Start Widget.
 */
if( is_admin() )
    $my_settings_page = new TBSLSettingsPage();
	
	add_action( 'widgets_init', function(){
    register_widget( 'Team_Broadcast_Status_List' );
	});	



/**
 * Adds My_Widget widget.
 */
class Team_Broadcast_Status_List extends WP_Widget {

	
	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'Team_Broadcast_Status_List', // Base ID
			__('Team Broadcast Status List', 'text_domain'), // Name
			array( 'description' => __( 'Displays the current online/offline status of a group of twitch accounts.', 'text_domain' ), ) // Args
		);
	}


	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
	
    	echo $args['before_widget'];

		echo "<link rel='stylesheet' href='" . plugins_url ( 'assets/css/main.css', __FILE__ ) . "'>";
         
		global $wpdb;
		$table_name = $wpdb->prefix . 'tbsl_channels';

		$results = $wpdb->get_results("SELECT name FROM ".$table_name." WHERE active = '1' ORDER BY sorting ASC");
		$channels ="";
		$last = count($results);
		$count = 0;
			foreach ( $results as $result ) 
			{
				$channels .= $result->name;
				$count = ++$count;
				if($count != $last){
					$channels .= ",";
				}
			}
        //array of users to follow online status
		if ( ! empty( $channels ) ) {
			$channels = str_replace(' ', '', $channels); 
			$channels = explode(',', $channels);
			$channelName = $channels;
		
       		//changes all array values to lowercase
        	$channelName = array_map('strtolower', $channelName);
        	//sorts array alphabetically 
        	//sort($channelName);
            //counts the ammount of names in array
        	$count = count($channelName);
        
			$options = get_option( 'tbsl_option_name' );
		
		
	        $online = '<div id="online-title" style="background-color:'.$options['tbsl_onhdr_color'] .'; color:'.$options['tbsl_onhdrfont_color'].'">ONLINE</div>';     // creates online variable
    	    $offline = '<div id="offline-title"style="background-color:'.$options['tbsl_offhdr_color'] .'; color:'.$options['tbsl_offhdrfont_color'].'">OFFLINE</div>';   // creates offline variable
        	$printOnline = 0;
        
			for($i=0;$i<$count;$i++){ //loops through users
        
    		    $clientId = 'tdtn2k12gw0xuru04uzx2crou3fsf8r';             
				// Register your application and get a client ID at http://www.twitch.tv/settings?section=applications	
				
				if(!@file_get_contents('https://api.twitch.tv/kraken/channels/'.strtolower($channelName[$i]).'?client_id='.$clientId)){
					
					echo "Twitch Status List: Failed to find channel(s)";
					
				}else{		
				
					$json_array_offline = json_decode(@file_get_contents('https://api.twitch.tv/kraken/channels/'.strtolower($channelName[$i]).'?client_id='.$clientId), true); //pulls channels data of offline users
					$json_array = json_decode(@file_get_contents('https://api.twitch.tv/kraken/streams/'.strtolower($channelName[$i]).'?client_id='.$clientId), true); //pulls stream data of online users from api	
         
					if ($json_array['stream'] != NULL) { //checkes to see if stream is currently live
						$channelTitle = $json_array['stream']['channel']['display_name']; //users display name
						$streamTitle = $json_array['stream']['channel']['status']; //users stream title
						$currentGame = $json_array['stream']['channel']['game']; //current game user is streaming
						$channelLogo = $json_array['stream']['channel']['logo']; //users icon
						$channelView = $json_array['stream']['viewers']; //# of current viewers
			
						if(empty($channelLogo)){
						   $channelLogo = plugins_url ( 'assets/images/twitch-nopic.jpg', __FILE__ );
						}
			 
						$gameLength = strlen($currentGame);
						
						if(empty($options['tbsl_playcount'])){
							$countset = 15;
						}else{
							$countset = $options['tbsl_playcount'];
						}
						
						if($gameLength > $countset){
							$currentGame = substr($currentGame, 0 , $countset) . "...";
						}
					  
						//adds current user to variable
						$online .= "<div id='twitch-widget' class='usr-online'><a href='http://twitch.tv/" . $channelName[$i]. "' target='_blank' id='tbsl-user-name' class='tbsl-user-name'><img src='" . $channelLogo . "' alt='" . $channelName[$i] . " channel logo' id='icon'><div id='user-title' style='color:".$options['tbsl_onchfont_color'].";' >"	. substr($channelTitle, 0, 14) . "</div></a><div id='current-game' class='gray' style='color:".$options['tbsl_onchfont_color'].";'>Playing: " . $currentGame . "</div></div>";
						
						//Ask about using with the plugin TabSlide seen in one of the screenshots
						/*echo '<script type="text/javascript">jQuery(document).ready( function(){jQuery("#tab_title_wrap").css("background-image", "' . plugins_url ( 'assets/images/twitch_on.png', __FILE__ ) . '")});</script>';*/

				
						$printOnline = 1;
					
					} else {
			
						if(empty($json_array_offline['logo'])){
							if(!empty($options['tbsl_noimage'])){
								
							$upload_dir = wp_upload_dir();
	
							$channelLogoOffline = $options['tbsl_noimage'];
								
							}else{
							
							$channelLogoOffline = plugins_url ( 'assets/images/twitch-nopic.jpg', __FILE__ );
							}
						}else{
							$channelLogoOffline = $json_array_offline['logo'];
						}
				
						//add current user to variable
						$offline .= "<div id='twitch-widget' class='usr-offline'><a href='http://twitch.tv/" . $json_array_offline['display_name'] . "' target='_blank' id='tbsl-user-name' class='tbsl-user-name'><img src='" . $channelLogoOffline . "' alt='Twitch logo' id='icon' class='icon'><div id='user-title' class='gray' style='color:".$options['tbsl_offchfont_color'].";'>" . substr($json_array_offline['display_name'], 0, 14) . "</div></a><div id='current-game' class='offline' style='color:".$options['tbsl_offchfont_color'].";'>Offline</div></div>";
			
					}
				
				}
        
        	}
        	//prints all online and offline users to page
        	if($printOnline == 1){
				if(isset( $options['tbsl_ononly'] ) ){
					echo $online;
				}else{
		       	    echo $online . $offline;
				}
			}else{
				if(isset( $options['tbsl_ononly'] ) ){
					echo $online . "<span class='alloff'>All streamers are currently offline.</span>";
				}else{
            	echo  $online . "<span class='alloff'>All streamers are currently offline.</span>" . $offline;
				}
        	}
		
		}else{
			echo "Twitch Status List: No channels entered.";
			echo '<div id="tbsl-online-title">ONLINE</div><div id="tbsl-offline-title">OFFLINE</div>';		
		};

		echo $args['after_widget'];
	}




	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		
		?>
		<p>
			Add Channels In TBSL Settings
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 *
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['channels'] = ( ! empty( $new_instance['channels'] ) ) ? strip_tags( $new_instance['channels'] ) : '';

		return $instance;
	}*/

} // class My_Widget

/**
 * Add Table to Database
 */

global $jal_db_version;
$jal_db_version = '1.0';

function jal_install() {
	global $wpdb;
	global $jal_db_version;

	$table_name = $wpdb->prefix . 'tbsl_channels';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		name tinytext NOT NULL,
		active int NOT NULL,
		sorting int NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'jal_db_version', $jal_db_version );
}

function jal_install_data() {
	global $wpdb;
	
	$welcome_name = 'lowpolytv';
	$welcome_active = 1;
	$welcome_sort = 1;
	
	$table_name = $wpdb->prefix . 'tbsl_channels';
	
	$wpdb->insert( 
		$table_name, 
		array( 
			'name' => $welcome_name, 
			'active' => $welcome_active, 
			'sorting' => $welcome_sort
		) 
	);
}

register_activation_hook( __FILE__, 'jal_install' );
register_activation_hook( __FILE__, 'jal_install_data' );

/* Stop Adding Functions Below this Line */