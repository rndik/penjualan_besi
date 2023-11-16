<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    //Tabel Transaksi
    //Get
    $app->get('/transaksi', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);
    
        try {
            // Membuat prepared statement untuk memanggil stored procedure
            $stmt = $db->prepare("CALL ReadAllTransaksi()");
            
            // Menjalankan stored procedure
            $stmt->execute();
            
            $allTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if ($allTransactions) {
                $response->getBody()->write(json_encode($allTransactions));
            } else {
                $response = $response->withStatus(404);
                $response->getBody()->write(json_encode(['message' => 'Tidak ada transaksi ditemukan']));
            }
        } catch (PDOException $e) {
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(['message' => 'Gagal membaca semua transaksi: ' . $e->getMessage()]));
        }
    
        return $response->withHeader("Content-Type", "application/json");
    });
    
    //Get by id
    $app->get('/transaksi/{id}', function (Request $request, Response $response, $args) {
        $transactionId = $args['id'];
        $db = $this->get(PDO::class);
    
        try {
            // Membuat prepared statement untuk memanggil stored procedure
            $stmt = $db->prepare("CALL ReadTransaksi(:transactionId)");
            $stmt->bindParam(':transactionId', $transactionId, PDO::PARAM_INT);
            
            // Menjalankan stored procedure
            $stmt->execute();
            
            $transactionDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if ($transactionDetails) {
                $response->getBody()->write(json_encode($transactionDetails));
            } else {
                $response = $response->withStatus(404);
                $response->getBody()->write(json_encode(['message' => 'Transaksi tidak ditemukan']));
            }
        } catch (PDOException $e) {
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(['message' => 'Gagal membaca transaksi: ' . $e->getMessage()]));
        }
    
        return $response->withHeader("Content-Type", "application/json");
    });    

    //Put transaksi
    $app->post('/transaksi', function (Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
        $customerId = $parsedBody["customerId"];
        $tanggal = $parsedBody["tanggal"];
        $productId = $parsedBody["productId"];
        $jumlah = $parsedBody["jumlah"];
    
        $db = $this->get(PDO::class);
    
        try {
            $db->beginTransaction();
    
            // Membuat prepared statement untuk memanggil stored procedure
            $stmt = $db->prepare("CALL Transaksi(:customerId, :tanggal, :productId, :jumlah)");
            $stmt->bindParam(':customerId', $customerId, PDO::PARAM_INT);
            $stmt->bindParam(':tanggal', $tanggal, PDO::PARAM_STR);
            $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
            $stmt->bindParam(':jumlah', $jumlah, PDO::PARAM_INT);
            
            // Menjalankan stored procedure
            $stmt->execute();
    
            $db->commit();
    
            $response->getBody()->write(json_encode(['message' => 'Transaksi berhasil']));
        } catch (PDOException $e) {
            $db->rollBack();
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(['message' => 'Transaksi Gagal: ' . $e->getMessage()]));
        }
    
        return $response->withHeader("Content-Type", "application/json");
    });
    
    //Delete transaksi
    $app->delete('/transaksi/{id}', function (Request $request, Response $response, $args) {
        $transactionId = $args['id'];
        $db = $this->get(PDO::class);
    
        try {
            $db->beginTransaction();
    
            // Membuat prepared statement untuk memanggil stored procedure
            $stmt = $db->prepare("CALL HapusTransaksi(:transactionId)");
            $stmt->bindParam(':transactionId', $transactionId, PDO::PARAM_INT);
            
            // Menjalankan stored procedure
            $stmt->execute();
    
            $db->commit();
    
            // Memeriksa apakah data berhasil dihapus
            if ($stmt->rowCount() > 0) {
                $response->getBody()->write(json_encode(['message' => 'Transaksi dengan ID ' . $transactionId . ' berhasil dihapus']));
            } else {
                $response = $response->withStatus(404);
                $response->getBody()->write(json_encode(['message' => 'Transaksi dengan ID ' . $transactionId . ' tidak ditemukan']));
            }
        } catch (PDOException $e) {
            $db->rollBack();
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(['message' => 'Hapus Transaksi Gagal: ' . $e->getMessage()]));
        }
    
        return $response->withHeader("Content-Type", "application/json");
    });
    
};