<?php

require_once 'vendor/autoload.php';

$app = new \Slim\Slim();

$db = new mysqli('localhost', 'root', 'root', 'curso_angular4');

// Configuración de cabeceras
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}

$app->get("/pruebas", function() use($app, $db){
    echo "Hola mundo desde Slim PHP";
    var_dump($db);
});

// LISTAR TODOS LOS PRODUCTOS
$app->get("/productos", function() use($app, $db){
    $sql = 'SELECT * FROM productos ORDER BY id DESC;';
    $query = $db->query($sql);

    $productos = array();
    while($producto = $query->fetch_assoc()){
        $productos[] = $producto;
    }
    
    $result = array(
        'status' => 'success',
        'code' => 200,
        'data' => $productos
    );

    echo json_encode($result);
});

// DEVOLVER UN SOLO PRODUCTO
$app->get("/productos/:id", function() use($app, $db){
    $sql = 'SELECT * FROM productos WHERE id = '.$id.';';
    $query = $db->query($sql);

    $result = array(
        'status' => 'error',
        'code' => 404,
        'message' => "Producto no disponible"
    );

    if($query->num_rows == 1) {
        $producto = $query->fetch_assoc();

        $result = array(
            'status' => 'success',
            'code' => 200,
            'data' => $producto
        );
    }
    
    echo json_encode($result);
});

// ELIMINAR UN PRODUCTO
$app->get("/delete-producto/:id", function($id) use($app, $db){
    $sql = 'DELETE FROM productos WHERE id = '.$id.';';
    $query = $db->query($sql);

    $result = array(
        'status' => 'error',
        'code' => 404,
        'message' => "El producto NO se ha eliminado"
    );

    if($query) {
        $result = array(
            'status' => 'success',
            'code' => 200,
            'message' => 'El producto se ha eliminado correctamente'
        );
    }
    
    echo json_encode($result);
});

// ACTUALIZAR UN PRODUCTO
$app->post("/update-producto/:id", function($id) use($app, $db){
    $json = $app->request->post('json');
    $data = json_decode($json, true);

    $sql = "UPDATE productos SET ".
            "nombre = '{$data["nombre"]}',".
            "description = '{$data["description"]}',";

    if(isset($data['imagen'])) {
        $sql .= "imagen = '{$data["imagen"]}',";
    }

    $sql .= "precio = '{$data["precio"]}' WHERE id = {$id}";

    $query = $db->query($sql);

    $result = array(
        'status' => 'error',
        'code' => 404,
        'message' => "El producto NO se ha actualizado"
    );
    if($query) {
        $result = array(
            'status' => 'success',
            'code' => 200,
            'message' => 'El producto se ha actualizado correctamente'
        );
    }
    
    echo json_encode($result);
});

// SUBIR UNA IMAGEN A UN PRODUCTO
$app->post("/upload-file", function() use($app, $db){
    $result = array(
        'status' => 'error',
        'code' => 404,
        'message' => "El archivo no ha podido subirse"
    );

    if(isset($_FILES['uploads'])) {
        $piramideUploader = new PiramideUploader();

        $upload = $piramideUploader->upload('image',"uploads","uploads",array('image/jpeg', 'image/png', 'image/gif'));
        $file = $piramideUploader->getInfoFile();
        $file_name = $file['complete_name'];

        if(isset($upload) && $upload["uploaded"] == false) {
            $result = array(
                'status' => 'error',
                'code' => 404,
                'message' => "El archivo no ha podido subirse"
            );
        }else{
            $result = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El archivo se ha subido correctamente',
                'filename' => $file_name
            );
        }
    }
    
    echo json_encode($result);
});

// GUARDAR PRODUCTOS
$app->post('/productos', function() use($app, $db){
    $json = $app->request->post('json');
    $data = json_decode($json, true);

    if(!isset($data['nombre'])) {
        $data['nombre'] = null;
    }
    if(!isset($data['description'])) {
        $data['description'] = null;
    }
    if(!isset($data['precio'])) {
        $data['precio'] = null;
    }
    if(!isset($data['imagen'])) {
        $data['imagen'] = null;
    }

    $query = "INSERT INTO productos VALUES(NULL,".
                "'{$data['nombre']}',".
                "'{$data['description']}',".
                "'{$data['precio']}',".
                "'{$data['imagen']}'".
                ");";

    $insert = $db->query($query);

    $result = array(
        'status' => 'error',
        'code' => 404,
        'message' => 'producto NO creado correctamente'
    );

    if($insert) {
        $result = array(
            'status' => 'success',
            'code' => 200,
            'message' => 'producto creado correctamente'
        );
    };

    echo json_decode($result);
});

$app->run();