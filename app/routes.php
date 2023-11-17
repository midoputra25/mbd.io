<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    // Barang
    $app->get('/barang', function (Request $request, Response $response) {
        try {
            $db = $this->get(PDO::class);
    
            $query = $db->query('CALL selectBarang()');
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            $response->getBody()->write(json_encode($results));
    
            return $response->withHeader("Content-Type", "application/json");
        } catch (PDOException $e) {
            $errorMessage = [
                "error" => [
                    "message" => "Barang tidak ditemukan " . $e->getMessage()
                ]
            ];
            $response->getBody()->write(json_encode($errorMessage));
            return $response->withStatus(500)->withHeader("Content-Type", "application/json");
        }
    });

    $app->get('/barang/{kode_barang}', function (Request $request, Response $response, $args) {
        try {
            $db = $this->get(PDO::class);
    
            $query = $db->prepare('CALL selectBarangByKode(:kode_barang)');
            $query->bindParam(':kode_barang', $args['kode_barang'], PDO::PARAM_INT);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
    
            if (!empty($results)) {
                $response->getBody()->write(json_encode($results[0]));
            } else {
                $response->getBody()->write(json_encode(["error" => "No data found"]));
            }
    
            return $response->withHeader("Content-Type", "application/json");
        } catch (PDOException $e) {
            $errorMessage = [
                "error" => [
                    "message" => "Barang tidak ditemukan " . $e->getMessage()
                ]
            ];
            $response->getBody()->write(json_encode($errorMessage));
            return $response->withStatus(500)->withHeader("Content-Type", "application/json");
        }
    });

    $app->post('/barang', function (Request $request, Response $response) {
        try {
            $parsedBody = $request->getParsedBody();
    
            $kode_barang = $parsedBody["kode_barang"];
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
        } catch (PDOException $e) {
            $errorMessage = [
                "error" => [
                    "message" => "Gagal Menambahkan barang " . $e->getMessage()
                ]
            ];
            $response->getBody()->write(json_encode($errorMessage));
            return $response->withStatus(500)->withHeader("Content-Type", "application/json");
        }
    });

    $app->put('/barang/{kode_barang}', function (Request $request, Response $response, $args) {
        try {
            $parsedBody = $request->getParsedBody();
    
            $kode_barang = $args['kode_barang'];
            $harga_barang = $parsedBody["harga_barang"];
    
            $db = $this->get(PDO::class);
    
            $query = $db->prepare('CALL UpdateHargaBarang(?,?)'); 
            $query->execute([$harga_barang, $kode_barang]);
    
            $response->getBody()->write(json_encode(
                [
                    'message' => 'Harga barang dengan kode ' . $kode_barang . ' telah diperbarui dengan nominal ' . $harga_barang
                ]
            ));
    
            return $response->withHeader("Content-Type", "application/json");
        } catch (PDOException $e) {
            $errorMessage = [
                "error" => [
                    "message" => "Gagal update barang " . $e->getMessage()
                ]
            ];
            $response->getBody()->write(json_encode($errorMessage));
            return $response->withStatus(500)->withHeader("Content-Type", "application/json");
        }
    });

    $app->delete('/barang/{kode_barang}', function (Request $request, Response $response, $args) {
        $kode_barang =  $request->getAttribute('kode_barang');
        $db = $this->get(PDO::class);

        try {
            $query = $db->prepare('CALL HapusBarang(?)');
            $query->execute([$kode_barang]);

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
                        'message' => 'barang dengan kode barang ' . $kode_barang . ' dihapus dari database'
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


    // Detail Transaksi----------------------------------------------------------
    $app->get('/detail', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $query = $db->query('CALL selectDetail()');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));

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

    $app->put('/detail_transaksi/{kode_detail}', function (Request $request, Response $response, $args) {
        $parsedBody = $request->getParsedBody();
    
        $kode_detail = $args['kode_detail'];
        $jumlah_barang = $parsedBody["jumlah_barang"];
    
        $db = $this->get(PDO::class);
    
        $query = $db->prepare('CALL UpdateJumlahBarang(?,?)'); 
        $query->execute([$jumlah_barang, $kode_detail]);
    
        $response->getBody()->write(json_encode(
            [
                'message' => 'Jumlah barang pada detail transaksi dengan kode ' . $kode_detail . ' telah diperbarui dengan jumlah ' . $jumlah_barang
            ]
        ));
    
        return $response->withHeader("Content-Type", "application/json");
    });

    $app->delete('/detail/{kode_detail}', function (Request $request, Response $response, $args) {
        $kode_detail =  $request->getAttribute('kode_detail');
        $db = $this->get(PDO::class);

        try {
            $query = $db->prepare('CALL HapusDetail(?)');
            $query->execute([$kode_detail]);

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
                        'message' => 'detail transaksi dengan kode  ' . $kode_detail . ' dihapus dari database'
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


    // Transaksi--------------------------------------------------------------------
    $app->get('/transaksi', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $query = $db->query('CALL selectTransaksi()');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));

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
                'message' => 'Transaksi disimpan dengan id ' . $lastId
            ]
        ));

        return $response->withHeader("Content-Type", "application/json");
    });

    $app->delete('/transaksi/{kode_transaksi}', function (Request $request, Response $response, $args) {
        $kode_transaksi =  $request->getAttribute('kode_transaksi');
        $db = $this->get(PDO::class);

        try {
            $query = $db->prepare('CALL HapusTransaksi(?)');
            $query->execute([$kode_transaksi]);

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
                        'message' => 'transaksi dengan kode ' . $kode_transaksi . ' dihapus dari database'
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


    // Faktur---------------------------------------------------------------------
    $app->get('/faktur', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $query = $db->query('CALL selectFaktur()');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));

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

    $app->put('/faktur/{nomor_faktur}', function (Request $request, Response $response, $args) {
        $parsedBody = $request->getParsedBody();
    
        $nomor_faktur = $args['nomor_faktur'];
        $jatuh_tempo = $parsedBody["jatuh_tempo"];
    
        $db = $this->get(PDO::class);
    
        $query = $db->prepare('CALL UpdateJatuhTempo(?,?)'); 
        $query->execute([$jatuh_tempo, $nomor_faktur]);
    
        $response->getBody()->write(json_encode(
            [
                'message' => 'Jatuh Tempo pada faktur dengan kode ' . $nomor_faktur . ' telah diperbarui menjadi ' . $jatuh_tempo
            ]
        ));
    
        return $response->withHeader("Content-Type", "application/json");
    });

    // Supplier------------------------------------------------------------------
    $app->get('/supplier', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $query = $db->query('CALL selectSupplier()');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));

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

    $app->put('/supplier/{kode_supplier}', function (Request $request, Response $response, $args) {
        $parsedBody = $request->getParsedBody();
    
        $kode_supplier = $args['kode_supplier'];
        $nama_supplier = $parsedBody["nama_supplier"];
    
        $db = $this->get(PDO::class);
    
        $query = $db->prepare('CALL UpdateNamaSupplier(?,?)'); 
        $query->execute([$nama_supplier, $kode_supplier]);
    
        $response->getBody()->write(json_encode(
            [
                'message' => 'Nama supplier dengan kode ' . $kode_supplier . ' telah diperbarui menjadi ' . $nama_supplier
            ]
        ));
    
        return $response->withHeader("Content-Type", "application/json");
    });
  
    $app->delete('/supplier/{kode_supplier}', function (Request $request, Response $response, $args) {
        $kode_supplier =  $request->getAttribute('kode_supplier');
        $db = $this->get(PDO::class);

        try {
            $query = $db->prepare('CALL HapusSupplier(?)');
            $query->execute([$kode_supplier]);

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
                        'message' => 'supplier dengan kode supplier ' . $kode_supplier . ' dihapus dari database'
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
