<?php
// Header dùng chung, được gọi từ addons/index.php
if (!isset($dt_category)) {
	return;
}
require_once(ABSPATH . 'addons/layout/icons.php');
require_once(ABSPATH . 'api/Catalog.php');

$catalog = new Catalog($appSession);
$ocop_categories = $catalog->topCategories($dt_category);
$ocop_main = rtrim(MAIN_SITE_URL, '/');
$ocop_nav = [
	["href" => $ocop_main . "sanpham.chohaosang.vn", "label" => "Trang chủ"],
	["href" => URL, "label" => "Sản phẩm", "products" => true],
	["href" => $ocop_main . "/3d-virtual", "label" => "Tham quan 3D", "soon" => true],
	["href" => $ocop_main . "/kien-thuc", "label" => "Kiến thức"],
	["href" => $ocop_main . "/gioi-thieu", "label" => "Giới thiệu"],
];
$ocop_products_active = in_array($page, ["category", "group", "product"]);
$ocop_logo = URL . "assets/images/logo.png";
$ocop_brand = htmlspecialchars(SITE_BRAND_NAME);
$ocop_has_chat = TAWK_PROPERTY_ID != "";
$ocop_placeholder = URL . "assets/images/placeholder-product.svg";
?>
<header class="ocop-header" id="ocop-header">
    <div class="ocop-header-inner">
        <a href="<?php echo $ocop_main; ?>/" class="ocop-logo" aria-label="Trang chủ <?php echo $ocop_brand; ?>">
            <img src="<?php echo $ocop_logo; ?>" alt="<?php echo $ocop_brand; ?>">
        </a>

        <nav class="ocop-nav" aria-label="Menu chính">
            <?php foreach ($ocop_nav as $item) { ?>
            <?php if (!empty($item["products"])) { ?>
            <div class="ocop-nav-products" id="ocop-nav-products">
                <a href="<?php echo $item["href"]; ?>" class="ocop-nav-link<?php if ($ocop_products_active) { echo " is-active"; } ?>" id="ocop-nav-products-link">
                    <span><?php echo $item["label"]; ?></span>
                    <?php echo ocop_icon("chevron-down", 14, "ocop-nav-chevron"); ?>
                </a>
            </div>
            <?php } else { ?>
            <a href="<?php echo $item["href"]; ?>" class="ocop-nav-link ocop-nav-link--gap">
                <span><?php echo $item["label"]; ?></span>
                <?php if (!empty($item["soon"])) { ?><span class="ocop-soon">Sắp có</span><?php } ?>
            </a>
            <?php } ?>
            <?php } ?>
        </nav>

        <div class="ocop-actions">
            <button type="button" class="ocop-icon-btn ocop-icon-btn--lg" data-ocop-search-open aria-label="Tìm kiếm sản phẩm">
                <?php echo ocop_icon("search", 18); ?>
            </button>
        </div>

        <div class="ocop-mobile-actions">
            <button type="button" class="ocop-icon-btn ocop-icon-btn--sm" data-ocop-search-open aria-label="Tìm kiếm sản phẩm">
                <?php echo ocop_icon("search", 20); ?>
            </button>
            <button type="button" class="ocop-icon-btn ocop-icon-btn--sm" id="ocop-menu-toggle" aria-expanded="false" aria-controls="ocop-drawer" aria-label="Mở menu điều hướng">
                <span class="ocop-menu-icon-open"><?php echo ocop_icon("menu", 20); ?></span>
                <span class="ocop-menu-icon-close"><?php echo ocop_icon("x", 20); ?></span>
            </button>
        </div>
    </div>

    <!-- Mega menu sản phẩm (desktop) -->
    <div class="ocop-mega" id="ocop-mega" hidden>
        <div class="ocop-mega-wrap">
            <div class="ocop-mega-box">
                <div class="ocop-mega-grid">
                    <div class="ocop-mega-left">
                        <div>
                            <div class="ocop-mega-head">
                                <span class="ocop-mega-title"><?php echo ocop_icon("layers", 14); ?><span>Danh mục loại</span></span>
                                <span class="ocop-mega-count"><?php echo count($ocop_categories); ?> nhóm</span>
                            </div>
                            <div class="ocop-mega-cats">
                                <?php foreach ($ocop_categories as $index => $category) { ?>
                                <a href="<?php echo htmlspecialchars($category["url"]); ?>" class="ocop-mega-cat<?php if ($index == 0) { echo " is-selected"; } ?>" data-code="<?php echo htmlspecialchars($category["code"]); ?>" data-name="<?php echo htmlspecialchars($category["name"]); ?>">
                                    <span class="ocop-truncate"><?php echo htmlspecialchars($category["name"]); ?></span>
                                    <?php echo ocop_icon("chevron-right", 14); ?>
                                </a>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="ocop-mega-all">
                            <a href="<?php echo URL; ?>" class="ocop-mega-all-link">
                                <span>Xem Tất Cả Danh Mục</span>
                                <?php echo ocop_icon("arrow-right", 14); ?>
                            </a>
                        </div>
                    </div>

                    <div class="ocop-mega-right">
                        <div>
                            <div class="ocop-mega-head ocop-mega-head--right">
                                <div class="ocop-mega-heading">
                                    <span class="ocop-dot-pulse"></span>
                                    <span class="ocop-mega-heading-text">Sản phẩm tiêu biểu: <span class="ocop-text-primary" id="ocop-mega-active-name"><?php echo count($ocop_categories) > 0 ? htmlspecialchars($ocop_categories[0]["name"]) : ""; ?></span></span>
                                </div>
                                <a href="<?php echo count($ocop_categories) > 0 ? htmlspecialchars($ocop_categories[0]["url"]) : URL; ?>" class="ocop-mega-see-all" id="ocop-mega-active-link">
                                    <span>Xem tất cả</span>
                                    <?php echo ocop_icon("arrow-right", 12); ?>
                                </a>
                            </div>
                            <div id="ocop-mega-products"></div>
                        </div>

                        <div class="ocop-mega-3d">
                            <div class="ocop-mega-3d-text">
                                <div class="ocop-mega-3d-icon"><?php echo ocop_icon("compass", 14); ?></div>
                                <span class="ocop-truncate">
                                    Khám phá trực quan qua <strong>Chợ 3D Virtual 360°</strong>
                                    <span class="ocop-mega-3d-badge">Sắp ra mắt</span>
                                </span>
                            </div>
                            <a href="<?php echo $ocop_main; ?>/3d-virtual" class="ocop-mega-3d-btn">
                                <span>Xem trước Chợ 3D</span>
                                <?php echo ocop_icon("arrow-right", 12); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Popup tìm kiếm sản phẩm -->
<div class="ocop-search" id="ocop-search" role="dialog" aria-modal="true" aria-label="Tìm kiếm sản phẩm thủy sản" hidden>
    <div class="ocop-search-backdrop" data-ocop-search-close></div>
    <div class="ocop-search-panel">
        <form class="ocop-search-form" id="ocop-search-form" action="<?php echo URL; ?>" method="get" role="search">
            <?php echo ocop_icon("search", 20, "ocop-search-icon"); ?>
            <input type="search" name="search" id="ocop-search-input" autocomplete="off" value="<?php echo htmlspecialchars($search); ?>" placeholder="Tìm nhanh: Tôm sú, Cua Cà Mau, Mực lá, Cá mú...">
            <button type="submit" class="ocop-search-submit">Tìm ngay</button>
            <button type="button" class="ocop-search-close" data-ocop-search-close aria-label="Đóng cửa sổ tìm kiếm">
                <?php echo ocop_icon("x", 16); ?>
            </button>
        </form>

        <div class="ocop-search-body">
            <div class="ocop-search-section">
                <span class="ocop-search-label">Tìm nhiều hôm nay:</span>
                <div class="ocop-search-tags" id="ocop-search-tags"></div>
            </div>

            <div class="ocop-search-section ocop-search-results">
                <div class="ocop-search-results-head">
                    <span class="ocop-search-label" id="ocop-search-results-label">Sản phẩm nổi bật:</span>
                    <a href="<?php echo URL; ?>" class="ocop-search-all">
                        <span>Xem tất cả sản phẩm</span>
                        <?php echo ocop_icon("arrow-right", 14); ?>
                    </a>
                </div>
                <div id="ocop-search-list"></div>
            </div>
        </div>
    </div>
</div>

<!-- Menu mobile -->
<div class="ocop-drawer" id="ocop-drawer" hidden>
    <div class="ocop-drawer-backdrop" data-ocop-drawer-close></div>
    <aside class="ocop-drawer-panel">
        <div class="ocop-drawer-top">
            <div class="ocop-drawer-head">
                <a href="<?php echo $ocop_main; ?>/" class="ocop-drawer-logo" aria-label="Trang chủ <?php echo $ocop_brand; ?>">
                    <img src="<?php echo $ocop_logo; ?>" alt="<?php echo $ocop_brand; ?>">
                </a>
                <button type="button" class="ocop-drawer-close" data-ocop-drawer-close aria-label="Đóng menu">
                    <?php echo ocop_icon("x", 20); ?>
                </button>
            </div>

            <nav class="ocop-drawer-nav" aria-label="Menu di động">
                <?php foreach ($ocop_nav as $index => $item) { ?>
                <?php if (!empty($item["products"])) { ?>
                <div class="ocop-drawer-group">
                    <div class="ocop-drawer-item ocop-fade-up<?php if ($ocop_products_active) { echo " is-active"; } ?>" style="animation-delay: <?php echo $index * 60; ?>ms">
                        <a href="<?php echo $item["href"]; ?>" class="ocop-drawer-item-link"><span><?php echo $item["label"]; ?></span></a>
                        <button type="button" class="ocop-drawer-sub-toggle" id="ocop-drawer-sub-toggle" aria-expanded="false" aria-controls="ocop-drawer-sub" aria-label="Mở danh mục sản phẩm">
                            <?php echo ocop_icon("chevron-down", 16); ?>
                        </button>
                    </div>
                    <div class="ocop-drawer-sub ocop-fade-up" id="ocop-drawer-sub" hidden>
                        <?php foreach ($ocop_categories as $category) { ?>
                        <a href="<?php echo htmlspecialchars($category["url"]); ?>" class="ocop-drawer-sub-link">
                            <span><?php echo htmlspecialchars($category["name"]); ?></span>
                            <span class="ocop-drawer-sub-count"><?php echo $category["count"]; ?> sản phẩm</span>
                        </a>
                        <?php } ?>
                    </div>
                </div>
                <?php } else { ?>
                <a href="<?php echo $item["href"]; ?>" class="ocop-drawer-item ocop-drawer-item--link ocop-fade-up" style="animation-delay: <?php echo $index * 60; ?>ms">
                    <span class="ocop-drawer-item-label">
                        <span><?php echo $item["label"]; ?></span>
                        <?php if (!empty($item["soon"])) { ?><span class="ocop-soon ocop-soon--lg">Sắp có</span><?php } ?>
                    </span>
                    <span class="ocop-drawer-arrow">→</span>
                </a>
                <?php } ?>
                <?php } ?>
            </nav>
        </div>

        <div class="ocop-drawer-bottom">
            <a href="tel:<?php echo SITE_HOTLINE_TEL; ?>" class="ocop-hotline" data-ocop-track="click_call_hotline" data-ocop-source="mobile_menu_popup" aria-label="Gọi Hotline <?php echo SITE_HOTLINE_DISPLAY; ?>">
                <div class="ocop-hotline-left">
                    <span class="ocop-hotline-icon">
                        <span class="ocop-ping"></span>
                        <?php echo ocop_icon("phone-call", 18); ?>
                    </span>
                    <div class="ocop-hotline-text">
                        <span class="ocop-hotline-kicker">Báo Giá 30s</span>
                        <span class="ocop-hotline-number"><?php echo SITE_HOTLINE_DISPLAY; ?></span>
                    </div>
                </div>
                <span class="ocop-hotline-cta">Gọi ngay</span>
            </a>

            <?php if ($ocop_has_chat) { ?>
            <div class="ocop-drawer-tools">
                <button type="button" class="ocop-drawer-tool" data-ocop-chat="header_mobile_menu">
                    <?php echo ocop_icon("message-square", 14); ?>
                    <span>Chat Trực Tiếp</span>
                </button>
            </div>
            <?php } ?>

            <?php if (SITE_WORKING_HOURS != "") { ?>
            <p class="ocop-drawer-hours">Giờ làm việc: <?php echo SITE_WORKING_HOURS; ?></p>
            <?php } ?>
        </div>
    </aside>
</div>
<script>
window.OCOP_HEADER = <?php echo json_encode([
	"baseUrl" => URL,
	"productsApi" => URL . "api/catalog/?ac=header_products",
	"placeholder" => $ocop_placeholder,
	"tawkPropertyId" => TAWK_PROPERTY_ID,
	"tawkWidgetId" => TAWK_WIDGET_ID,
	"icons" => [
		"chevronRight" => ocop_icon("chevron-right", 14),
		"arrowRight" => ocop_icon("arrow-right", 12),
	],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>
