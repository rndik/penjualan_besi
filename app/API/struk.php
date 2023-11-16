<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    //Tabel Struk
    // Get
    $app->get('/struk', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $query = $db->query('CALL ReadAllStruk()');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));

        return $response->withHeader("Content-Type", "application/json");
    });
  
    $app->get('/struk/{id}', function (Request $request, Response $response, $args) {
        $strukId = $args['id'];
        $db = $this->get(PDO::class);
    
        try {
            // Membuat prepared statement untuk memanggil stored procedure
            $stmt = $db->prepare("CALL ReadStruk(:strukId)");
            $stmt->bindParam(':strukId', $strukId, PDO::PARAM_INT);
            
            // Menjalankan stored procedure
            $stmt->execute();
            
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($product) {
                $response->getBody()->write(json_encode($product));
            } else {
                $response = $response->withStatus(404);
                $response->getBody()->write(json_encode(['message' => 'Struk tidak ditemukan']));
            }
        } catch (PDOException $e) {
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(['message' => 'Gagal membaca Struk: ' . $e->getMessage()]));
        }
    
        return $response->withHeader("Content-Type", "application/json");
    });
};