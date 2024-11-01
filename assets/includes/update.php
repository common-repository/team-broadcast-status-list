<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php' );

if (!session_id()) {
    session_start();
}

global $wpdb;
$table_name = $wpdb->prefix . 'tbsl_channels';

$results = $wpdb->get_results("SELECT * FROM ".$table_name);

foreach ( $results as $result ) 
{

$id = $result->id;

$active = "active" . $id;
$sort = "sort" . $id;

if(isset($_POST[$active])){
	$activate = 1;
}else{
	$activate = 0;
}

$setSort = $_POST[$sort];

if(empty($setSort)){
	$sorting = 0;
}else{
	$sorting = intval( $setSort );
}

	$wpdb->update( 
		$table_name, 
		array(
			'active' => $activate,
			'sorting' => $sorting
		), 
		array( 'id' => $id)
	);

}
$_SESSION['update'] = "success";
	$location = wp_get_referer();
	wp_safe_redirect($location);