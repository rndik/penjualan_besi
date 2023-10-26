<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    //TABEL Product
    // get
    $app->get('/product', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $query = $db->query('CALL ReadAllProduct()');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));

        return $response->withHeader("Content-Type", "application/json");
    });

    // get by id
    $app->get('/product/{id}', function (Request $request, Response $response, $args) {
        $productId = $args['id'];
        $db = $this->get(PDO::class);
    
        try {
            // Membuat prepared statement untuk memanggil stored procedure
            $stmt = $db->prepare("CALL ReadProduct(:productId)");
            $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
            
            // Menjalankan stored procedure
            $stmt->execute();
            
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($product) {
                $response->getBody()->write(json_encode($product));
            } else {
                $response = $response->withStatus(404);
                $response->getBody()->write(json_encode(['message' => 'Produk tidak ditemukan']));
            }
        } catch (PDOException $e) {
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(['message' => 'Gagal membaca produk: ' . $e->getMessage()]));
        }
    
        return $response->withHeader("Content-Type", "application/json");
    });
    
    //post product
    $app->post('/product', function (Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
        $namaProduct = $parsedBody["namaProduct"];
        $hargaProduct = $parsedBody["hargaProduct"];
    
        $db = $this->get(PDO::class);
    
        try {
            $db->beginTransaction();
    
            // Membuat prepared statement untuk memanggil stored procedure
            $stmt = $db->prepare("CALL TambahProduct(:namaProduct, :hargaProduct)");
            $stmt->bindParam(':namaProduct', $namaProduct, PDO::PARAM_STR);
            $stmt->bindParam(':hargaProduct', $hargaProduct, PDO::PARAM_STR);
            
            // Menjalankan stored procedure
            $stmt->execute();
    
            $response->getBody()->write(json_encode(['message' => 'Produk berhasil ditambahkan']));
        } catch (PDOException $e) {
            $db->rollBack();
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(['message' => 'Penambahan Product Gagal: ' . $e->getMessage()]));
        }
    
        return $response->withHeader("Content-Type", "application/json");
    });

    //put product
    $app->put('/product/{id}', function (Request $request, Response $response, $args) {
        $productId = $args['id'];
        $parsedBody = $request->getParsedBody();
        $namaProduct = $parsedBody["namaProduct"];
        $hargaProduct = $parsedBody["hargaProduct"];
    
        $db = $this->get(PDO::class);
    
        try {
            $db->beginTransaction();
    
            // Membuat prepared statement untuk memanggil stored procedure
            $stmt = $db->prepare("CALL UpdateProduct(:productId, :namaProduct, :hargaProduct)");
            $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
            $stmt->bindParam(':namaProduct', $namaProduct, PDO::PARAM_STR);
            $stmt->bindParam(':hargaProduct', $hargaProduct, PDO::PARAM_STR);
            
            // Menjalankan stored procedure
            $stmt->execute();
    
            // Memeriksa apakah data berhasil diperbarui
            if ($stmt->rowCount() > 0) {
                $response->getBody()->write(json_encode(['message' => 'Produk dengan ID ' . $productId . ' berhasil diperbarui']));
            } else {
                $response = $response->withStatus(404);
                $response->getBody()->write(json_encode(['message' => 'Produk dengan ID ' . $productId . ' tidak ditemukan']));
            }
        } catch (PDOException $e) {
            $db->rollBack();
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(['message' => 'Pembaruan Product Gagal: ' . $e->getMessage()]));
        }
    
        return $response->withHeader("Content-Type", "application/json");
    });
    
    //delete product
    $app->delete('/product/{id}', function (Request $request, Response $response, $args) {
        $productId = $args['id'];
        $db = $this->get(PDO::class);
    
        try {
            $db->beginTransaction();
    
            // Membuat prepared statement untuk memanggil stored procedure
            $stmt = $db->prepare("CALL HapusProduct(:productId)");
            $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
            
            // Menjalankan stored procedure
            $stmt->execute();
    
            // Memeriksa apakah data berhasil dihapus
            if ($stmt->rowCount() > 0) {
                $response->getBody()->write(json_encode(['message' => 'Produk dengan ID ' . $productId . ' berhasil dihapus']));
            } else {
                $response = $response->withStatus(404);
                $response->getBody()->write(json_encode(['message' => 'Produk dengan ID ' . $productId . ' tidak ditemukan']));
            }
        } catch (PDOException $e) {
            $db->rollBack();
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(['message' => 'Penghapusan Product Gagal: ' . $e->getMessage()]));
        }
    
        return $response->withHeader("Content-Type", "application/json");
    });
    
    
};