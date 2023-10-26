<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    //TABEL CUSTOMER
    // get
    $app->get('/customer', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $query = $db->query('CALL ReadAllCustomer()');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));

        return $response->withHeader("Content-Type", "application/json");
    });

    // get by id
    $app->get('/customer/{id}', function (Request $request, Response $response, $args) {
        $db = $this->get(PDO::class);
        $customerId = $args['id'];
    
        $query = $db->prepare('CALL ReadCustomer(:customerId)');
        $query->bindParam(':customerId', $customerId, PDO::PARAM_STR);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
    
        if (count($results) > 0) {
            $response->getBody()->write(json_encode($results[0]));
        } else {
            $response->getBody()->write(json_encode(['message' => 'Customer not found']));
        }
    
        return $response->withHeader("Content-Type", "application/json");
    });
    

    // post customer
    $app->post('/customer', function (Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
        $customerId = $parsedBody["customerId"];
        $namaCustomer = $parsedBody["namaCustomer"];
        $alamatCustomer = $parsedBody["alamatCustomer"];
        $noTelpCustomer = $parsedBody["noTelpCustomer"];
    
        $db = $this->get(PDO::class);
    
        try {
            $db->beginTransaction();
    
            // Membuat prepared statement untuk memanggil stored procedure
            $stmt = $db->prepare("CALL TambahCustomer(:customerId, :namaCustomer, :alamatCustomer, :noTelpCustomer)");
            $stmt->bindParam(':customerId', $customerId, PDO::PARAM_INT);
            $stmt->bindParam(':namaCustomer', $namaCustomer, PDO::PARAM_STR);
            $stmt->bindParam(':alamatCustomer', $alamatCustomer, PDO::PARAM_STR);
            $stmt->bindParam(':noTelpCustomer', $noTelpCustomer, PDO::PARAM_STR);
            
            // Menjalankan stored procedure
            $stmt->execute();
    
            $response->getBody()->write(json_encode(['message' => 'Pelanggan berhasil ditambahkan']));
        } catch (PDOException $e) {
            $db->rollBack();
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(['message' => 'Penambahan Pelanggan Gagal: ' . $e->getMessage()]));
        }
    
        return $response->withHeader("Content-Type", "application/json");
    });
    
    

    // put customer
    $app->put('/customer/{id}', function (Request $request, Response $response, $args) {
        $customerId = $args['id'];
        $parsedBody = $request->getParsedBody();
        $namaCustomer = $parsedBody["namaCustomer"];
        $alamatCustomer = $parsedBody["alamatCustomer"];
        $noTelpCustomer = $parsedBody["noTelpCustomer"];
    
        $db = $this->get(PDO::class);
    
        try {
            $db->beginTransaction();
    
            // Membuat prepared statement untuk memanggil stored procedure
            $stmt = $db->prepare("CALL UpdateCustomer(:customerId, :namaCustomer, :alamatCustomer, :noTelpCustomer)");
            $stmt->bindParam(':customerId', $customerId, PDO::PARAM_INT);
            $stmt->bindParam(':namaCustomer', $namaCustomer, PDO::PARAM_STR);
            $stmt->bindParam(':alamatCustomer', $alamatCustomer, PDO::PARAM_STR);
            $stmt->bindParam(':noTelpCustomer', $noTelpCustomer, PDO::PARAM_STR);
            
            // Menjalankan stored procedure
            $stmt->execute();
    
            $response->getBody()->write(json_encode(['message' => 'Data pelanggan dengan ID ' . $customerId . ' telah diubah']));
        } catch (PDOException $e) {
            $db->rollBack();
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(['message' => 'Pengubahan Data Pelanggan Gagal: ' . $e->getMessage()]));
        }
    
        return $response->withHeader("Content-Type", "application/json");
    });
    
    

    // delete customer
    $app->delete('/customer/{id}', function (Request $request, Response $response, $args) {
        $customerId = $args['id'];
        $db = $this->get(PDO::class);
    
        try {
            $db->beginTransaction();
    
            // Membuat prepared statement untuk memanggil stored procedure
            $stmt = $db->prepare("CALL HapusCustomer(:customerId)");
            $stmt->bindParam(':customerId', $customerId, PDO::PARAM_INT);
            
            // Menjalankan stored procedure
            $stmt->execute();
        
            // Memeriksa apakah data berhasil dihapus
            if ($stmt->rowCount() > 0) {
                $response->getBody()->write(json_encode(['message' => 'Customer dengan ID ' . $customerId . ' berhasil dihapus']));
            } else {
                $response = $response->withStatus(404);
                $response->getBody()->write(json_encode(['message' => 'Customer dengan ID ' . $customerId . ' tidak ditemukan']));
            }
        } catch (PDOException $e) {
            $db->rollBack();
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(['message' => 'Hapus Customer Gagal: ' . $e->getMessage()]));
        }
    
        return $response->withHeader("Content-Type", "application/json");
    });
};
