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

        $query = $db->query('CALL selectDetail()');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));

        return $response->withHeader("Content-Type", "application/json");
    });

    $app->get('/transaksi', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $query = $db->query('CALL selectTransaksi()');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));

        return $response->withHeader("Content-Type", "application/json");
    });

    $app->get('/faktur', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $query = $db->query('CALL selectFaktur()');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));

        return $response->withHeader("Content-Type", "application/json");
    });

    $app->get('/supplier', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $query = $db->query('CALL selectSupplier()');
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

    $app->get('/detail/{kode_detail}', function (Request $request, Response $response, $args) {
        $db = $this->get(PDO::class);

        $query = $db->prepare('CALL selectDetailByKode(:kode_detail)');
        $query->bindParam(':kode_detail', $args['kode_detail'], PDO::PARAM_INT);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results[0]));

        return $response->withHeader("Content-Type", "application/json");
    });

    $app->get('/faktur/{nomor_faktur}', function (Request $request, Response $response, $args) {
        $db = $this->get(PDO::class);

        $query = $db->prepare('CALL selectFakturByKode(:nomor_faktur)');
        $query->bindParam(':nomor_faktur', $args['nomor_faktur'], PDO::PARAM_INT);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results[0]));

        return $response->withHeader("Content-Type", "application/json");
    });

    $app->get('/supplier/{kode_supplier}', function (Request $request, Response $response, $args) {
        $db = $this->get(PDO::class);

        $query = $db->prepare('CALL selectSupplierByKode(:kode_supplier)');
        $query->bindParam(':kode_supplier', $args['kode_supplier'], PDO::PARAM_INT);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results[0]));

        return $response->withHeader("Content-Type", "application/json");
    });

    $app->get('/transaksi/{kode_transaksi}', function (Request $request, Response $response, $args) {
        $db = $this->get(PDO::class);

        $query = $db->prepare('CALL selectTransaksiByKode(:kode_transaksi)');
        $query->bindParam(':kode_transaksi', $args['kode_transaksi'], PDO::PARAM_INT);
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

        $query = $db->prepare('CALL TambahBarang(?, ?, ?, ?)');

        $query->execute([$kode_barang, $nama_barang, $harga_barang, $stok_barang]);

        $lastId = $db->lastInsertId();

        $response->getBody()->write(json_encode(
            [
                'message' => 'barang disimpan dengan id ' . $lastId
            ]
        ));

        return $response->withHeader("Content-Type", "application/json");
    });

    $app->post('/detail', function (Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();

        $kode_detail = $parsedBody["kode_detail"]; 
        $kode_barang = $parsedBody["kode_barang"];
        $kode_transaksi = $parsedBody["kode_transaksi"];
        $jumlah_barang = $parsedBody["jumlah_barang"];

        $db = $this->get(PDO::class);

        $query = $db->prepare('CALL TambahDetail(?, ?, ?, ?)');

        $query->execute([$kode_detail, $kode_barang, $kode_transaksi, $jumlah_barang]);

        $lastId = $db->lastInsertId();

        $response->getBody()->write(json_encode(
            [
                'message' => 'detail disimpan dengan id ' . $lastId
            ]
        ));

        return $response->withHeader("Content-Type", "application/json");
    });

    $app->post('/faktur', function (Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();

        $nomor_faktur = $parsedBody["nomor_faktur"]; 
        $kode_supplier = $parsedBody["kode_supplier"];
        $tanggal_faktur = $parsedBody["tanggal_faktur"];
        $jatuh_tempo = $parsedBody["jatuh_tempo"];

        $db = $this->get(PDO::class);

        $query = $db->prepare('CALL TambahFaktur(?, ?, ?, ?)');

        $query->execute([$nomor_faktur, $kode_supplier, $tanggal_faktur, $jatuh_tempo]);

        $lastId = $db->lastInsertId();

        $response->getBody()->write(json_encode(
            [
                'faktur' => 'detail disimpan dengan id ' . $lastId
            ]
        ));

        return $response->withHeader("Content-Type", "application/json");
    });

    $app->post('/supplier', function (Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();

        $kode_supplier = $parsedBody["kode_supplier"]; 
        $nama_supplier = $parsedBody["nama_supplier"];

        $db = $this->get(PDO::class);

        $query = $db->prepare('CALL TambahSupplier(?, ?)');

        $query->execute([$kode_supplier, $nama_supplier]);

        $lastId = $db->lastInsertId();

        $response->getBody()->write(json_encode(
            [
                'message' => 'Supplier disimpan dengan id ' . $lastId
            ]
        ));

        return $response->withHeader("Content-Type", "application/json");
    });

    $app->post('/transaksi', function (Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();

        $kode_transaksi = $parsedBody["kode_transaksi"]; 
        $nomor_faktur = $parsedBody["nomor_faktur"];
        $total_harga = $parsedBody["total_harga"];

        $db = $this->get(PDO::class);

        $query = $db->prepare('CALL TambahTransaksi(?, ?, ?)');

        $query->execute([$kode_transaksi, $nomor_faktur, $total_harga]);

        $lastId = $db->lastInsertId();

        $response->getBody()->write(json_encode(
            [
                'message' => 'detail disimpan dengan id ' . $lastId
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
