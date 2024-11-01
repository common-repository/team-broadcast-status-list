<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php' );

global $wpdb;
$id = $_GET['id'];

$table_name = $wpdb->prefix . 'tbsl_channels';

$wpdb->delete($table_name, array('id' => $id));


echo "Deleting...";