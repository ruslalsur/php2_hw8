<?php


namespace App\Controllers;


use App\Classes\Templater;
use App\App;

abstract class Controller {
    protected $template;
    protected $twig;
    protected $hidenMenuItems = [];
    protected $app;

    public function __construct() {
        $this->twig = Templater::getInstance()->twig;
        $this->app = App::getInstance();

        if (empty($_SESSION['login'])) {
            $this->hidenMenuItems['cart'] = 'hide';
            $this->hidenMenuItems['order'] = 'hide';
            $this->hidenMenuItems['profile'] = 'hide';
            $this->hidenMenuItems['logout'] = 'hide';
        } else {
            if (empty($_SESSION['cart'])) {
                $this->hidenMenuItems['cart'] = 'hide';
                $this->hidenMenuItems['order'] = 'hide';
            }
            $this->hidenMenuItems['reg'] = 'hide';
            $this->hidenMenuItems['login'] = 'hide';
        }
    }

    /**
     * @param array $params
     * @param string|null $template
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    protected function render(?array $params = [], ?string $template = null): string {
        if (!$template) {
            $template = $this->template;
        }

        $twig = $this->twig->load($template);

        $params = array_merge([
            'session' => $this->app->session,
        ], $params);

        return $twig->render($params);
    }
}