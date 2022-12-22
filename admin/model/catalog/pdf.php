<?php

/**
 * https://eclecticgeek.com/dompdf/debug.php
 */

use Dompdf\Dompdf;
use Dompdf\Options;

class ModelCatalogPdf extends Model {
    private $total = 0;
    private $max = 0;
    private $parents = [];

    public function generatePdf($max, $category_ids)
    {
        $this->max = $max;
        ini_set('max_execution_time', 900);
        $root_path = dirname(DIR_APPLICATION);

        require $root_path . '/vendor/autoload.php';

        $this->load->model('catalog/category');
        $this->load->model('catalog/product');
        $this->load->model('tool/image');

        $data_html_category = '';
        foreach ($category_ids as $category_id) {
            $data_html_category .= $this->foreachCategory($category_id);
        }

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        //$options->set('debugCss', true);


        $data = file_get_contents(DIR_IMAGE . 'catalog/POSUDA22.png');
        $data2 = file_get_contents(DIR_IMAGE . 'catalog/logo2.jpg');
        $type = pathinfo(DIR_IMAGE . 'catalog/POSUDA22.png', PATHINFO_EXTENSION);
        $type2 = pathinfo(DIR_IMAGE . 'catalog/logo2.jpg', PATHINFO_EXTENSION);
        $logo = 'data:image/' . $type . ';base64,' . base64_encode($data);
        $logo2 = 'data:image/' . $type2 . ';base64,' . base64_encode($data2);

        $mpdf = new Dompdf($options);
        $html = <<<EOF
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        <meta http-equiv="X-UA-Compatible" content="IE=edge">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <meta content="text/html; charset=UTF-8" http-equiv="Content-Type"/>
                        <style>
                            body{
                                margin-top: 0;
                                font-size: 6pt;
                            }
                            .head_text{
                                font-size: 8pt;
                            }
                            .fs_head{
                                font-size: 14pt;
                                color: #fff;
                            }
                            .fs_head-cat{
                                font-size: 16pt;
                                color: #9e7d67;
                            }
                            .bg_color{
                                background-color: #9e7d67;
                            }
                            .container{
                                width: 190mm;
                                margin: auto;
                            }
                            .logo{
                                width: 100mm;
                            }
                            .col {
                                width: 25mm;
                            }
                            .fw-bold {
                                font-weight: 700!important;
                            }
                            .header_tel{
                                text-align: center;
                            }
                            .block_tel{
                                text-align: center;
                                margin: auto;
                            }
                            .m_0{
                                margin:0;
                            }
                        </style>
                    </head>
                    <body>
                        <table class="container">
                            <tr>
                                <td>
                                    <a href="https://www.odnorazovayaposudaykt.ru/index.php?route=common/home"><img class="logo img-responsive" src="$logo" title="Интернет магазин Одноразовой посуды" alt="Интернет магазин Одноразовой посуды"></a>
                                </td>
                                <td>
                                    <table class="head_text">
                                        <tr>
                                            <td>ИП АСЕЕВА О.Ю.ИНН 143520612490</td>
                                        </tr>
                                        <tr>
                                            <td>Телефон Оптового отдела: <a href="tel:+79644217505">+7964-421-75-05</a></td>
                                        </tr>
                                        <tr>
                                            <td>Телефон магазина: <a href="tel:+79644217505">+7964-421-75-05</a></td>
                                        </tr>
                                        <tr>
                                            <td>Адрес: ул. Красильникова, 3в, Якутск, Респ. Саха (Якутия), Россия, 677007</td>
                                        </tr>
                                        <tr>
                                            <td>Сайт доставки: <a href="http://www.odnorazovayaposudaykt.ru/">http://www.odnorazovayaposudaykt.ru/</a></td>
                                        </tr>
                                        <tr>
                                            <td>Email: <a href="mail: odnorazovaya_posuda_ykt@mail.ru"> odnorazovaya_posuda_ykt@mail.ru</a></td>
                                        </tr>
                                        <tr>
                                            <td><a href="Www.instagram.com/odnorazovaya_posuda_ykt">Www.instagram.com/odnorazovaya_posuda_ykt</a></td>
                                        </tr>
                                    </table>
                                        
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" align="center" style="text-align:center;">
                                    <img class="logo img-responsive" src="$logo2" title="Интернет магазин Одноразовой посуды" alt="Интернет магазин Одноразовой посуды">
                                </td>
                            </tr>
                        </table>
                        $data_html_category
                    </body>
                    </html>
        EOF;

        /**
         * Расскомментируй для проверки в браузере
         */
        //print $html; die();

        // (D) WRITE HTML TO PDF
        $mpdf->loadHtml($html);

        // Render the HTML as PDF
        $mpdf->render();

        // Output the generated PDF to Browser
        //$mpdf->stream();
        $mpdf->stream('price.pdf',array('Attachment'=>0));
    }

    /**
     * @param $category_id
     * @return string
     */
    private function generateCategoryHtml($category_id, $show_parent_title = true): string
    {
            $category = $this->model_catalog_category->getCategory($category_id);
            $parent = $this->model_catalog_category->getCategory($category['parent_id']);
            
            $category_name = $category['name'];
            $parent_name = $parent['name'];

            $filter_products = [
                'filter_category_id' => $category['category_id'],
                //'filter_sub_category' => true,
            ];
            $products = $this->getProducts($filter_products);

            $data_html_product = $this->generateProductHtml($products);

            if ($show_parent_title) {
                $html_parent_name = "<p class='m_0 fs_head-cat fw-bold'>$parent_name</p>";
            } else {
                $html_parent_name = '';
            }

        return <<<EOF
                        <table class="container">
                            <tbody>
                                <tr>
                                        <th colspan="7">
                                            $html_parent_name
                                        </th>
                                </tr>
                                <tr>
                                        <th colspan="7">
                                            <h2 class="m_0 fs_head bg_color fw-bold">$category_name</h2>
                                        </th>
                                </tr>
                            </tbody>
                        </table>
                        $data_html_product
                            
            EOF;
    }

    /**
     * @param $category_id
     * @return string
     */
    private function foreachCategory($category_id): string
    {
        $data_html_category = '';
        $childs_categories = $this->getCategories($category_id);

        $category = $this->model_catalog_category->getCategory($category_id);
        $parent = $this->model_catalog_category->getCategory($category['parent_id']);

        if (!in_array($parent['category_id'], $this->parents)) {
            $this->parents[] = $parent['category_id'];
            $show_parent_title = true;
        } else {
            $show_parent_title = false;
        }

        $data_html_category .= $this->generateCategoryHtml($category_id, $show_parent_title);

        foreach ($childs_categories as $childs_category) {
            $data_html_category .= $this->foreachCategory($childs_category['category_id']);
        }

        return $data_html_category;
    }

    /**
     * @param $products
     * @return string
     */
    private function generateProductHtml($products): string
    {
        $key_product = 0;
        $data_html_product = '';

        foreach ($products as $product)
        {
            
            if ($key_product === 0 || $key_product % 7 === 0) {
                if($key_product !== 0 ){
                    $data_html_product .= "</tr>";
                }
                $data_html_product .= "<tr>";
            }

            if (!empty($this->max) && $this->total > $this->max) {
                continue;
            }

            $image_path = DIR_IMAGE . $product['image'];

            // сделаем резак фото
            if (is_file($image_path) && !empty($product['image'])) {
                $image = $this->model_tool_image->resize($product['image'], 150, 150);
            } else {
                $image = $this->model_tool_image->resize('no_image.png', 150, 150);
            }

            $data = file_get_contents($image);
            $type = pathinfo($image, PATHINFO_EXTENSION);
            $image_64_decode = 'data:image/' . $type . ';base64,' . base64_encode($data);

            $price = (int)$product['price'];
            $name = $product['name'];

            $data_product = <<<EOF
                                <td>
                                    <div class="block_tel">
                                        <img src="$image_64_decode" class="col" alt="$name">
                                        <p>$price ₽</p>
                                        <p>$name</p>
                                    </div>
                                </td>
                            EOF;

            if (count($products) === ($key_product + 1) && count($products) < 7) {
                for ($i = 1; $i <= (7 - count($products)); $i++) {
                    $data_product .= "<td width='25mm'></td>";
                }
            }

            $data_html_product .= $data_product;

            if ( count($products) === ($key_product + 1)) {
                $data_html_product .= "</tr>";
            }

            $this->total++;
            $key_product++;
        }

        return <<<EOF
                       <table class="container">
                            <tbody>
                                $data_html_product
                            </tbody>
                        </table>
            EOF;
    }
    public function getProducts($data = array()) {
        $sql = "SELECT p.product_id, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special";

        if (!empty($data['filter_category_id'])) {
            if (!empty($data['filter_sub_category'])) {
                $sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";
            } else {
                $sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
            }

            if (!empty($data['filter_filter'])) {
                $sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
            } else {
                $sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
            }
        } else {
            $sql .= " FROM " . DB_PREFIX . "product p";
        }

        $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

        if (!empty($data['filter_category_id'])) {
            if (!empty($data['filter_sub_category'])) {
                $sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
            } else {
                $sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
            }

            if (!empty($data['filter_filter'])) {
                $implode = array();

                $filters = explode(',', $data['filter_filter']);

                foreach ($filters as $filter_id) {
                    $implode[] = (int)$filter_id;
                }

                $sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
            }
        }

        if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
            $sql .= " AND (";

            if (!empty($data['filter_name'])) {
                $implode = array();

                $words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

                foreach ($words as $word) {
                    $implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
                }

                if ($implode) {
                    $sql .= " " . implode(" AND ", $implode) . "";
                }

                if (!empty($data['filter_description'])) {
                    $sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
                }
            }

            if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
                $sql .= " OR ";
            }

            if (!empty($data['filter_tag'])) {
                $implode = array();

                $words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_tag'])));

                foreach ($words as $word) {
                    $implode[] = "pd.tag LIKE '%" . $this->db->escape($word) . "%'";
                }

                if ($implode) {
                    $sql .= " " . implode(" AND ", $implode) . "";
                }
            }

            if (!empty($data['filter_name'])) {
                $sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
            }

            $sql .= ")";
        }

        if (!empty($data['filter_manufacturer_id'])) {
            $sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
        }

        $sql .= " GROUP BY p.product_id";

        $sort_data = array(
            'pd.name',
            'p.model',
            'p.quantity',
            'p.price',
            'rating',
            'p.sort_order',
            'p.date_added'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
                $sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
            } elseif ($data['sort'] == 'p.price') {
                $sql .= " ORDER BY (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
            } else {
                $sql .= " ORDER BY " . $data['sort'];
            }
        } else {
            $sql .= " ORDER BY p.sort_order";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC, LCASE(pd.name) DESC";
        } else {
            $sql .= " ASC, LCASE(pd.name) ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $product_data = array();

        $query = $this->db->query($sql);

        foreach ($query->rows as $result) {
            $product_data[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
        }

        return $product_data;
    }

    public function getCategories($parent_id = 0) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)");

        return $query->rows;
    }
}

