<?php

namespace App\Controllers;

use App\Classes\Templater;
use App\Models\Product;

class ProductsController extends Controller {
    protected $template = 'products.twig';

    public function index($data = []) {
        $products = Product::fetchAll();

        //подготовка меню для отображения
        $this->hidenMenuItems['products'] = 'hide';

        return $this->render(['title' => 'Каталог товаров',
            'products' => $products,
            'hide' => $this->hidenMenuItems
        ]);
    }
}
