<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Cart;

class CartController extends Controller {
    protected $template;

    public function __construct() {
        parent::__construct();
        $this->template = 'cart.twig';

        if (!empty($_SESSION['login']) && empty( $_SESSION['cart'])) {
            $_SESSION['cartPrice'] = 0;
            $_SESSION['cartQuantity'] = 0;
        }
    }

    //метод контролера реализующий функционал отображение содержимого корзины
    public function showCartContent() {
        return $this->render(['title' => 'Содержимое корзины',
            'hide' => $this->hidenMenuItems
        ]);
    }

    //метод контролера реализующий функционал добавления товара в корзину
    public function add() {
        //проверка авторизации пользователя
        if (empty($_SESSION['login'])) {
            session_destroy();
            echo 0;
        } else {
            //добавления к добавленному продукту недостающей характеристики
            $productID = $_POST['productID'];

            $cartProduct = Cart::fetchOne([$productID]);
            $cartProduct += ['quantity' => 1];

            //запись в $_SESSION первой покупки
            //без этого впоследствии задается неправильная структура вложенности массива cart
            //первая покупка получается на уровень выше остальных
            if (empty($_SESSION['cart'])) {
                $_SESSION['cart'][0] = $cartProduct;
            } else {
                //увеличение колличества ранее добавленного
                $wasChange = false;
                foreach ($_SESSION['cart'] as $index => $item) {
                    if ($item['id'] === $productID) {
                        ++$_SESSION['cart'][$index]['quantity'];
                        $wasChange = true;
                    }
                }

                //запись в $_SESSION новой покупки
                if (!$wasChange) {
                    array_push($_SESSION['cart'], $cartProduct);
                }
            }
            $_SESSION['cartPrice'] += $cartProduct['price'];
            ++$_SESSION['cartQuantity'];
            echo 1;
        }
    }

    //метод контролера реализующий функционал удаления содержимого корзины
    function remove() {
        $productID = $_POST['productID'];
        $cartProduct = Cart::fetchOne([$productID]);

        //проверка аторизации пользователя (на всякий там пожарный)
        if (empty($_SESSION['login'])) {
            session_destroy();
            return 0;
        } else {
            foreach ($_SESSION['cart'] as $index => $item) {
                if ($item['id'] === $productID) {
                    if ($item['quantity'] > 1) {
                        --$_SESSION['cart'][$index]['quantity'];
                        --$_SESSION['cartQuantity'];
                        $_SESSION['cartPrice'] -= $cartProduct['price'];
                    } else {
                        unset($_SESSION['cart'][$index]);
                    }
                    break;
                }
            }
            echo 1;
        }
    }


}
