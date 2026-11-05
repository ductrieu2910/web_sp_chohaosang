<?php
// $link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . rtrim(dirname($_SERVER["SCRIPT_NAME"]), '/') . "/";
//$link = "https://vftfarms.com/";
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
      || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$link = ($https ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $base . "/";
define( 'URL', $link);
// Ảnh sản phẩm (document/?id=...) lấy từ server này; link trang và API giỏ hàng vẫn dùng URL ở trên.
// Muốn lấy ảnh từ chính site đang chạy thì đổi thành: define( 'IMAGE_URL', URL);
define( 'IMAGE_URL', 'https://vftfarms.com/');
// Mỗi khách một session riêng, lưu trong cookie vd_id (file tương ứng: session/<id>).
// Chỉ chấp nhận chữ, số và dấu "-" để không ai dùng _id / cookie ghi file ra ngoài thư mục session/.
$session_id = "";
if(isset($_COOKIE["vd_id"]) && is_string($_COOKIE["vd_id"]) && preg_match('/^[A-Za-z0-9-]{8,64}$/', $_COOKIE["vd_id"]))
{
	$session_id = $_COOKIE["vd_id"];
}
if(isset($_REQUEST['_id']) && is_string($_REQUEST['_id']) && preg_match('/^[A-Za-z0-9-]{1,64}$/', $_REQUEST['_id']))
{
	$session_id = $_REQUEST['_id'];
}
if($session_id == "")
{
	// Khách mới (chưa có cookie): tạo mã ngẫu nhiên dạng UUID và cấp cookie 10 năm
	$hex = bin2hex(random_bytes(16));
	$session_id = substr($hex, 0, 8)."-".substr($hex, 8, 4)."-".substr($hex, 12, 4)."-".substr($hex, 16, 4)."-".substr($hex, 20, 12);
	setcookie("vd_id", $session_id, [
		'expires' => time() + (10 * 365 * 24 * 60 * 60),
		'path' => '/',
		'httponly' => true,
		'samesite' => 'Lax',
	]);
	$_COOKIE["vd_id"] = $session_id;
}

define( 'ID', 'VFSC');
define( 'META_TITLE', 'Chuỗi an toàn thực phẩm Việt Nam');
define( 'META_KEYWORD', 'vftfarms.com');
define( 'META_DESCRIPTION', 'vftfarms.com');
define( 'DB_HOST', 'localhost' );
define( 'DB_PORT', '5434' );
define( 'DB_NAME', 'postgres' );
define( 'DB_USER', 'postgres' );
define( 'DB_PASSWORD', '123456' );
define( 'LANGUAGE', 'vi' );
define( 'COMPANY_ID', 'e741709c-6309-4704-a6dc-e339a8b4bf7f' );
define( 'CURRENCY_ID', '23' );
define( 'COMPANY_NAME', 'Vftfarms' );
define( 'COMPANY_LAT', '10.861177053727173' );
define( 'COMPANY_LNG', '106.74004260552694' );
define( 'CONTACT_EMAIL', 'info@vidu.vn');
define( 'CONTACT_TEL', '02432029199');
define( 'CONTACT_ADDRESS', 'Tp. Hồ Chí Minh');
define( 'SERVICE_URL', '');
define( 'WS_URL', '');
define( 'ODD_COLOR', "#fffde7");
define( 'EVEN_COLOR', "#fff");
define( 'thousands_point', ",");
define( 'decimal_point', ".");
define( 'date_format', "YYYY-MM-DD");
define( 'copyright', "All Rights Reserved by vftfarms.vn");
define( 'addons', "addons");
define( 'logo', "");
define( 'db_type', "postgresql");
define( 'developer', "0");
define( 'HOME', "home");
define( 'SUPPORT_EMAIL', "no-reply@vidu.vn");
define( 'DEFAULT_DOC_PATH', "");
define( 'GOOGLE_API_KEY', 'AIzaSyA3mDc_R4Q5mX_AU9UMwsJbjljyTPvpqfM');

// Header / footer OCOP: website chính và thông tin liên hệ
define( 'MAIN_SITE_URL', 'https://chohaosang.vn/');
define( 'SITE_BRAND_NAME', 'OCOP Thủy Sản');
define( 'SITE_HOTLINE_DISPLAY', '0976 884 598');
define( 'SITE_HOTLINE_TEL', '0976884598');
define( 'SITE_EMAIL', '');			// để trống: ẩn nút Email ở footer
define( 'SITE_WORKING_HOURS', '');	// để trống: ẩn dòng giờ làm việc trong menu mobile
define( 'TAWK_PROPERTY_ID', '');	// để trống: ẩn các nút Chat trực tiếp
define( 'TAWK_WIDGET_ID', 'default');
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

if(DEFAULT_DOC_PATH == "")
{
	define("DOC_PATH", dirname( __FILE__ )."/disk/");
}else{
	define("DOC_PATH", DEFAULT_DOC_PATH);
}
	
require_once( ABSPATH . 'app/AppSession.php' );
require_once( ABSPATH . 'app/data/DataTier.php' );
require_once( ABSPATH . 'app/data/PostgreSQLTier.php' );

$appSession = new AppSession($session_id);
if($appSession->getConfig()->hasKey("currency_id") == false)
{
	$appSession->getConfig()->setProperty("currency_id", CURRENCY_ID);
}
if($appSession->getConfig()->hasKey("thousands_sep") == false)
{
	$appSession->getConfig()->setProperty("thousands_sep", thousands_point);
}

if($appSession->getConfig()->hasKey("decimal_point") == false)
{
	$appSession->getConfig()->setProperty("decimal_point", decimal_point);
}
if($appSession->getConfig()->hasKey("date_format") == false)
{
	$appSession->getConfig()->setProperty("date_format", date_format);
}
if($appSession->getConfig()->getProperty("addons") == "")
{
	$appSession->getConfig()->setProperty("addons", addons);
}
$appSession->getConfig()->setProperty("service_url", SERVICE_URL);
$appSession->getConfig()->setProperty("ws_url", WS_URL);
$appSession->getConfig()->setProperty("company_id", COMPANY_ID);
if(db_type == "postgresql")
{
	$dataTier = new PostgreSQLTier(DB_NAME, DB_HOST, DB_PORT, DB_USER, DB_PASSWORD);
	$appSession->setTier($dataTier);
}else{
$dataTier = new DataTier(SERVICE_URL, "", "");
$appSession->setTier($dataTier);
}


require_once(ABSPATH.'app/lang/Language.php' );
$session_id = $appSession->getConfig()->getProperty("session_id");
if($session_id == "")
{
	$session_id = $appSession->getTool()->getId();
	$appSession->getConfig()->setProperty("session_id", $session_id);
	$appSession->getConfig()->save();
}
$lang_id = $appSession->getConfig()->getProperty("lang_id");
if($lang_id == "")
{
	if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
	{
		$default_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		$sql = "SELECT id FROM res_lang WHERE code iLIKE '%".$default_lang."%' AND status =0";
		$msg = $appSession->getTier()->createMessage();
		$msg->add("query", $sql);
		$arr = $appSession->getTier()->getArray($msg);
		if(count($arr)>0)
		{
			$lang_id = $arr[0][0];
			
		}else
		{
			$lang_id = "vi";
		}
		$appSession->getConfig()->setProperty("lang_id", $lang_id);
		$appSession->getConfig()->save();
	}
}
$lang_id = "vi";
$appSession->getConfig()->setProperty("lang_id", $lang_id);
$routing = new Language();
$routing->load($appSession->getTier(), "web_routes", $lang_id);