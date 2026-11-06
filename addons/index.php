<?php
require_once(ABSPATH . 'api/Product.php');


$page = "category";
$lang = "vi";
$page_id = '';

if ($uri != '/' && $uri != '') {
    $items = explode("/", $uri);
    if (count($items) == 1) {
        $page = $items[0];
    }
    if (count($items) > 0) {
        $page_id = $items[count($items) - 1];
    }
	if($page_id == "lang")
	{

		if (count($items) > 2)
		{
			//$lang_id = $items[count($items) - 2];

			//$appSession->getConfig()->setProperty("lang_id", $lang_id);
			//$appSession->getConfig()->save();
		}
		$page_id = $items[0];
	}
}
$search = "";
$sort = "";
$page_no = 1;
$arr = $appSession->getTool()->split($PARAMS, '&');

for($i =0; $i<count($arr); $i++)
{
	$index = $appSession->getTool()->indexOf($arr[$i], '=');
	if($index != -1)
	{

		$k = substr($arr[$i], 0, $index);
		$v = substr($arr[$i], $index + 1);

		if($k == "search")
		{
			$search = $v;

		}
		else if($k == "sort" && in_array($v, ["name", "price-asc", "price-desc"], true))
		{
			$sort = $v;
		}
		else if($k == "page")
		{
			$page_no = max(1, intval($v));
		}
	}
}

$langList =["vi", "en", "ru", "cn", "jp", "kr"];
$lang_id = "vi";
for($i=0; $i<count($langList); $i++)
{
	if($appSession->getConfig()->getProperty("lang_id") == $langList[$i])
	{
		$lang_id = $langList[$i];
		break;
	}
}

include( ABSPATH .'app/lang/'.$lang_id.'.php');
foreach($langs as $key => $item)
{
	$appSession->getLang()->setProperty($key, $item);
}


$msg = $appSession->getTier()->createMessage();

$product = new Product($appSession);
$dt_category = $product->categoryList();

// Trang đầu tiên của site là danh sách toàn bộ sản phẩm
$page = "category";

$selected_id = "";
$page_title = META_TITLE;
$page_keywords = META_KEYWORD;
$page_description = META_DESCRIPTION;
$document_id = "";
$document_ext = "";
$index = $appSession->getTool()->indexOf($uri, "/c-");
if ($index != -1) {
    $page = "category";
    $code = $appSession->getTool()->substring($uri, $index + 3);
    $code = $appSession->getTool()->replace($code, "#", "");
	$code = $appSession->getTool()->replace($code, "/", "");

    $sql = "SELECT d1.id, d1.name, d2.keywords, d2.description FROM product_category d1 LEFT OUTER JOIN meta d2 ON(d1.id = d2.rel_id AND d2.status=0 AND d2.publish=1) WHERE d1.code='" . $code . "' AND d1.status =0";

    $msg->add("query", $sql);
    $arr = $appSession->getTier()->getArray($msg);
    if (count($arr) > 0) {
        $selected_id = $arr[0][0];
        if ($arr[0][1] != "") {
            $page_title = META_TITLE . " - " . $arr[0][1];
        }
        if ($arr[0][2] != "") {
            $page_keywords = $arr[0][2];
        }
        if ($arr[0][3] != "") {
            $page_description = $arr[0][3];
        }
    }
}
$index = $appSession->getTool()->indexOf($uri, "/g-");
if ($index != -1) {
    $page = "group";
    $code = $appSession->getTool()->substring($uri, $index + 3);
    $code = $appSession->getTool()->replace($code, "#", "");
	$code = $appSession->getTool()->replace($code, "/", "");
    $sql = "SELECT d1.id, d1.name, d2.keywords, d2.description FROM product_group d1 LEFT OUTER JOIN meta d2 ON(d1.id = d2.rel_id AND d2.status=0 AND d2.publish=1) WHERE d1.code='" . $code . "'";

    $msg->add("query", $sql);
    $arr = $appSession->getTier()->getArray($msg);
    if (count($arr) > 0) {
        $selected_id = $arr[0][0];
        if ($arr[0][1] != "") {
            $page_title = META_TITLE . " - " . $arr[0][1];
        }
        if ($arr[0][2] != "") {
            $page_keywords = $arr[0][2];
        }
        if ($arr[0][3] != "") {
            $page_description = $arr[0][3];
        }
    }
}
$index = $appSession->getTool()->indexOf($uri, "/p-");
$price_id = "";
if ($index != -1) {
    $page = "product";
    $price_id = $appSession->getTool()->substring($uri, $index + 3);

    $sql = "SELECT d2.id, d2.name, d3.keywords, d3.description, d8.document_id, d9.document_id, doc1.ext, doc2.ext, d1.id AS price_id FROM product_price d1 LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id) LEFT OUTER JOIN meta d3 ON(d2.id = d3.rel_id AND d3.status=0 AND d3.publish=1)  LEFT OUTER JOIN poster d8 ON(d1.id = d8.rel_id AND d8.publish=1 AND d8.status =0) LEFT OUTER JOIN poster d9 ON(d2.id = d9.rel_id AND d9.publish=1 AND d9.status =0) LEFT OUTER JOIN document doc1 ON(d8.document_id = doc1.id) LEFT OUTER JOIN document doc2 ON(d9.document_id = doc2.id) WHERE (d1.id ='" . $price_id . "' OR d1.code='".$price_id."')";

    $msg->add("query", $sql);
    $arr = $appSession->getTier()->getArray($msg);
    if (count($arr) > 0) {
        $selected_id = $arr[0][0];
        if ($arr[0][1] != "") {
            $page_title = $arr[0][1]. " - ".META_TITLE ;
        }
        if ($arr[0][2] != "") {
            $page_keywords = $arr[0][2];
        }
        if ($arr[0][3] != "") {
            $page_description = $arr[0][3];
        }
		$document_id = $arr[0][4];
		$document_ext = $arr[0][6];
		if($document_id == "")
		{
			$document_id = $arr[0][5];
			$document_ext = $arr[0][7];
		}
    }
}

$is_catalog_url = $appSession->getTool()->indexOf($uri, "/c-") != -1 || $appSession->getTool()->indexOf($uri, "/g-") != -1 || $appSession->getTool()->indexOf($uri, "/p-") != -1;

$uri = $appSession->getTool()->replace($uri, "/", "");

if ($uri == "card") {
    $page = "card";
} else if ($uri == "category") {
    $page = "category";
} else if ($uri == "checkout") {
    $page = "checkout";
} else if ($uri == "paid") {
    $page = "paid";
} else if ($uri != "" && $is_catalog_url == false) {
    // Đường dẫn không còn tồn tại
    $page = "404";
    http_response_code(404);
}

require_once(ABSPATH . 'api/Sale.php');
$sale = new Sale($appSession);
$productList = $sale->productList();

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title; ?></title>
	<meta content="<?php echo $page_description ?>" name="description">
    <meta content="<?php echo $page_keywords ?>" name="keywords">
	<?php
	if( $document_id != "")
	{
		if($document_ext == "svg")
		{
			$document_ext  = "svg+xml";
		}else if($document_ext == "png"){
			$document_ext  = "png";
		}else if($document_ext == "avif"){
			$document_ext  = "avif";
		}else{
			$document_ext  = "jpeg";
		}
	?>
	<meta property="og:image:type" content="image/<?php echo $document_ext;?>">
	<meta property="og:image" content="<?php echo IMAGE_URL; ?>document/?id=<?php echo $document_id; ?>&h=220">

	<?php
	}
	?>
	<meta property="og:title" content="<?php echo $page_title; ?>">
	<meta property="og:description" content="<?php echo $page_title ?>">
    <link rel="shortcut icon" href="<?php echo URL; ?>assets/images/logo.png" />
    <link rel="stylesheet" href="<?php echo URL; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo URL; ?>assets/css/all.min.css">
    <link rel="stylesheet" href="<?php echo URL; ?>assets/css/animate.css">
    <link rel="stylesheet" href="<?php echo URL; ?>assets/css/swiper.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo URL; ?>assets/css/slick.css">
    <link rel="stylesheet" type="text/css" href="<?php echo URL; ?>assets/css/slick-theme.css">
    <link rel="stylesheet" href="<?php echo URL; ?>assets/css/custom-select.css">
    <link rel="stylesheet" href="<?php echo URL; ?>assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700;800&display=swap">
    <?php
    $theme_base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
    $theme_version = '25';
    ?>
    <link rel="stylesheet" href="<?php echo $theme_base; ?>assets/css/theme-tokens.css?v=<?php echo $theme_version; ?>">
    <link rel="stylesheet" href="<?php echo $theme_base; ?>assets/css/theme-legacy-colors.css?v=<?php echo $theme_version; ?>">
    <link rel="stylesheet" href="<?php echo $theme_base; ?>assets/css/theme.css?v=<?php echo $theme_version; ?>">
    <link rel="stylesheet" href="<?php echo $theme_base; ?>assets/css/ocop-layout.css?v=<?php echo $theme_version; ?>">
	<script src='<?php echo URL; ?>assets/js/jquery.min.js'></script>
    <script type="text/javascript" src="<?php echo URL; ?>assets/js/controller.js"></script>
    <script type="text/javascript" src="<?php echo URL; ?>assets/js/tool.js"></script>
	<script type="text/javascript" src="<?php echo URL; ?>assets/js/sha1.js"></script>
	<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-ZPNECBZVPW"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-ZPNECBZVPW');
</script>
</head>

<body id="top-page">
    <a class="position-absolute" href="javascript:void(0)" onClick="cartopen()">
        <div id="sitebar-drawar" class="sitebar-drawar" style="display:none">
            <div class="cart-count d-flex align-items-center">
                <i class="fas fa-shopping-basket"></i>
                <span id="itemCount">0</span>
            </div>
            <div class="total-price" id="totalPrice">0</div>
        </div>
    </a>

    <!-- sidebar-cart -->
    <div id="sitebar-cart" class="sitebar-cart">

    </div>

    <?php include(ABSPATH . 'addons/layout/header.php'); ?>

    <main class="main-content-area">
        <?php
        include(ABSPATH . "addons/" . $page . '/index.php');
        ?>
    </main>

    <?php include(ABSPATH . 'addons/layout/footer.php'); ?>

     <div id="pnFullDialog" class="modal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" id="closeFullDialog" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					 <h4 class="modal-title" id="pnFullTitle"></h4>
				</div>
				<div class="modal-body" >
					<div id="pnFullDialogContent">
					</div>
				</div>

			</div>
		</div>
	</div>


<a href="#top-page" class="to-top js-scroll-trigger"><span><i class="fas fa-arrow-up"></i></span></a>

    <script src='<?php echo URL; ?>assets/js/bootstrap.bundle.min.js'></script>
    <script src='<?php echo URL; ?>assets/js/swiper.min.js'></script>
    <script src="<?php echo URL; ?>assets/js/slick.js"></script>
    <script src='<?php echo URL; ?>assets/js/jquery-easeing.min.js'></script>
    <script src='<?php echo URL; ?>assets/js/scroll-nav.js'></script>
    <script src="<?php echo URL; ?>assets/js/jquery.elevatezoom.js"></script>
    <script src='<?php echo URL; ?>assets/js/price-range.js'></script>
    <script src='<?php echo URL; ?>assets/js/custom-select.js'></script>
    <script src='<?php echo URL; ?>assets/js/multi-countdown.js'></script>
    <script src='<?php echo URL; ?>assets/js/fly-cart.js'></script>
    <script src='<?php echo URL; ?>assets/js/theia-sticky-sidebar.js'></script>
    <script src='<?php echo URL; ?>assets/js/functions.js?v=1'></script>
    <script src="<?php echo $theme_base; ?>assets/js/theme-reveal.js?v=<?php echo $theme_version; ?>"></script>
    <script src="<?php echo $theme_base; ?>assets/js/ocop-layout.js?v=<?php echo $theme_version; ?>"></script>
	<script>
    var BASE_URL = '<?php echo URL; ?>';

	function loadCard()
	{
		var _url =BASE_URL + "api/sale_action/?ac=viewCardCount";

		loadPage('contentView', _url, function(status, message)
		{

			if(status== 0)
			{
				var index = message.indexOf(';');
				if(index != -1)
				{
					var ctr = document.getElementById('sitebar-drawar');

					var itemCount = message.substring(0, index);
					var totalPrice = message.substring(index + 1);
					if(itemCount == "0")
					{
						ctr.style.display = "none";
					}else{
						ctr.style.display = "block";
					}
					ctr = document.getElementById('itemCount');
					if(ctr != null)
					{
						ctr.innerHTML = itemCount;
					}
					ctr = document.getElementById('totalPrice');
					if(ctr != null)
					{
						ctr.innerHTML = totalPrice;
					}
				}
			}

		}, true);

	}

    function addProduct(product_id, currency_id, unit_id, attribute_id, quantity, unit_price, second_unit_id, factor, description, company_id, price_id, type_id, oncompleted) {
		if(factor == "" || factor == "0")
		{
			factor = 1;
		}
        var _url = BASE_URL + "api/sale_action/?ac=addProduct";
        _url = _url + "&product_id=" + product_id;
        _url = _url + "&currency_id=" + currency_id;
        _url = _url + "&unit_id=" + unit_id;
		_url = _url + "&attribute_id=" + attribute_id;
        _url = _url + "&quantity=" + quantity;
        _url = _url + "&unit_price=" + encodeURIComponent(unit_price);
		_url = _url + "&second_unit_id=" + second_unit_id;
		_url = _url + "&factor=" + factor;
        _url = _url + "&description=" + encodeURIComponent(description);
		_url = _url + "&company_id=" + company_id;
		_url = _url + "&rel_id=" + price_id;
		_url = _url + "&type_id=" + type_id;

        loadPage('contentView', _url, function(status, message) {
            if (status == 0) {
                if (message.length == 36) {
                    if (oncompleted != null) {
                        oncompleted(status, message);

                    }
					loadCardContent();
                } else {
                    console.log(message);
                }

            }

        }, true);
    }

    function removeCard(id, oncompleted) {
		var result = confirm('<?php echo $appSession->getLang()->find("Are you sure to remove?");?>');
		if (!result) {
			return;
		}
				var _url = BASE_URL + "api/sale_action/?ac=removeCard";
        _url = _url + "&id=" + id;

        loadPage('contentView', _url, function(status, message) {
            if (status == 0) {
                if (message.length == 36) {
                    if (oncompleted != null) {
                        oncompleted(status, message);
                    }
                } else {
                    console.log(message);
                }

            }

        }, true);
    }

    function updateCard(id, quantity, oncompleted) {
        var _url = BASE_URL + "api/product/?ac=sale_product_update_quantity";
        _url = _url + "&id=" + id + "&quantity=" + quantity;

        loadPage('contentView', _url, function(status, message) {
            if (status == 0) {
                if (message.length == 36) {
                    if (oncompleted != null) {
                        oncompleted(status, message);
                    }
                } else {
                    console.log(message);
                }

            }

        }, true);
    }

    function loadWishList() {
        var _url = BASE_URL + "api/sale_action/?ac=viewWishListCount";

        loadPage('contentView', _url, function(status, message) {

            if (status == 0) {

            }

        }, true);

    }

    function addToWishList(product_id) {
        var _url = BASE_URL + "api/sale_action/?ac=addToWishList";
        _url = _url + "&product_id=" + product_id;
        loadPage('contentView', _url, function(status, message) {
            if (status == 0) {
                if (message.length == 36) {
                    loadWishList();
                } else {
                    console.log(message);
                }

            }

        }, true);
    }

    function removeToWishList(product_id) {
        var _url = BASE_URL + "api/sale_action/?ac=removeToWishList";
        _url = _url + "&product_id=" + product_id;
        loadPage('contentView', _url, function(status, message) {
            if (status == 0) {
                if (message.length == 36) {
                    document.location.reload();
                } else {
                    console.log(message);
                }

            }

        }, true);
    }

	function loadCardContent()
	{
		var _url = BASE_URL + "addons/card/";
        loadPage('sitebar-cart', _url, function(status, message) {
            if (status == 0) {

            }
        }, false);
	}
	function cartopen() {

		var _url = BASE_URL + "addons/card/";
        loadPage('sitebar-cart', _url, function(status, message) {
            if (status == 0) {
                document.getElementById("sitebar-cart").classList.add('open-cart');
				document.getElementById("sitebar-drawar").classList.add('hide-drawer');
            }
        }, false);



	}

	function cartclose() {
		document.getElementById("sitebar-cart").classList.remove('open-cart');
		document.getElementById("sitebar-drawar").classList.remove('hide-drawer');
	}

	function openPopup(_url, oncompleted)
	{

		loadPage('pnFullDialogContent', _url, function(status, message)
		{
			if(status== 0)
			{
				$("#pnFullDialog").modal();
				 if (oncompleted != null) {
                        oncompleted(status, message);
                    }

			}

		}, false);
	}
	function closePopup()
	{
		var p = document.getElementById('closeFullDialog');
		p.click();
	}
	loadCard();
    </script>


</body>
</html>
