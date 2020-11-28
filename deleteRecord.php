<?php
require_once 'config.php';

if (isset($_GET['id'])) {
    $conn = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);
    if ($conn->connect_error) {
        echo json_encode(['status' => 400, 'msg' => 'DB Connection failed.']);
        exit;
    }
    
    $order_id_n = $_GET['id'];
    $sql = "DELETE FROM orders WHERE order_id_n='$order_id_n'" ;
    
    $mask = 'images/generated/'.$_GET['id'].'_*.*';
    foreach (glob($mask) as $filepath) {
        unlink($filepath);
    }
    
    if ($conn->query($sql) === true) {
        echo json_encode(['status' => 200, 'msg' => 'Deleted successfully!']);
    } else {
        echo json_encode(['status' => 200, 'msg' => $conn->error]);
    }
    $conn->close();
}
