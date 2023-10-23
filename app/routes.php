<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    // get
    $app->get('/barang', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $query = $db->query('CALL selectBarang()');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));

        return $response->withHeader("Content-Type", "application/json");
    });

    $app->get('/detail', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $query = $db->query('CALL selectBarang()');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));

        return $response->withHeader("Content-Type", "application/json");
    });

    $app->get('/transaksi', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $query = $db->query('CALL selectBarang()');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));

        return $response->withHeader("Content-Type", "application/json");
    });

    // get by id
    $app->get('/barang/{kode_barang}', function (Request $request, Response $response, $args) {
        $db = $this->get(PDO::class);

        $query = $db->prepare('CALL selectBarangByKode(:kode_barang)');
        $query->bindParam(':kode_barang', $args['kode_barang'], PDO::PARAM_INT);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results[0]));

        return $response->withHeader("Content-Type", "application/json");
    });

    // post data
    $app->post('/barang', function (Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();

        $kode_barang = $parsedBody["kode_barang"]; // menambah dengan kolom baru
        $nama_barang = $parsedBody["nama_barang"];
        $harga_barang = $parsedBody["harga_barang"];
        $stok_barang = $parsedBody["stok_barang"];

        $db = $this->get(PDO::class);

        $query = $db->prepare('INSERT INTO barang (kode_barang, nama_barang, harga_barang, stok_barang) values (?, ?, ?, ?)');

        // urutan harus sesuai dengan values
        $query->execute([$kode_barang, $nama_barang, $harga_barang, $stok_barang]);

        $lastId = $db->lastInsertId();

        $response->getBody()->write(json_encode(
            [
                'message' => 'barang disimpan dengan id ' . $lastId
            ]
        ));

        return $response->withHeader("Content-Type", "application/json");
    });

    // put data
    $app->put('/barang/{kode_barang}', function (Request $request, Response $response, $args) {
        $parsedBody = $request->getParsedBody();
    
        $currentId = $args['kode_barang'];
        $harga_barang = $parsedBody["harga_barang"];
    
        $db = $this->get(PDO::class);
    
        $query = $db->prepare('UPDATE barang SET harga_barang = ? WHERE kode_barang = ?'); // Ganti 'id' dengan 'kode_barang'
        $query->execute([$harga_barang, $currentId]);
    
        $response->getBody()->write(json_encode(
            [
                'message' => 'Harga barang dengan kode ' . $currentId . ' telah diperbarui dengan nominal ' . $harga_barang
            ]
        ));
    
        return $response->withHeader("Content-Type", "application/json");
    });

    // delete data
    $app->delete('/barang/{id}', function (Request $request, Response $response, $args) {
        $currentId = $args['id'];
        $db = $this->get(PDO::class);

        try {
            $query = $db->prepare('DELETE FROM countries WHERE id = ?');
            $query->execute([$currentId]);

            if ($query->rowCount() === 0) {
                $response = $response->withStatus(404);
                $response->getBody()->write(json_encode(
                    [
                        'message' => 'Data tidak ditemukan'
                    ]
                ));
            } else {
                $response->getBody()->write(json_encode(
                    [
                        'message' => 'country dengan id ' . $currentId . ' dihapus dari database'
                    ]
                ));
            }
        } catch (PDOException $e) {
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(
                [
                    'message' => 'Database error ' . $e->getMessage()
                ]
            ));
        }

        return $response->withHeader("Content-Type", "application/json");
    });
};
