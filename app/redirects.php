<?php
// Redirect 301 cho các trang đã gỡ: chuyển sang URL tương ứng trên website chính (MAIN_SITE_URL),
// không có trang tương ứng thì về trang chủ website chính. Đích "local:" là URL trên chính site này.
function removedPageRedirects()
{
	return [
		"home" => "/",
		"about" => "/gioi-thieu",
		"contact" => "/gioi-thieu#lien-he",
		"blog" => "/kien-thuc",
		"post" => "/kien-thuc",
		"faq" => "/hoi-dap",
		"sale_shipping" => "/chinh-sach/van-chuyen",
		"sale_refund" => "/chinh-sach/doi-tra",
		"sale_security" => "/chinh-sach/bao-mat",
		"privacy" => "/chinh-sach/bao-mat",
		"sale_agreement" => "/chinh-sach/dieu-khoan",
		"sale_payment" => "/cach-dat-hang",
		"chinh_sach" => "/",
		"sale_rule" => "/",
		"sale_support" => "/",
		"employment" => "/",
		"client" => "/",
		"quote" => "/",
		"dlapp" => "/",
		"thanks" => "/",
		"account" => "/",
		"tai-khoan" => "/",
		"profile" => "/",
		"loyalty" => "/",
		"wishlist" => "/",
		"delivery" => "/",
		"signin" => "/",
		"signin_otp" => "/",
		"signup" => "/",
		"signup_otp" => "/",
		"signup-success" => "/",
		"login" => "/",
		"forgot" => "/",
		"reset" => "/",
		"order" => "/",
		"order_detail" => "/",
		"product_popup" => "local:",
		"product" => "local:",
	];
}

function redirectRemovedPage($uri)
{
	$path = strtolower(trim($uri, "/"));
	if ($path == "") {
		return;
	}
	// URL danh mục / nhóm / chi tiết sản phẩm luôn được giữ
	if (preg_match('#/(c|g|p)-#', "/" . $path)) {
		return;
	}
	$segments = explode("/", $path);
	$key = $segments[0];
	if ($key == "addons" && count($segments) > 1) {
		$key = $segments[1];
	}
	$redirects = removedPageRedirects();
	if (!isset($redirects[$key])) {
		return;
	}
	$target = $redirects[$key];
	if (strpos($target, "local:") === 0) {
		$location = URL . substr($target, 6);
	} else {
		$location = rtrim(MAIN_SITE_URL, "/") . $target;
	}
	header("Location: " . $location, true, 301);
	exit();
}
?>
