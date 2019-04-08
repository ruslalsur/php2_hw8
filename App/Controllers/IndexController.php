<?php

namespace App\Controllers;

class IndexController extends Controller {
    protected $template = 'index.twig';

    public function index($data = []) {
        //подготовка меню для отображения
        $this->hidenMenuItems['main'] = 'hide';

        return $this->render(['title' => 'Главная страница',
            'hide' => $this->hidenMenuItems

        ]);
    }
}
