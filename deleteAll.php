<?php
require_once 'config.php';

if (isset($_GET['all'])) {
    $conn = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "DELETE FROM orders WHERE 1" ;
	
	$folder_path = realpath('./images/generated/');
	$files = glob($folder_path . '/*'); 
    foreach ($files as $file) {
		if (is_file($file)) unlink($file);
    }

    if ($conn->query($sql) === true) {
        echo "All deleted successfully";
        header('location:index.php');
    } else {
        echo "Error updating record: " . $conn->error;
    }
    $conn->close();
}
