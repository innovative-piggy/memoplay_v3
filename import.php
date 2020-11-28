<?php
require_once 'config.php';

if (!empty($_FILES["orders_file"]["name"])) {
    $connect = mysqli_connect(SERVERNAME, USERNAME, PASSWORD, DBNAME);
    $output = '';
    $allowed_ext = array("csv");
    $extension = explode(".", $_FILES["orders_file"]["name"]);
    $extension = end($extension);
    mysqli_set_charset($connect, "utf8mb4_unicode_ci");

    if (in_array($extension, $allowed_ext)) {
        $file_data = fopen($_FILES["orders_file"]["tmp_name"], 'r');

        $rows = [];
        while (($row = fgetcsv($file_data)) !== false) {
            $rows[] = $row;
        }
        
        $num_rows = count($rows);
        $tot = 0; $tt = 0;
        for ($i = 0; $i < $num_rows; $i++) {
            $row = $rows[$i];
            if ($row[14] === "open" && strpos(($SKU = $row[17]), "MEMOPLAYGlass") !== FALSE) {
                $order_id           = mysqli_real_escape_string($connect, substr($row[0], 1));
                $title              = mysqli_real_escape_string($connect, $row[1]);
                $email              = mysqli_real_escape_string($connect, $row[4]);
                $firstname          = mysqli_real_escape_string($connect, filter_filename($row[5]));
                $lastname           = mysqli_real_escape_string($connect, filter_filename($row[6]));
                $shipping_address   = mysqli_real_escape_string($connect, $row[7]);
                $city               = mysqli_real_escape_string($connect, $row[9]);
                $country            = mysqli_real_escape_string($connect, $row[10]);
                $datep              = mysqli_real_escape_string($connect, $row[11]);
                $taille             = mysqli_real_escape_string($connect, $row[12]);
                $order_id_n         = mysqli_real_escape_string($connect, $order_id . '_' . $row[15]);
                $quantity           = mysqli_real_escape_string($connect, $row[16]);

                $tt++;
                if (strpos($SKU, "2") !== FALSE && $rows[$i + 1][2] !== "Titre de musique") { // if UPSELL that has not its own data (texts, cover_image, spotify_code)
                    $query = "INSERT INTO orders(order_id_n, order_id, title, email, firstname, lastname, shipping_address, city, country, datep, taille, quantity, SKU) VALUES ('$order_id_n', '$order_id', '$title', '$email', '$firstname', '$lastname', '$shipping_address', '$city', '$country', '$datep', '$taille', '$quantity', '$SKU')";
                    $tot += (mysqli_query($connect, $query) === true);
                    continue;
                }

                $titre_musique          = mysqli_real_escape_string($connect, remove_unneed_letters($rows[++$i][3]));
                $phrase_personnalisee   = mysqli_real_escape_string($connect, $rows[++$i][3]);
                $cover_image            = mysqli_real_escape_string($connect, $rows[++$i][3]);
                $spotify_code           = mysqli_real_escape_string($connect, $rows[++$i][3]);

                $query = "INSERT INTO orders(order_id_n, order_id, title, email, firstname, lastname, shipping_address, city, country, titre_musique, phrase_personnalisee, cover_image, spotify_code, datep, taille, quantity, SKU) VALUES ('$order_id_n', '$order_id', '$title', '$email', '$firstname', '$lastname', '$shipping_address', '$city', '$country', '$titre_musique', '$phrase_personnalisee', '$cover_image', '$spotify_code', '$datep', '$taille', '$quantity', '$SKU')";
                $tot += (mysqli_query($connect, $query) === true);
            }
        }
        
        echo json_encode(['status' => 200, 'tot' => $tot, 'unique_upsell_cnt' => auto_unique_upsell()]);
    } else {
        echo json_encode(['status' => 400, 'msg' => 'Unsupported File!']);
    }
} else {
    echo json_encode(['status' => 400, 'msg' => 'Empty File!']);
}
