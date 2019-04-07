<?php

namespace App\Controllers;

use App\Models\User;

class UserController extends Controller {
    protected $template;

    //метод контролера пользователя реализующий функционал залогинивания пользователя
    public function login() {
        $this->template = 'login.twig';
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';
        $msg = '';

        //Если логин и пароль переданы попытаемся авторизоваться
        if ($login && $password) {
            //преобразуем пароль в хэш
            $password = md5($password);

            //получаем пользователя из базы по логин-паролю
            $user = User::userIsRegistred([$login, $password]);

            //если пользователь найден. Записываем его в сессию
            if ($user) {
                $_SESSION['login'] = $user;
                header('Location: /user/profile/');
            } else {
                $msg = 'Неверная пара';
            }
        }

        //подготовка меню для отображения
        $this->hidenMenuItems['login'] = 'hide';

        return $this->render(['title' => 'Вход',
            'cartQuantity' => $_SESSION['cartQuantity'] ?? '',
            'cartPrice' => $_SESSION['cartPrice'] ?? '',
            'hide' => $this->hidenMenuItems,
            'message' => $msg
        ]);
    }

    //метод контролера пользователя реализующий функционал разлогинивания пользователя
    public function logout() {
        //Убиваем сессию и тем самым разлогиниваем пользователя
        session_destroy();
        header('Location: /products/index/');
    }

    //метод контролера пользователя реализующий функционал личного кабинета
    public function profile($data = []) {
        $viwedHeader = "";

        //авдруг кто то через адресную строку зайдет сюда
        if (empty($_SESSION['login'])) {
            header('Location: /login.php');
        }

        //подготовка меню для отображения
        $this->hidenMenuItems['profile'] = 'hide';

        if (!empty($_SESSION['viewed'])) {
            $viwedHeader = 'Вами недавно было просмотрено:';
        }


        //генерация списка заказов
        if (User::isAdmin()) {
            $orders = User::fetchOrders();
        } else {
            $userId = (int)$_SESSION['login']['id'];
            $orders = User::fetchUserOrders([$userId]);
        }
        $result = '';
        foreach ($orders as $order) {
            $orderId = (int)$order['id'];
            $orderProducts = User::fetchOrderProducts([$orderId]);
            $orderSum = 0;
            $amountSum = 0;
            $content = '';
            $this->template = 'orderRow.twig';
            foreach ($orderProducts as $product) {
                $amount = $product['amount'];
                $price = $product['price'];
                $productSum = $amount * $price;
                $content .= $this->render([
                    'img' => $product['image'],
                    'name' => $product['name'],
                    'id' => $product['id'],
                    'amount' => $amount,
                    'price' => $price,
                    'sum' => $productSum
                ]);

                $amountSum += $amount;
                $orderSum += $productSum;
            }

            $statuses = [
                0 => 'оформлен',
                1 => 'собирается',
                2 => 'готов',
                3 => 'завершен',
                4 => 'отменен'
            ];

            //кто именно назаказывал то стока ...
            $orderUserName = User::fetchOne([$order['user_id']])['name'];

            //генерация заголовка заказа
            $subString = '<select class="adm-order-status"  data-id="' . $orderId . '">';
            foreach ($statuses as $key => $status) {

                //выяснение сколько статусов для выбора будет недоступно пользователю при выборе
                if ($order['status'] == 3) {
                    $amountUserStatusDisable = 4;
                } else {
                    $amountUserStatusDisable = 3;
                }

                //у пользователя будет доступна для выбора только опция отмены заказа либо никакая, у админа все вседа
                if (!User::isAdmin() && (int)$key <= $amountUserStatusDisable) {
                    $disable = 'disabled';
                } else {
                    $disable = '';
                }
                if ($key == $order['status']) {
                    $subString .= '<option ' . $disable . ' value="' . $key . '" selected>' . $status . '</option>';
                } else {
                    $subString .= '<option ' . $disable . ' value="' . $key . '">' . $status . '</option>';
                }
            }
            $subString .= '</select>&nbsp';

            //реализация административных возможностей для заказа
            if (User::isAdmin()) {
                $subString .= '<button class="del-order" data-id="' . $orderId . '">удалить</button>';
            }

            //продолжение генерации заголовка
            $orderHeader =
                '<div class="order-container">
                <div class="order-row">
                    <div class="order-col header-row">
                        &nbspЗАКАЗ&nbsp' . $orderId . '&nbsp (' . $orderUserName . ')&nbsp' . $subString . '
                    </div>
                    <div class="order-col header-row">цена за шт.</div>
                    <div class="order-col header-row">штук</div>
                    <div class="order-col header-row">итого</div>
                </div>';

            //генерация строки "итого" заказа
            $orderFooter =
                '<div class="order-row footer">
                    <div class="order-col">ИТОГО</div>
                    <div class="order-col"></div>
                    <div class="order-col">' . $amountSum . '</div>
                    <div class="order-col">$' . $orderSum . '</div>
                </div>
            </div>';

            //сборка всей разметки заказа
            $result .=  $orderHeader . $content . $orderFooter;

        }
        $result = '<h3>Список Ваших заказов:</h3>' . $result;

        $this->template = 'profile.twig';
        return $this->render(['title' => 'Личный кабинет',
            'cartQuantity' => $_SESSION['cartQuantity'] ?? '',
            'cartPrice' => $_SESSION['cartPrice'] ?? '',
            'hide' => $this->hidenMenuItems,
            'ordersContent' => $result,
            'viewedHeader' => $viwedHeader,
            'userName' => $_SESSION['login']['name'],
            'products' => $_SESSION['viewed'] ?? ''
        ]);
    }

    //метод контролера пользователя реализующий функционал добавления информации о заказе
    public function orderer() {
        if (empty($_SESSION['login'])) {
            header('Location: /user/login/');
        }

        $userId = (int)$_SESSION['login']['id'];

        $orderId = User::insertOrder([$userId]);

        if (!$orderId) {
            echo "ошибка добавления пользователя в таблицу заказов";
            exit();
        }

        //создание запроса с множественными значениями (сразу несколько строк)
        $values = [];
        foreach ($_SESSION['cart'] as $cartItem) {
            $productID = (int)$cartItem['id'];
            $amount = (int)$cartItem['quantity'];
            $values[] = "($orderId, $productID, $amount)";
        }
        $values = implode(', ', $values);

        //потом превентирую от иньекций
        if (User::insertOrderProducts("INSERT INTO `orders_products` (`order_id`, `product_id`, `amount`) VALUES $values")) {
            unset($_SESSION['cart']);
            header('Location: /user/profile/');
        } else {
            echo 'заказ не создан';
        }
    }

    //метод контролера пользователя реализующий функционал удаления информации о заказе
    public function deleteOrder() {
        $orderId = $_POST['orderId'];
        echo User::delOrder([$orderId]);
    }

    //метод контролера пользователя реализующий функционал изменения статуса заказа
    function changeOrderStatus () {
        $orderId = $_POST['orderId'];
        $selectedStatus = $_POST['selectedStatus'];

        //поменяется в любом случае для роли администратора или же если заказ не завершен
        //для пользователя будет доступна только опция отмены заказа и все ...
        if(User::isAdmin() || (!User::isAdmin() && (int)User::fetchOne([$orderId]) < 3)) {
            echo User::updateOrderStatus([$selectedStatus, $orderId]);
        }
    }
}
