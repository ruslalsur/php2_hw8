<?php

namespace App\Controllers;

//use App\Classes\Templater;
use App\Models\Product;

class ProductController extends Controller {
    protected $template = 'product.twig';
    protected $id;
    protected $product;
    private $present;
    private $image;

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

    //создание нового товара в каталоге
    //$params = [':name'=>$name, ':description'=>$description, ':price'=>$price, ':image'=>$image]
    public function createProduct() {
        if (empty($this->app->post)) {
            $this->template = 'createProduct.twig';
            return $this->render([
                'title' => 'Новый товар',
                'hide' => $this->hidenMenuItems,
            ]);
        }

        //добавления нового изображения либо картинки по-умолчанию см. константу NO_IMAGE в config.php
        $this->image = $this->uploadFile();
        if (empty($image)) {
            $this->image = '/img/' . basename(NO_IMAGE);
        }

        $newProductDetails = [
            ':name' => $this->app->post['name'],
            ':description' => $this->app->post['description'],
            ':price' => $this->app->post['price'],
            ':image' => $this->image
        ];

        header('Location: /product/index/?id=' . Product::create($newProductDetails));
    }

    //изменение характеристик товара в каталоге
    public function updateProduct($data = []) {
        $this->id = (int)$data['id'] ?? '';
        $this->product = Product::fetchOne([$this->id]);
        var_dump($this->id);

        if (empty($this->app->post)) {
            $this->template = 'updateProduct.twig';
            return $this->render([
                'title' => 'Изменить товар',
                'hide' => $this->hidenMenuItems,
                'productDetails' => $this->product
            ]);
        }

        //изменение либо сохранение старого изображения
        $oldImage = $this->product['image'];
        $this->image = $this->uploadFile();
        if (empty($this->image)) {
            $this->image = $oldImage;
        }

        $newProductDetails = [
            ':name' => $this->app->post['name'],
            ':description' => $this->app->post['description'],
            ':price' => $this->app->post['price'],
            ':image' => $this->image,
            ':id' => $this->id
        ];
//        var_dump($newProductDetails);
//        var_dump(Product::update($newProductDetails));

        Product::update($newProductDetails) ? header('Location: /product/index/?id=' . $this->id) : false;
    }

    //удаление товара из каталога
    public function deleteProduct($id) {
        $db = createConnection();
        $sql = 'DELETE FROM products WHERE id=' . $id;
        return execQuery($sql, $db);
    }

    //загрузка файла на сервер
    protected function uploadFile() {
        $uploaddir = WWW_DIR . 'img/upload/';
        $uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
            return '/img/upload/' . basename($uploadfile);
        } else {
            return '';
        }
    }
}
