<?php

/**
 * https://eclecticgeek.com/dompdf/debug.php
 */

use Dompdf\Dompdf;
use Dompdf\Options;

class ModelCatalogPdf extends Model {
    public function generatePdf($max)
    {
        ini_set('max_execution_time', 900);
        $root_path = dirname(DIR_APPLICATION);

        require $root_path . '/vendor/autoload.php';

        $this->load->model('catalog/category');
        $this->load->model('catalog/product');

        $categories = $this->model_catalog_category->getCategories();
        $data_html_category = '';
        $total = 0;

        foreach ($categories as $category) {
            if (!empty($max) && $total === $max) {
                continue;
            }

            $products = $this->model_catalog_product->getProductsByCategoryId($category['category_id']);
            $data_html_product = '';

            $key = 1;
            foreach ($products as $product)
            {
                if (!empty($max) && $total > $max) {
                    continue;
                }

                $image_path = DIR_IMAGE . $product['image'];

                if (!file_exists($image_path) || empty($product['image'])) {
                    $image_path = DIR_IMAGE . 'no_image.png';
                }

                $data = file_get_contents($image_path);
                $type = pathinfo($image_path, PATHINFO_EXTENSION);
                $image = 'data:image/' . $type . ';base64,' . base64_encode($data);

                $price = (int)$product['price'];
                $name = $product['name'];

                $data_product = <<<EOF
                            <tr>
                                <td>
                                    <table>
                                        <tr>
                                            <td>
                                                <img src="$image" class="col" alt="$name">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="block_tel">$price ₽</td>
                                        </tr>
                                        <tr>
                                            <td class="block_tel">$name</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                
            EOF;

                $data_html_product .= $data_product;

                $total++;
                $key++;
            }

            $category_name = $category['name'];

            $data_category = <<<EOF
                        <table class="container">
                            <tr>
                                <th>
                                    <h2 class="fw-bold">$category_name</h2>
                                </th>
                            </tr>
                                $data_html_product
                        </table>
            EOF;

            $data_html_category .= $data_category;
        }

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        //$options->set('debugCss', true);


        $data = file_get_contents(DIR_IMAGE . 'catalog/POSUDA22.png');
        $type = pathinfo(DIR_IMAGE . 'catalog/POSUDA22.png', PATHINFO_EXTENSION);
        $logo = 'data:image/' . $type . ';base64,' . base64_encode($data);

        $mpdf = new Dompdf($options);
        $html = <<<EOF
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        <meta http-equiv="X-UA-Compatible" content="IE=edge">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <meta content="text/html; charset=UTF-8" http-equiv="Content-Type"/>
                        <style>
                            .container{
                                width: 200mm;
                                margin: auto;
                                padding-top: 30px;
                            }
                            .logo{
                                width: 100mm;
                            }
                            .col {
                                width: 40mm;
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
                                    <a href="https://www.odnorazovayaposudaykt.ru/index.php?route=common/home"><img class="logo img-responsive" src="https://www.odnorazovayaposudaykt.ru/image/catalog/POSUDA22.png" title="Интернет магазин Одноразовой посуды" alt="Интернет магазин Одноразовой посуды"></a>
                                </td>
                                <td>
                                    <table>
                                        <tr>
                                            <td>Ежедневно с 09:00 до 20:00</td>
                                        </tr>
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
}
