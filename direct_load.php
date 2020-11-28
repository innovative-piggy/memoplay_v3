<?php
require_once 'config.php';

define('APIKEY', '34012b12176cd1d50c6254d8d7616326');
define('APIPWD', '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');
define('DOMAIN', 'memoplay2.myshopify.com/admin/api/2020-10/');

$connect = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);
$query = $connect->query("SELECT o.shopify_id FROM orders o WHERE NOT ISNULL(o.shopify_id) ORDER BY o.shopify_id DESC");
$since_id = 0;
if ($query->num_rows > 0) {
    $row = $query->fetch_assoc();
    $since_id = $row['shopify_id'];
}
$connect->close();

$tot = 0;
$success_cnt = 0;
$fail_cnt = 0;
$upsell_cnt = 0;

$rlt = true;
while (1) {
    $data = shopify_call(
        NULL, 
        "orders.json", 
        [
            'status' => 'open', 
            'fields' => 'id,order_number,email,line_items,customer,shipping_address,created_at', 
            'limit' => 250, 
            // 'since_id' => $since_id
        ]
    );
    
    $rlt = save_to_db($data);
    if ($rlt === FALSE || $rlt === TRUE) break;

    $tot += $rlt['tot'];
    $success_cnt += $rlt['success_cnt'];
    $fail_cnt += $rlt['fail_cnt'];
    $upsell_cnt += $rlt['upsell_cnt'];

    $since_id = $rlt['since_id'];
    
    break;
}

if ($rlt === FALSE) {
    echo json_encode(['status' => 400, 'msg' => 'Load Failuer!']);
} else {
    echo json_encode([
        'status' => 200,
        'tot' => $tot,
        'success_cnt' => $success_cnt,
        'fail_cnt' => $fail_cnt,
        'upsell_cnt' => $upsell_cnt
    ]);
}

function save_to_db($rlt) {
    if (!isset($rlt['response'])) return false;
    $rlt = json_decode($rlt['response'], true, 1024, JSON_OBJECT_AS_ARRAY);
    if (!isset($rlt['orders'])) return false;
    if (count($rlt['orders']) == 0) return true;
    
    $rlt = $rlt['orders'];
    
    $connect = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);
    $connect->set_charset("utf8mb4_unicode_ci");
    
    $tot = count($rlt);
    $success_cnt = 0;
    $fail_cnt = 0;
    $since_id = 0;
    
    foreach ($rlt as $row) {
        $since_id = $row['id'];
        if (strpos(($SKU = $row['line_items'][0]['sku']), "MEMOPLAYGlass") === false) continue;
    
        $shopify_id = $row['id'];
    
        $order_id           = $connect->real_escape_string($row['order_number']);
        $title              = $connect->real_escape_string($row['line_items'][0]['title']);
        $email              = $connect->real_escape_string($row['email']);
        $firstname          = $connect->real_escape_string(filter_filename($row['customer']['first_name']));
        $lastname           = $connect->real_escape_string(filter_filename($row['customer']['last_name']));
        $shipping_address   = $connect->real_escape_string($row['shipping_address']['address1']);
        $city               = $connect->real_escape_string($row['shipping_address']['city']);
        $country            = $connect->real_escape_string($row['shipping_address']['country']);
        $datep              = $connect->real_escape_string($row['created_at']);
        $taille             = $connect->real_escape_string($row['line_items'][0]['variant_title']);
        $order_id_n         = $connect->real_escape_string($order_id . '_' . $row['line_items'][0]['fulfillable_quantity']);
        $quantity           = $connect->real_escape_string($row['line_items'][0]['quantity']);
    
        if (strpos($SKU, "2") !== FALSE && !isset($row['line_items'][0]['properties'])) { // if UPSELL that has not its own data (texts, cover_image, spotify_code)
            $query = "INSERT INTO orders(order_id_n, order_id, title, email, firstname, lastname, shipping_address, city, country, datep, taille, quantity, SKU, shopify_id) VALUES ('$order_id_n', '$order_id', '$title', '$email', '$firstname', '$lastname', '$shipping_address', '$city', '$country', '$datep', '$taille', '$quantity', '$SKU', '$shopify_id')";
            if (mysqli_query($connect, $query) === true) $success_cnt++;
            else $fail_cnt++;
            continue;
        }
        
        $titre_musique          = $connect->real_escape_string(remove_unneed_letters($row['line_items'][0]['properties'][0]['value']));

        $phrase_personnalisee   = $connect->real_escape_string($row['line_items'][0]['properties'][1]['value']);
        // $$$ remove strange character code as if it looks like emoji letter
        $phrase_personnalisee   = json_decode(str_replace('\ufe0f', '', json_encode($phrase_personnalisee)));
        // $$$

        $cover_image            = $connect->real_escape_string($row['line_items'][0]['properties'][2]['value']);
        $spotify_code           = $connect->real_escape_string($row['line_items'][0]['properties'][3]['value']);
        
        $query = "INSERT INTO orders(order_id_n, order_id, title, email, firstname, lastname, shipping_address, city, country, titre_musique, phrase_personnalisee, cover_image, spotify_code, datep, taille, quantity, SKU, shopify_id) VALUES ('$order_id_n', '$order_id', '$title', '$email', '$firstname', '$lastname', '$shipping_address', '$city', '$country', '$titre_musique', '$phrase_personnalisee', '$cover_image', '$spotify_code', '$datep', '$taille', '$quantity', '$SKU', '$shopify_id')";
        if (mysqli_query($connect, $query) === true) $success_cnt++;
        else $fail_cnt++;
    }

    $connect->close();

    return [
        'tot' => $tot,
        'success_cnt' => $success_cnt,
        'fail_cnt' => $fail_cnt,
        'upsell_cnt' => auto_upsell(),
        'since_id' => $since_id
    ];
}

function shopify_call($token, $api_endpoint,  $query = array(), $method = 'GET', $request_headers = array()) {
    // Build URL
    $url = "https://" . APIKEY . ":" . APIPWD . "@" . DOMAIN . $api_endpoint;
    if (!is_null($query) && in_array($method, array('GET', 'DELETE'))) {
        $url = $url . "?" . http_build_query($query);
    }

    // Configure cURL
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 3);
    // curl_setopt($curl, CURLOPT_SSLVERSION, 3);
    curl_setopt($curl, CURLOPT_USERAGENT, 'My New Shopify App v.1');
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curl, CURLOPT_TIMEOUT, 300);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

    // Setup headers
    $request_headers[] = "";
    if (!is_null($token)) {
        $request_headers[] = "X-Shopify-Access-Token: " . $token;
    }
    curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);

    if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
        if (is_array($query)) {
            $query = http_build_query($query);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
    }

    // Send request to Shopify and capture any errors
    $response = curl_exec($curl);
    $error_number = curl_errno($curl);
    $error_message = curl_error($curl);

    // Close cURL to be nice
    curl_close($curl);

    // Return an error is cURL has a problem
    if ($error_number) {
        return $error_message;
    } else {

        // No error, return Shopify's response by parsing out the body and the headers
        $response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);

        // Convert headers into an array
        $headers = array();
        $header_data = explode("\n", $response[0]);
        $headers['status'] = $header_data[0]; // Does not contain a key, have to explicitly set
        array_shift($header_data); // Remove status, we've already set it above
        foreach ($header_data as $part) {
            $h = explode(":", $part);
            $headers[trim($h[0])] = trim($h[1]);
        }

        // Return headers and Shopify's response
        return array('headers' => $headers, 'response' => $response[1]);
    }
}
