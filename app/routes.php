<?php

declare(strict_types=1);

use Slim\App;

return function (App $app) {
    //Tabel Customer
    $customerRoutes = require __DIR__.'/API/customer.php';
    $customerRoutes($app);

    //Tabel Product
    $productRoutes = require __DIR__.'/API/product.php';
    $productRoutes($app);

    // //Tabel Transaksi
    $productRoutes = require __DIR__.'/API/transaksi.php';
    $productRoutes($app);
    
    // //Tabel Struk
    $productRoutes = require __DIR__.'/API/struk.php';
    $productRoutes($app);
};