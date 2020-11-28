<?php
require_once 'config.php';

$files = [];
if (is_dir(DIR)) {
	foreach (scandir(DIR) as $t) {
		if (is_file(DIR . $t)) {
			$files[] = $t;
		}
	}
}

$conn = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);
if ($conn->connect_error) exit;

$sql = "SELECT * FROM orders o ";
$result = $conn->query($sql);

$iTotalRecords = $result->num_rows;

$iDisplayLength = intval($_REQUEST['length']);
$iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;

$iDisplayStart = intval($_REQUEST['start']);
$sEcho = intval($_REQUEST['draw']);

$records = array();
$records["data"] = array();

if (!is_null($_REQUEST['search']['value'])) {
	$str = $_REQUEST['search']['value'];
	$sql .= "WHERE o.order_id_n LIKE '%$str%' OR o.email LIKE '%$str%' ";
}
$fields = ['o.order_id', 'o.datep', 'o.email', 'o.titre_musique', 'o.phrase_personnalisee', ''];
$sql .= "ORDER BY " . $fields[$_REQUEST['order'][0]['column']] . " " .  $_REQUEST['order'][0]['dir'] . " ";
$sql .= "LIMIT $iDisplayStart, $iDisplayLength ";

$result = $conn->query($sql);
$iFilteredRecords = $result->num_rows;
while ($row = $result->fetch_assoc()) {
	$action1 = '<a href="javascript:generate(\'' . $row["order_id_n"] . '\')">Generate</a>';
	$find_file = NULL;
	foreach ($files as $file) {
		if (strpos($file, strval($row["order_id_n"])) === 0) {
			$find_file = $file;
			break;
		}
	}
	if (!is_null($find_file)) $action1 .= '<hr><a href="javascript:preview(\'' . $find_file . '\')">Preview</a>';

	if (strpos($row['SKU'], "2") !== false && is_null($row['cover_image']))
		$action2 = '<a href="javascript:edit_upsell(\'' . $row["order_id_n"] . '\', \'' . $row['taille'] . '\', \'' . $row['firstname'] . ' ' . $row['lastname'] . '\')">UPSELL</a>';
	else 
		$action2 = '<a href="javascript:update(\'' . $row["order_id_n"] . '\')">Edit</a>';
	$action2 .= '<hr><a href="javascript:delete_record(\'' . $row["order_id_n"] . '\')">Delete</a>';

	$records["data"][] = array(
		$row['order_id'],
		date("Y-m-d (H:i:s)", strtotime($row['datep'])),
		$row['firstname'] . ' ' . $row['lastname'] . '<br>' . $row['email'] . '<br>' . $row['shipping_address'],
		$row['titre_musique'],
		$row['phrase_personnalisee'],
		$action1,
		$action2,
		'tr_' . $row['order_id_n']
	);
}

$records["draw"] = $sEcho;
$records["recordsTotal"] = $iTotalRecords;
$records["recordsFiltered"] = $iTotalRecords;

echo json_encode($records);
