<?php


namespace App\Controllers;


use App\Classes\Templater;

abstract class Controller
{
	protected $template;
	protected $twig;
	protected $hidenMenuItems = [];

	public function __construct()
	{
		$this->twig = Templater::getInstance()->twig;

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

	protected function render($params = [], $template = null)
	{
		if(!$template) {
			$template = $this->template;
		}

		$twig = $this->twig->load($template);

		return $twig->render($params);
	}
}
