<?php
// Footer dùng chung, được gọi từ addons/index.php sau addons/layout/header.php
if (!isset($ocop_categories)) {
	return;
}
$ocop_footer_logo = file_exists(ABSPATH . "assets/images/logo-ocop-blue.png") ? URL . "assets/images/logo-ocop-blue.png" : $ocop_logo;
$ocop_footer_bg = file_exists(ABSPATH . "assets/images/footer-ocean-bg.png") ? URL . "assets/images/footer-ocean-bg.png" : "";
?>
<footer class="ocop-footer">
    <?php if ($ocop_footer_bg != "") { ?>
    <div class="ocop-footer-bg" style="background-image: url('<?php echo $ocop_footer_bg; ?>')" aria-hidden="true"></div>
    <?php } ?>
    <div class="ocop-footer-glow" aria-hidden="true"></div>

    <div class="ocop-footer-inner">
        <div class="ocop-footer-main">
            <div class="ocop-footer-brand">
                <a href="<?php echo $ocop_main; ?>/" class="ocop-footer-logo">
                    <img src="<?php echo $ocop_footer_logo; ?>" alt="<?php echo $ocop_brand; ?>">
                </a>

                <div class="ocop-footer-channels">
                    <?php if ($ocop_has_chat) { ?>
                    <button type="button" class="ocop-channel" data-ocop-chat="footer_channel_icon" data-ocop-track="click_live_chat" data-ocop-source="footer_channel_icon" aria-label="Chat trực tiếp trên website">
                        <?php echo ocop_icon("message-square", 16); ?>
                    </button>
                    <?php } ?>
                    <a href="tel:<?php echo SITE_HOTLINE_TEL; ?>" class="ocop-channel" aria-label="Gọi Hotline <?php echo SITE_HOTLINE_DISPLAY; ?>">
                        <?php echo ocop_icon("phone-call", 16); ?>
                    </a>
                    <?php if (SITE_EMAIL != "") { ?>
                    <a href="mailto:<?php echo SITE_EMAIL; ?>" class="ocop-channel" aria-label="Gửi Email liên hệ">
                        <?php echo ocop_icon("mail", 16); ?>
                    </a>
                    <?php } ?>
                </div>
            </div>

            <div class="ocop-footer-cols">
                <div class="ocop-footer-col">
                    <h3 class="ocop-footer-title">Sản phẩm</h3>
                    <ul class="ocop-footer-list">
                        <?php foreach ($ocop_categories as $category) { ?>
                        <li><a href="<?php echo htmlspecialchars($category["url"]); ?>"><?php echo htmlspecialchars($category["name"]); ?></a></li>
                        <?php } ?>
                    </ul>
                </div>

                <div class="ocop-footer-col">
                    <h3 class="ocop-footer-title">Về chúng tôi</h3>
                    <ul class="ocop-footer-list">
                        <li><a href="<?php echo $ocop_main; ?>/gioi-thieu">Giới thiệu <?php echo $ocop_brand; ?></a></li>
                        <li><a href="<?php echo $ocop_main; ?>/kien-thuc">Tin tức &amp; Kiến thức</a></li>
                        <li><a href="<?php echo $ocop_main; ?>/gioi-thieu#lien-he">Hợp Tác B2B &amp; Nhà Hàng</a></li>
                        <li><a href="<?php echo $ocop_main; ?>/gioi-thieu#lien-he">Liên hệ &amp; Báo giá</a></li>
                    </ul>
                </div>

                <div class="ocop-footer-col ocop-footer-col--wide">
                    <h3 class="ocop-footer-title">Hỗ trợ</h3>
                    <ul class="ocop-footer-list">
                        <li><a href="<?php echo $ocop_main; ?>/cach-dat-hang">Hướng dẫn Đặt hàng</a></li>
                        <li><a href="<?php echo $ocop_main; ?>/chinh-sach/van-chuyen">Chính sách Vận chuyển lạnh</a></li>
                        <li><a href="<?php echo $ocop_main; ?>/chinh-sach/doi-tra">Chính sách Đổi trả &amp; Bù hàng</a></li>
                        <li><a href="<?php echo $ocop_main; ?>/hoi-dap">Hỏi đáp thường gặp (FAQ)</a></li>
                        <li class="ocop-footer-hotline">
                            Hotline: <a href="tel:<?php echo SITE_HOTLINE_TEL; ?>"><?php echo SITE_HOTLINE_DISPLAY; ?></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="ocop-footer-bottom">
            <p class="ocop-footer-copy">
                © <?php echo date("Y"); ?> <?php echo $ocop_brand; ?> – Thương mại Thủy sản Tuyển chọn. Bảo lưu mọi quyền.
            </p>
            <div class="ocop-footer-policies">
                <a href="<?php echo $ocop_main; ?>/chinh-sach/dieu-khoan">Điều khoản sử dụng</a>
                <span class="ocop-footer-sep">•</span>
                <a href="<?php echo $ocop_main; ?>/chinh-sach/bao-mat">Chính sách bảo mật</a>
                <span class="ocop-footer-sep">•</span>
                <a href="<?php echo $ocop_main; ?>/chinh-sach/van-chuyen">Chính sách vận chuyển lạnh</a>
            </div>
        </div>
    </div>
</footer>
