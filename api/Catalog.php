<?php
require_once(ABSPATH . 'api/Product.php');

// Dữ liệu danh mục / sản phẩm dùng cho header (mega menu, tìm kiếm nhanh) và footer
class Catalog
{
	public $appSession;
	public $msg;
	public function __construct($appSession) {
		$this->appSession = $appSession;
		$this->msg = $appSession->getTier()->createMessage();
	}

	// id danh mục => id danh mục cấp cao nhất
	function topParentMap($dt_category)
	{
		$parents = [];
		for ($i = 0; $i < $dt_category->getRowCount(); $i++) {
			$parents[$dt_category->getString($i, "id")] = $dt_category->getString($i, "parent_id");
		}
		$map = [];
		foreach ($parents as $id => $parent_id) {
			$top = $id;
			$guard = 0;
			while (isset($parents[$top]) && $parents[$top] != "" && $guard < 20) {
				$top = $parents[$top];
				$guard++;
			}
			$map[$id] = $top;
		}
		return $map;
	}

	function categoryName($dt_category, $i)
	{
		$name = $dt_category->getString($i, "name_lg");
		if ($name == "") {
			$name = $dt_category->getString($i, "name");
		}
		return $name;
	}

	// Danh mục cấp cao nhất kèm số sản phẩm
	function topCategories($dt_category)
	{
		$map = $this->topParentMap($dt_category);

		$sql = "SELECT d1.category_id, COUNT(d5.id) FROM product d1";
		$sql = $sql." INNER JOIN product_price d5 ON(d1.id = d5.product_id AND d5.status =0 AND d5.publish=1 AND d5.type='PRODUCT')";
		$sql = $sql." WHERE d1.status =0 AND d1.publish = 1 GROUP BY d1.category_id";
		$this->msg->add("query", $sql);
		$arr = $this->appSession->getTier()->getArray($this->msg);
		$counts = [];
		for ($i = 0; $i < count($arr); $i++) {
			if (!isset($map[$arr[$i][0]])) {
				continue;
			}
			$top = $map[$arr[$i][0]];
			$counts[$top] = (isset($counts[$top]) ? $counts[$top] : 0) + intval($arr[$i][1]);
		}

		$categories = [];
		for ($i = 0; $i < $dt_category->getRowCount(); $i++) {
			if ($dt_category->getString($i, "parent_id") != "") {
				continue;
			}
			$id = $dt_category->getString($i, "id");
			$code = $dt_category->getString($i, "code");
			$name = $this->categoryName($dt_category, $i);
			$categories[] = [
				"id" => $id,
				"code" => $code,
				"name" => $name,
				"url" => URL . $this->appSession->getTool()->validUrl($name) . "/c-" . $code,
				"count" => isset($counts[$id]) ? $counts[$id] : 0,
			];
		}
		return $categories;
	}

	// Danh sách sản phẩm gọn cho tìm kiếm nhanh và mega menu
	function headerProducts($dt_category)
	{
		$map = $this->topParentMap($dt_category);
		$tops = [];
		foreach ($this->topCategories($dt_category) as $category) {
			$tops[$category["id"]] = $category;
		}

		$sql = "SELECT d1.id, d1.category_id, d1.code, d1.name, lg.description AS name_lg, d2.document_id, d5.id AS price_id, d5.unit_id, d5.attribute_id, d5.type_id";
		$sql = $sql.", d6.name AS unit_name, d8.document_id AS price_document_id, d9.name AS attribute_name, d12.name AS sticker, 0.0 AS unit_in_stock";
		$sql = $sql." FROM product d1";
		$sql = $sql." LEFT OUTER JOIN poster d2 ON(d1.id = d2.rel_id AND d2.publish=1 AND d2.status =0)";
		$sql = $sql." LEFT OUTER JOIN res_lang_line lg ON(lg.lang_id='".$this->appSession->getConfig()->getProperty("lang_id")."' AND lg.rel_id = d1.id AND lg.name='product_name' AND lg.status =0)";
		$sql = $sql." LEFT OUTER JOIN product_price d5 ON(d1.id = d5.product_id AND d5.status =0 AND d5.publish=1) LEFT OUTER JOIN product_unit d6 ON(d5.unit_id = d6.id)";
		$sql = $sql." LEFT OUTER JOIN poster d8 ON(d5.id = d8.rel_id AND d8.publish=1 AND d8.status =0)";
		$sql = $sql." LEFT OUTER JOIN attribute d9 ON(d5.attribute_id = d9.id)";
		$sql = $sql." LEFT OUTER JOIN res_meta d12 ON(d1.id = d12.rel_id AND d12.type='Sticker' AND d12.status =0)";
		$sql = $sql." WHERE d1.status =0 AND d1.publish = 1 AND d5.type='PRODUCT'";
		$sql = $sql." ORDER BY d1.name ASC, d1.sequence ASC, d5.sequence ASC";
		$this->msg->add("query", $sql);
		$productList = $this->appSession->getTier()->getTable($this->msg);

		$product = new Product($this->appSession);
		$productList = $product->countProduct($productList);

		// Nhãn sticker trong DB => nhãn hiển thị của header
		$badges = ["sale" => "Bán chạy", "new" => "Mới về"];

		$items = [];
		$seen = [];
		for ($i = 0; $i < $productList->getRowCount(); $i++) {
			// Mỗi sản phẩm một dòng (sản phẩm có nhiều đơn vị giá chỉ lấy giá đầu tiên)
			$price_id = $productList->getString($i, "price_id");
			$product_id = $productList->getString($i, "id");
			if ($price_id == "" || isset($seen[$product_id])) {
				continue;
			}
			$seen[$product_id] = true;

			$name = $productList->getString($i, "name_lg");
			if ($name == "") {
				$name = $productList->getString($i, "name");
			}
			$document_id = $productList->getString($i, "price_document_id");
			if ($document_id == "") {
				$document_id = $productList->getString($i, "document_id");
			}
			$category_id = $productList->getString($i, "category_id");
			$top = isset($map[$category_id]) && isset($tops[$map[$category_id]]) ? $tops[$map[$category_id]] : null;
			$sticker = strtolower(trim($productList->getString($i, "sticker")));
			$attribute_name = trim($productList->getString($i, "attribute_name"));
			$unit_name = trim($productList->getString($i, "unit_name"));
			$spec = $attribute_name == $unit_name ? $unit_name : trim($attribute_name . " " . $unit_name);

			$items[] = [
				"id" => $productList->getString($i, "code"),
				"name" => trim($name),
				"url" => URL . $this->appSession->getTool()->validUrl($name) . "/p-" . $price_id,
				"image" => $document_id != "" ? IMAGE_URL . "document/?id=" . $document_id . "&h=120" : "",
				"categoryCode" => $top != null ? $top["code"] : "",
				"categoryName" => $top != null ? $top["name"] : "",
				"specSummary" => $spec,
				"badge" => isset($badges[$sticker]) ? $badges[$sticker] : "",
				"inStock" => $productList->getFloat($i, "unit_in_stock") > 0,
			];
		}
		return $items;
	}
}
?>
