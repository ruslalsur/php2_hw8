<?php
require_once __DIR__ . '/../config/config.php';
$func = $_POST['func'] ?? '';
$argument = $_POST['argument'] ?? '';
echo $func($argument);

//добавление в корзину
function addToCart($productID)
{
//проверка авторизации пользователя
    if (empty($_SESSION['login'])) {
        session_destroy();
        return 0;
    } else {

        //добавления к добавленному продукту недостающей характеристики
        $cartProduct = getProduct($productID);
        $cartProduct += ['quantity' => 1];

        //запись в $_SESSION первой покупки
        //без этого впоследствии задается неправильная структура вложенности массива cart
        //первая покупка получается на уровень выше остальных
        if (empty($_SESSION['cart'])) {
            $cart = [];
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

        return 1;
    }
}

//удаление из корзины
function rmFromCart($productID)
{
//проверка аторизации пользователя (на всякий там пожарный)
    if (empty($_SESSION['login'])) {
        session_destroy();
        return 0;
    } else {
        foreach ($_SESSION['cart'] as $index => $item) {
            if ($item['id'] === $productID) {
                if ($item['quantity'] > 1) {
                    --$_SESSION['cart'][$index]['quantity'];
                } else {
                    unset($_SESSION['cart'][$index]);
                }
                break;
            }
        }

        return 1;
    }
}

//удаление информации о заказах сразу из двух таблиц (orders и orders_products)
function delOrder($orderId)
{
    $orderId = (int) $orderId;
    $sql1 = "DELETE FROM orders WHERE id = $orderId";
    $sql2 = "DELETE FROM orders_products WHERE order_id = $orderId";
    return execQuery($sql1) && execQuery($sql2) ? 1 : 0;
}

//изменение статуса заказа
function changeOrderStatus ($argument) {
    $orderId = (int) $argument[0];
    $selectedStatus = (int) $argument[1];
    $sqlCurrentStatus = "SELECT * FROM orders WHERE id = $orderId";

    //поменяется в любом случае для роли администратора или же если заказ не завершен
    //для пользователя будет доступна только опция отмены заказа и все ...
    if(isAdmin() || (!isAdmin() && (int)show($sqlCurrentStatus)['status'] < 3)) {
        $sql = "UPDATE orders SET status = $selectedStatus WHERE id = $orderId";
        return execQuery($sql);
    }
}