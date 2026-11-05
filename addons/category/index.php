<?php
require_once(ABSPATH . 'api/Product.php');

function findChild($dt, $parent_id)
{
    $ids = "";
    for ($i = 0; $i < $dt->getRowCount(); $i++) {
        if ($dt->getString($i, "parent_id") == $parent_id) {
            if ($ids != "") {
                $ids = $ids . ",";
            }
            $ids = $ids . $dt->getString($i, "id");
            $s1 = findChild($dt, $dt->getString($i, "id"));
            if ($s1 != "") {
                $ids = $ids . "," . $s1;
            }
        }
    }
    return $ids;
}
$ids = findChild($dt_category, $selected_id);
if ($ids != "") {
    $ids = $ids . "," . $selected_id;
} else {
    $ids = $selected_id;
}
$arr = $appSession->getTool()->split($ids, ',');
$ids = "";
for ($i = 0; $i < count($arr); $i++) {
    if ($ids != "") {
        $ids = $ids . " OR ";
    }
    $ids = $ids . " d1.category_id='" . $arr[$i] . "'";
}
$productList = null;
$sql = "SELECT d1.id, d5.unit_id, d5.attribute_id,	d5.type_id, d1.code, d1.name, lg.description AS name_lg, d2.document_id, d5.unit_price, d5.old_price, d6.name AS unit_name, d5.currency_id";
$sql = $sql.", d9.name AS attribute_name, d10.name attribute_category_name, d5.factor"; 
$sql = $sql.", d5.company_id, d7.commercial_name, d7.name AS company_name, d5.id AS price_id, d8.document_id AS price_document_id, d5.description, d11.name AS type_name, 0.0 AS unit_in_stock, d12.name AS sticker";
$sql = $sql." FROM product d1";
$sql = $sql." LEFT OUTER JOIN poster d2 ON(d1.id = d2.rel_id AND d2.publish=1 AND d2.status =0)";
$sql = $sql." LEFT OUTER JOIN res_lang_line lg ON(lg.lang_id='".$appSession->getConfig()->getProperty("lang_id")."' AND lg.rel_id = d1.id AND lg.name='product_name' AND lg.status =0)";
$sql = $sql." LEFT OUTER JOIN product_price d5 ON(d1.id = d5.product_id AND d5.status =0 AND d5.publish=1) LEFT OUTER JOIN product_unit d6 ON(d5.unit_id = d6.id)";
$sql = $sql." LEFT OUTER JOIN res_company d7 ON(d5.company_id = d7.id)";
$sql = $sql." LEFT OUTER JOIN poster d8 ON(d5.id = d8.rel_id AND d8.publish=1 AND d8.status =0)";
$sql = $sql." LEFT OUTER JOIN attribute d9 ON(d5.attribute_id = d9.id)";
$sql = $sql." LEFT OUTER JOIN attribute_category d10 ON(d9.category_id = d10.id)";
$sql = $sql." LEFT OUTER JOIN product_type d11 ON(d5.type_id = d11.id)";
$sql = $sql." LEFT OUTER JOIN res_meta d12 ON(d1.id = d12.rel_id AND d12.type='Sticker' AND d12.status =0)";
$sql = $sql." WHERE d1.status =0 AND d1.publish = 1";
$sql = $sql." AND (".$ids.")";
if($search != "")
{
	$sql = $sql." AND (".$appSession->getTier()->buildSearch(["d1.code", "d1.name", "lg.description"], $search).")";
}

$sql1 = $sql." AND d5.type='PRODUCT'";
$sql1 = $sql1." ORDER BY d1.name ASC, d1.sequence ASC, d5.sequence ASC";
$msg->add("query", $sql1);

$productList = $appSession->getTier()->getTable($msg);

$product = new Product($appSession);

$productList = $product->countProduct($productList);

?>

<!-- page-header-section start -->
<div class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 d-flex justify-content-between justify-content-md-start">
                <ul class="breadcrumb">
                    <li><a href="<?php echo rtrim(MAIN_SITE_URL, "/"); ?>/"><?php echo $appSession->getLang()->find("Home");?> </a></li>
                    <li><span>/</span></li>
                    <li><a href="<?php echo URL; ?>"><?php echo $appSession->getLang()->find("Category product");?></a></li>
					<?php
					if($selected_id != "")
					{
						$category_name = "";
						$category_code = "";
						
						for($i =0; $i<$dt_category->getRowCount(); $i++)
						{
						 if($dt_category->getString($i, "id") == $selected_id)
						  {
							   $category_code = $dt_category->getString($i, "code");
								$category_name = $dt_category->getString($i, "name_lg");
								if ($category_name == "") {
									$category_name = $dt_category->getString($i, "name");
								}
						  }
						}
					?>
					 <li><span>/</span></li>
                    <li><a href="<?php echo URL; ?><?php echo $appSession->getTool()->validUrl($category_name); ?>/c-<?php echo $category_code; ?>"><?php echo $category_name;?></a></li>
					<?php
					}
					?>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- page-header-section end -->

<!-- page-content -->
<?php
// Filter bar data: top-level groups, the active group and its child categories
$filter_active_parent = "";
$filter_active_name = "";
for ($i = 0; $i < $dt_category->getRowCount(); $i++) {
    if ($dt_category->getString($i, "id") == $selected_id) {
        $filter_active_parent = $dt_category->getString($i, "parent_id") == "" ? $selected_id : $dt_category->getString($i, "parent_id");
        $filter_active_name = $dt_category->getString($i, "name_lg");
        if ($filter_active_name == "") {
            $filter_active_name = $dt_category->getString($i, "name");
        }
    }
}
$filter_children = [];
if ($filter_active_parent != "") {
    for ($i = 0; $i < $dt_category->getRowCount(); $i++) {
        if ($dt_category->getString($i, "parent_id") == $filter_active_parent) {
            $child_name = $dt_category->getString($i, "name_lg");
            if ($child_name == "") {
                $child_name = $dt_category->getString($i, "name");
            }
            $filter_children[] = [$dt_category->getString($i, "id"), $dt_category->getString($i, "code"), $child_name];
        }
    }
}
$filter_action = strtok($_SERVER['REQUEST_URI'], '?');
?>
<section class="page-content listing-page section-ptb-90">
    <div class="container">
        <div class="listing-hero">
            <h1 class="listing-title"><?php echo $filter_active_name != "" ? $filter_active_name : $appSession->getLang()->find("Category product"); ?></h1>
            <p class="listing-lead">Lọc theo nhóm, tìm theo tên sản phẩm hoặc <a href="<?php echo rtrim(MAIN_SITE_URL, "/"); ?>/gioi-thieu#lien-he">liên hệ</a> để được tư vấn và báo giá.</p>
        </div>

        <div class="listing-filter">
            <p class="listing-filter-label">1. Chọn theo nhóm sản phẩm:</p>
            <ul class="filter-chips">
                <li><a class="filter-chip<?php if ($selected_id == "") { echo " is-active"; } ?>" href="<?php echo URL; ?>"<?php if ($selected_id == "") { echo ' aria-current="page"'; } ?>>Tất cả<?php if ($selected_id == "") { echo " (" . $productList->getRowCount() . ")"; } ?></a></li>
                <?php
                for ($i = 0; $i < $dt_category->getRowCount(); $i++) {
                    if ($dt_category->getString($i, "parent_id") != "") {
                        continue;
                    }
                    $chip_id = $dt_category->getString($i, "id");
                    $chip_code = $dt_category->getString($i, "code");
                    $chip_name = $dt_category->getString($i, "name_lg");
                    if ($chip_name == "") {
                        $chip_name = $dt_category->getString($i, "name");
                    }
                    $chip_active = $chip_id == $filter_active_parent;
                ?>
                <li><a class="filter-chip<?php if ($chip_active) { echo " is-active"; } ?>" href="<?php echo URL; ?><?php echo $appSession->getTool()->validUrl($chip_name); ?>/c-<?php echo $chip_code; ?>"<?php if ($chip_id == $selected_id) { echo ' aria-current="page"'; } ?>><?php echo $chip_name; ?></a></li>
                <?php
                }
                ?>
            </ul>

            <?php if (count($filter_children) > 0) { ?>
            <p class="listing-filter-label">2. Chọn loại:</p>
            <ul class="filter-chips filter-chips-sm">
                <?php for ($i = 0; $i < count($filter_children); $i++) { ?>
                <li><a class="filter-chip<?php if ($filter_children[$i][0] == $selected_id) { echo " is-active"; } ?>" href="<?php echo URL; ?><?php echo $appSession->getTool()->validUrl($filter_children[$i][2]); ?>/c-<?php echo $filter_children[$i][1]; ?>"<?php if ($filter_children[$i][0] == $selected_id) { echo ' aria-current="page"'; } ?>><?php echo $filter_children[$i][2]; ?></a></li>
                <?php } ?>
            </ul>
            <?php } ?>

            <div class="listing-tools">
                <form class="listing-search" action="<?php echo htmlspecialchars($filter_action); ?>" method="get" role="search">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <label class="sr-only" for="listing-search-input"><?php echo $appSession->getLang()->find("Search for Products");?></label>
                    <input type="search" id="listing-search-input" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Tìm theo tên hoặc mã sản phẩm...">
                </form>
                <div class="listing-sort">
                    <label class="sr-only" for="listing-sort-select">Sắp xếp</label>
                    <select id="listing-sort-select" class="listing-select" onchange="sortListing(this.value)">
                        <option value="">Sắp xếp: Mặc định</option>
                        <option value="name">Tên: A → Z</option>
                        <option value="price-asc">Giá: thấp → cao</option>
                        <option value="price-desc">Giá: cao → thấp</option>
                    </select>
                </div>
            </div>

            <p class="listing-count"><i class="fas fa-sliders-h" aria-hidden="true"></i> Hiển thị <strong><?php echo $productList->getRowCount(); ?></strong> sản phẩm<?php if ($search != "") { echo ' cho “' . htmlspecialchars($search) . '”'; } ?></p>
        </div>

                <div class="row product-list" id="listing-grid">
					<?php
					if($productList->getRowCount() == 0){
						echo "<h2>".$appSession->getLang()->find("Product is empty")."</h2>";
					}
					?>
                    <?php
                    for ($i = 0; $i < $productList->getRowCount(); $i++) {

                        $product_id = $productList->getString($i, "id");
                        $code = $productList->getString($i, "code");
                        $document_id = $productList->getString($i, "price_document_id");
						if($document_id == "")
						{
							$document_id = $productList->getString($i, "document_id");
						}
                        $name = $productList->getString($i, "name_lg");

                        if ($name == "") {
                            $name = $productList->getString($i, "name");
                        }
                        $unit_price = $productList->getFloat($i, "unit_price");
                        $old_price = $productList->getFloat($i, "old_price");
                        $unit_id = $productList->getString($i, "unit_id");
                        $currency_id = $productList->getFloat($i, "currency_id");
						$unit_name = $productList->getString($i, "unit_name");
						$price_id = $productList->getString($i, "price_id");
						
						$attribute_category_name = $productList->getString($i, "attribute_category_name");
						$attribute_id = $productList->getString($i, "attribute_id");
						$type_id = $productList->getString($i, "type_id");
						$attribute_name = $productList->getString($i, "attribute_name");
						$attribute_code = $productList->getString($i, "attribute_code");
						$unit_in_stock = $productList->getFloat($i, "unit_in_stock");
						$second_unit_id = $productList->getString($i, "second_unit_id");
						$factor = $productList->getString($i, "factor");
						$company_id = $productList->getString($i, "company_id");
						$commercial_name = $productList->getString($i, "commercial_name");		
						if($commercial_name == "")
						{
							$commercial_name = $productList->getString($i, "company_name");
						}
						if($factor == "" || $factor == "0")
						{
							$factor = "1";
						}
						$product_type_name = $productList->getString($i, "type_name");
						$sticker = $productList->getString($i, "sticker");
						

                    ?>
                    <div class="col-sm-6 col-lg-4 col-xl-3" data-name="<?php echo htmlspecialchars($name); ?>" data-price="<?php echo $unit_price; ?>">
                        <div class="product-item">
						<?php
							if($sticker != "")
							{
								?>
							<span class="product-sticker">
							<?php echo $sticker;?>
							</span> 
							<?php
							}
							?>
                            <div class="product-thumb">
							<?php if($product_type_name != ""){ ?>
							<div class="product-type-tag"><?php echo $product_type_name;?></div>
							<?php } ?>
                                <a href="<?php echo URL; ?><?php echo  $appSession->getTool()->validUrl($name); ?>/p-<?php echo $price_id;?>"><img
                                        src="<?php echo IMAGE_URL; ?>document/?id=<?php echo $document_id; ?>&h=230"
                                        alt="<?php echo $name;?>"></a>
                                <?php
                                    if ($old_price != 0) {
                                        $p = -100;
                                        if ($unit_price != 0) {
                                            $p = $old_price - $unit_price;
                                            $p = ($p / $old_price) * 100;
                                        }

                                    ?>
                                <span class="batch sale"><?php echo intval($p); ?>%</span>
                                <?php
                                    }
                                    ?>

										
				
                                </a>
                            </div>
                            <div class="product-content">
                                <h6><a href="<?php echo URL; ?><?php echo  $appSession->getTool()->validUrl($name); ?>/p-<?php echo $price_id;?>""
                                        class="product-title"><?php echo $name ?></a>
                                </h6>
								
								<?php
									 if($attribute_category_name != "")
									 {
									?>
									 <p class="quantity"><?php echo $attribute_category_name;?> : <?php echo $attribute_name;?></p>
									 <?php
									 }
									 ?>
									 
                                 <p class="quantity stock-line<?php if ($unit_in_stock == 0) { echo " is-out"; } ?>"><?php echo $appSession->getLang()->find("Stock");?> : <?php if ( $unit_in_stock == 0) {
									echo $appSession->getLang()->find("Out of stock");
								} else {
									echo $unit_in_stock;
								}  ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="price">
                                       <?php echo $appSession->getCurrency()->format($currency_id, $appSession->getTool()->toDouble($unit_price)); ?>
                                             / <?php echo $unit_name ?>
                                    </div>
                                    <a class="detail-btn" href="<?php echo URL; ?><?php echo  $appSession->getTool()->validUrl($name); ?>/p-<?php echo $price_id;?>"><?php echo $appSession->getLang()->find("More");?></a>
										<?php if($unit_in_stock>0)
										{
										?>
											<a
                                             href="javascript:addProduct('<?php echo $product_id; ?>', '<?php echo $currency_id; ?>', '<?php echo $unit_id; ?>', '<?php echo $attribute_id; ?>', 1, <?php echo $unit_price; ?>, '<?php echo $second_unit_id; ?>', <?php echo $factor; ?>, '', '<?php $company_id;?>', '<?php echo $price_id;?>','<?php echo $type_id;?>', function(status, message){ loadCard(); })">
                                             <span class="cart-btn"><i class="fas fa-shopping-cart"></i><?php echo $appSession->getLang()->find("Add");?></span>

                                         </a>
										 <?php
										}
										?>
										 
                                    </a>
                                </div>
								 
                            </div>
                        </div>
                    </div>
                    <?php
                    }
                    ?>

                </div>
    </div>
</section>
<script>
function sortListing(mode) {
    var grid = document.getElementById('listing-grid');
    var items = Array.prototype.slice.call(grid.querySelectorAll('[data-price]'));
    items.forEach(function (el, i) { if (el.dataset.order === undefined) { el.dataset.order = i; } });
    items.sort(function (a, b) {
        if (mode === 'name') { return a.dataset.name.localeCompare(b.dataset.name, 'vi'); }
        if (mode === 'price-asc') { return parseFloat(a.dataset.price) - parseFloat(b.dataset.price); }
        if (mode === 'price-desc') { return parseFloat(b.dataset.price) - parseFloat(a.dataset.price); }
        return a.dataset.order - b.dataset.order;
    });
    items.forEach(function (el) { grid.appendChild(el); });
}
</script>
<!-- page-content -->