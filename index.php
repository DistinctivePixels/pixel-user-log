<?php
/*
Plugin Name: Pixel User Logs
Plugin URI: http://wordpress.org/plugins/hello-dolly/
Description: Simple user logs
Author: Danny Jones
Version: 1.0.0
Author URI: http://wordpress.org/plugins/hello-dolly/
*/

/*
* GRAB USER IP
*/
function pixel_user_logs_get_the_user_ip() {
    if ( !empty( $_SERVER[ 'HTTP_CLIENT_IP' ] ) ) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif ( !empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) ) {
        $ip = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
    } else {
        $ip = $_SERVER[ 'REMOTE_ADDR' ];
    }
    return $ip;
}

/*
* HANDLE LOGGING LOGINS
*/
function pixel_user_logs_add_to_log( $id, $user ) {

	$user = get_userdata( $user->ID );
	$user_roles = $user->roles;
	$user_role = array_shift( $user_roles );

	$tracking[time()] = array(
		'event'			=> 'Login',
		'time' 			=> date("Y/m/d, h:i:sa"),
		'ip' 			=> pixel_user_logs_get_the_user_ip(),
		'username'		=> $user->display_name,
		'role'			=> $user_role
	);

	$tracking_data = get_option( 'pixels_login_tracker' );

	if( is_array( $tracking_data ) ) {
		$tracking = $tracking + $tracking_data;
	}		

  	update_option( 'pixels_login_tracker', $tracking );

}

add_action( 'wp_login','pixel_user_logs_add_to_log', 1, 2 );

/*
* HANDLE LOGGING LOGOUTS 
*/
function pixel_user_logs_add_logouts_to_log( $user ) {

	$current_user = wp_get_current_user();
	$roles = $current_user->roles;
	$user_role = array_shift($roles);

	$tracking[time()] = array(
		'event'			=> 'Logout',
		'time' 			=> date("Y/m/d, h:i:sa"),
		'ip' 			=> pixel_user_logs_get_the_user_ip(),
		'username'		=> $current_user->display_name,
		'role'			=> $user_role
	);

	$tracking_data = get_option( 'pixels_login_tracker' );

	if( is_array( $tracking_data ) ) {
		$tracking = $tracking + $tracking_data;
	}		

  	update_option( 'pixels_login_tracker', $tracking );

}

add_action( 'wp_logout','pixel_user_logs_add_logouts_to_log', 1, 2 );

/*
* CREATE PAGE TO DISPLAY OUTPUT
*/
add_action('admin_menu', function() {
    add_options_page( 'Pixel User Log', 'Pixel User Log', 'manage_options', 'pixel-logs', 'pixel_logs_settings_page' );
});

/*
* HANDLE DISPLAYING OUTPUT
*/
function pixel_logs_settings_page() {
  ?>
    <div class="wrap">

    <h2 style="margin-bottom: 20px;"><?php echo __( 'Pixel User Logs', 'pixel-logs' ); ?></h2>

    <div id="settings-wrapper">
       
        <ul>
            <?php 

                $log = get_option('pixels_login_tracker');
                $i = 1;
                foreach ($log as $entry) {
                	if( $entry['event'] == 'Logout' ) {
						echo '<li style="font-size: 14px; margin-bottom: 15px;">'. $i . ' | <strong style="color:red;">'. $entry['event'] . '</strong> | Time: '. $entry['time'] .' | IP: '. $entry['ip'] .' | Username/Role: '. $entry['username'] .'/'. $entry['role'] .'</li>';
                	} else {
                		echo '<li style="font-size: 14px; margin-bottom: 15px;">'. $i . ' | <strong style="color:green;">'. $entry['event'] . '</strong> | Time: '. $entry['time'] .' | IP: '. $entry['ip'] .' | Username/Role: '. $entry['username'] .'/'. $entry['role'] .'</li>';
                	}
                    
                    $i++;
                }
            ?> 
        </ul>

	</div>

  <?php
}