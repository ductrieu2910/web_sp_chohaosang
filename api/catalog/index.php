<?php
require_once(ABSPATH . 'api/Catalog.php');

$ac = "";
if (isset($_REQUEST['ac'])) {
	$ac = $_REQUEST['ac'];
}

if ($ac == "header_products") {
	$product = new Product($appSession);
	$catalog = new Catalog($appSession);
	$items = $catalog->headerProducts($product->categoryList());

	header('Content-Type: application/json; charset=utf-8');
	header('Cache-Control: public, max-age=300');
	echo json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit();
}
http_response_code(400);
echo "INVALID";
?>
