//запрос на добавление товара в корзину
$('.add').click(function() {
    $productId = $( this ).data('id');
    
    $.post({
        url: '/cart/add/',
        data: {
            productID: $productId
        },
        success: function (data) {
            if (data != 0) {
                location.reload();
            } else {
                console.log(data);
                alert("авторизуйтесь");
            }
        }
    });
});

//запрос на удаление товара в корзину
$('.remove').click(function() {
    $productId = $( this ).data('id');
    
    $.post({
        url: '/cart/remove/',
        data: {
            productID: $productId
        },
        success: function (data) {
            if (data != 0) {
                location.reload();
            } else {
                console.log(data);
                alert("авторизуйтесь");
            }
        }
    });
});

//запрос на изменение статусы заказа
$('.adm-order-status').on('change',function() {
    $orderId = $( this ).data('id');
    $selectedStatus = $(this).val();
   
    $.post({
        url: '/user/changeOrderStatus/',
        data: {
            orderId: $orderId,
            selectedStatus: $selectedStatus
        },
        success: function (data) {
            if (data != 0) {
                location.reload();
            } else {
                console.log(data);;
            }
        }
    });
});

//запрос на удаление заказа
$('button.del-order').on('click',function() {
    $orderId = $( this ).data('id');
    
    $.post({
        url: '/user/deleteOrder/',
        data: {
            orderId: $orderId
        },
        success: function (data) {
            if (data != 0) {
                location.reload();
            } else {
                console.log(data);
            }
        }
    });
});