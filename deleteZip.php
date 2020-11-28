<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION["admin"])) {
    header('location: ./login.php');
}

if (isset($_GET['id'])) {
    $conn = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "DELETE FROM zip WHERE id=". $_GET['id'] ;

    $filePath = realpath("zip/".$_GET['datep']."_".$_GET['startp']."_".$_GET['endp'].".zip");

    $deleted = unlink($filePath);
    if ($deleted) {
        echo 'File ' . $filePath . ' was deleted!';
    }

    if ($conn->query($sql) === true) {
        echo "Record deleted successfully";
        header('location: ./download_bulk.php');
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $conn->close();
}
