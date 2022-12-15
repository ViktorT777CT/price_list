<?php
class ControllerCommonPdf extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('common/pdf');
        $this->document->setTitle($this->language->get('heading_title'));

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
           $this->download();
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('common/pdf', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('common/pdf', 'user_token=' . $this->session->data['user_token'], true);
        $data['user_token'] = $this->session->data['user_token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['categories'] = $this->getList();

        $this->response->setOutput($this->load->view('common/pdf', $data));
    }

    private function download()
    {
        $max = (int)($this->request->post['max'] ?? 0);
        $category_ids = $this->request->post['category'];
        $this->load->model('catalog/pdf');

        $this->model_catalog_pdf->generatePdf($max, $category_ids);
    }

    private function getList() {

        $this->load->language('catalog/category');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('catalog/category');

        $data['categories'] = array();

        $filter_data = array(
            'sort'  => 'name',
            'order' => 'ASC',
            'start' => 0,
            'limit' => 50000
        );

        $results = $this->model_catalog_category->getCategories($filter_data);

        foreach ($results as $result) {
            $level = $this->model_catalog_category->getCategoryPath($result['category_id']);
            $category = $this->model_catalog_category->getCategory($result['category_id']);
           // echo"<pre>"; var_dump(compact('level', 'category')); die();

            if (!empty($level) && count($level) === 2 && $category['status'] == 1) {
                $data[] = array(
                    'category_id' => $result['category_id'],
                    'name'        => $result['name'],
                    'category' => $category,
                    'level' => $level,
                );
            }
        }

        return $data ?? [];
    }
}
