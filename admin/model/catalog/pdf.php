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
                <div class="col">
                    <div class="d-flex justify-content-center">
                        <img src="$image" class="img-fluid" alt="$name">
                    </div>
                    <div class="count">
                        <p class="d-flex justify-content-center">$price ₽</p>
                    </div>
                    <div class="description">
                        <p class="d-flex justify-content-center">$name</p>
                    </div>
                </div>
            EOF;

                $data_html_product .= $data_product;

                $total++;
                $key++;
            }

            $category_name = $category['name'];

            $data_category = <<<EOF
                <section class="container mt-5">
                    <div class="d-flex justify-content-center">
                        <h2 class="fw-bold">$category_name</h2>
                    </div>
                    <div class="row">
                        $data_html_product
                    </div>
                </section>
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
                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
                        <style>
                            .header_tel{
                                text-align: center;
                            }
                            .block_tel{
                                display: flex;
                                justify-content: center;
                                margin: auto;
                                align-items: center;
                                height: 100%;
                            }
                      </style>
                    </head>
                    <body>
                    <div class="alert alert-primary" role="alert">
                      A simple primary alert—check it out!
                    </div>
                         <header class="container">
                            <div class="row">
                                <div class="col">
                                    <div>
                                        <a href="https://www.odnorazovayaposudaykt.ru/index.php?route=common/home"><img src="$logo" title="Интернет магазин Одноразовой посуды" alt="Интернет магазин Одноразовой посуды" class="img-responsive"></a>
                                    </div>
                                </div>
                                    
                                <div class="col-md-4 col-sm-12 col-xs-12 header-contacts">
                                    <div class="block_tel">
                                        <div>
                                            <div class="schedule header_tel heigth">
                                                <span>Ежедневно с 09:00 до 20:00</span>
                                            </div>
                                            <div>
                                                <p class="m-0">ИП АСЕЕВА О.Ю.ИНН 143520612490</p>
                                                <p class="m-0">Телефон Оптового отдела: <a href="tel:+79644217505">+7964-421-75-05</a></p>
                                                <p class="m-0">Телефон магазина: <a href="tel:+79644217505">+7964-421-75-05</a></p>
                                                <p class="m-0">Адрес: ул. Красильникова, 3в, Якутск, Респ. Саха (Якутия), Россия, 677007</p>
                                                <p class="m-0">Сайт доставки: <a href="http://www.odnorazovayaposudaykt.ru/">http://www.odnorazovayaposudaykt.ru/</a> </p>
                                                <p class="m-0">Email: <a href="mail: odnorazovaya_posuda_ykt@mail.ru"> odnorazovaya_posuda_ykt@mail.ru</a></p>
                                                <p class="m-0"><a href="Www.instagram.com/odnorazovaya_posuda_ykt">Www.instagram.com/odnorazovaya_posuda_ykt</a></p>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </header> 
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
