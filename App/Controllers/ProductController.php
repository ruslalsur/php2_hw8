<?php

namespace App\Controllers;

//use App\Classes\Templater;
use App\Models\Product;

class ProductController extends Controller {
    protected $template = 'product.twig';
    private $id;
    protected $product;
    private $present;

    public function index($data = []) {
        $this->id = (int)$data['id'] ?? '';
        $this->product = Product::fetchOne([$this->id]);

        if (!empty($_SESSION['login'])) {
            //запоминание первой просмотренной страницы товара в список просмотренных товаров
            if (empty($_SESSION['viewed'])) {
                $_SESSION['viewed'][] = $this->product;
            }

            //проверка на наличие просматриваемой страницы товара в списке
            $this->present = false;
            foreach ($_SESSION['viewed'] as $viewedEach) {
                if (in_array($this->product['id'], $viewedEach)) {
                    $this->present = true;
                }
            }

            //добавление страницы в список просмотренных, если там такой уже нет
            if (!$this->present) {
                $_SESSION['viewed'][] = $this->product;
            }

            //проверка на размер списка просмотренных страниц пользователя
            if (count($_SESSION['viewed']) > 5) {
                array_shift($_SESSION['viewed']);
            }
        }

        return $this->render(['title' => 'О товаре',
            'product' => $this->product,
            'hide' => $this->hidenMenuItems
        ]);
    }
}
