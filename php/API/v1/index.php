<?php 
	header("Access-Control-Allow-Origin: *");
	header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
	header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin, Authorization");
	header('Content-Type: text/html; charset=utf-8');
	header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');

	include_once '../include/Config.php';
	include_once '../include/DbConnect.php';
	include_once '../include/DbHandler.php';
	require '../libs/vendor/autoload.php';



	$app = new \Slim\Slim();

	function echoResponse($status_code, $response) {
		$app = \Slim\Slim::getInstance();
		// Http response code
		$app->status($status_code);

		// setting response content type to json
		$app->contentType('application/json');

		echo json_encode($response);
		
	}

	function authenticate(\Slim\Route $route) {
		// Getting request headers
		$headers = apache_request_headers();
		$response = array();
		$app = \Slim\Slim::getInstance();

		// Verifying Authorization Header
		if (isset($headers['Authorization'])) {
			//$db = new DbHandler(); //utilizar para manejar autenticacion contra base de datos

			// get the api key
			$token = $headers['Authorization'];

			// validating api key
			if (!($token == API_KEY)) { //API_KEY declarada en Config.php

				// api key is not present in users table
				$response["error"] = true;
				$response["message"] = "Acceso denegado. Token inválido";
				echoResponse(401, $response);

				$app->stop(); //Detenemos la ejecución del programa al no validar

			} else {
				//procede utilizar el recurso o metodo del llamado
			}
		} else {
			// api key is missing in header
			$response['token'] = $headers['Authorization'];
			$response["error"] = true;
			$response["message"] = "Falta token de autorización";
			echoResponse(400, $response);

			$app->stop();
		}
	}

	function verifyRequiredParams($required_fields) {
		$error = false;
		$error_fields = "";
		$request_params = array();
		$request_params = $_REQUEST;
		// Handling PUT request params
		if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
			$app = \Slim\Slim::getInstance();
			parse_str($app->request()->getBody(), $request_params);
		}
		foreach ($required_fields as $field) {
			if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
				$error = true;
				$error_fields .= $field . ', ';
			}
		}

		if ($error) {
			// Required field(s) are missing or empty
			// echo error json and stop the app
			$response = array();
			$app = \Slim\Slim::getInstance();
			$response["error"] = true;
			$response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
			echoResponse(400, $response);

			$app->stop();
		}
	}


	//SA REQUESTS

	$app->post('/login', 'authenticate', function() use ($app, $db){
		verifyRequiredParams(array('username', 'password'));
		$user = $app->request->post('username');
		$pass = $app->request->post('password');
		$pass = md5($pass);
		$response = $db->login("usuarios", $user, $pass);
		echoResponse(200, $response);
	});

	$app->get('/getAllReports', 'authenticate', function() use ($db) {
		$response = $db->getAll("reportes");
		echoResponse(200, $response);
	});

	$app->post('/getPresentations', 'authenticate', function() use ($app, $db){
		verifyRequiredParams(array('id'));
		$id = $app->request->post('id');

		$response = $db->get("presentaciones", "idReporte", $id);
		echoResponse(200, $response);
	});

	$app->post('/getReport', 'authenticate', function() use ($app, $db) {
		verifyRequiredParams(array('id'));

		$id = $app->request->post('id');
		$response = $db->get("reportes", "idUsuario", $id);
		echoResponse(200, $response);
	});	

	$app->post('/getOneReport', 'authenticate', function() use ($app, $db) {
		verifyRequiredParams(array('idReporte'));

		$id = $app->request->post('idReporte');
		$response = $db->get("reportes", "idReporte", $id);
		echoResponse(200, $response);
	});	

	$app->post('/getPictures', 'authenticate', function() use ($app, $db){
		verifyRequiredParams(array('id'));

		$id = $app->request->post('id');
		$response = $db->get("pictures", "idReporte", $id);
		echoResponse(200, $response);
	});

	$app->post('/getLicense', 'authenticate', function() use ($app, $db) {
		verifyRequiredParams(array('sn', 'mac'));

		$sn = $app->request->post('sn');
		$mac = $app->request->post('mac');

		$response = $db->getLicx("codes", $sn, $mac);
		echoResponse(200, $response);
	});

	$app->get('/getUsers', 'authenticate', function() use ($db){
		$response = $db->getAll("usuarios");
		echoResponse(200, $response);
	});

	$app->post('/getDir', 'authenticate', function() use ($app, $db){
		verifyRequiredParams(array('dir'));

		$dir = $app->request->post('dir');
		$response = $db->getFiles($dir);
		
		echoResponse(200, $response);
	});

	$app->post('/deleteUser', 'authenticate', function() use ($app, $db){
		verifyRequiredParams(array('id'));

		$id = $app->request->post('id');
		$response = $db->remove("usuarios", "idUsuario", $id);
		echoResponse(200, $response);
	});

	$app->post('/deletePicture', 'authenticate', function() use ($app, $db){
		verifyRequiredParams(array('condition'));
		$condition = $app->request->post('condition');

		if($condition == "1"){
			verifyRequiredParams(array('path'));
			$path = $app->request->post('path');

			$response = $db->removeFile("pictures", $path);
			echoResponse(200, $response);
		}else if($condition == "2"){
			verifyRequiredParams(array('path', 'names'));
			$path = $app->request->post('path');
			$names = $app->request->post('names');

			$response = $db->removeFolder("pictures", $path, $names);
			echoResponse(200, $response);
		}
	});

	$app->post('/deletePre', 'authenticate', function() use ($app, $db){
		verifyRequiredParams(array('id'));
		$id = $app->request->post('id');
		$response = $db->remove("presentaciones", "idPresentacion", $id);

		echoResponse(200, $response);
	});

	$app->post('/deleteReport', 'authenticate', function() use ($app, $db){
		verifyRequiredParams(array('id'));
		$id = $app->request->post('id');
		$response = $db->remove("reportes", "idReporte", $id);

		echoResponse(200, $response);
	});

	$app->post('/addUser', 'authenticate', function() use ($app, $db){
		verifyRequiredParams(array('nombre', 'username', 'password', 'tipo', 'telefono', 'zona'));

		$params['nombre'] = $app->request->post('nombre');
		$params['username'] = $app->request->post('username');
		$params['password'] = md5($app->request->post('password'));
		$params['tipo'] = $app->request->post('tipo');
		$params['telefono'] = $app->request->post('telefono');
		$params['zona'] = $app->request->post('zona');

		$response = $db->add("usuarios", $params);

		echoResponse(200, $response);
	});

	$app->post('/editUser', 'authenticate', function() use ($app, $db){
		verifyRequiredParams(array('id', 'nombre', 'username', 'password', 'tipo', 'telefono', 'zona'));

		$id = $app->request->post('id');
		$params['nombre'] = $app->request->post('nombre');
		$params['username'] = $app->request->post('username');
		$params['password'] = md5($app->request->post('password'));
		$params['tipo'] = $app->request->post('tipo');
		$params['telefono'] = $app->request->post('telefono');
		$params['zona'] = $app->request->post('zona');

		$response = $db->edit("usuarios", "idUsuario", $id, $params);

		echoResponse(200, $response);
	});

	$app->post('/editPre', 'authenticate', function() use ($app, $db){
		verifyRequiredParams(array('id', 'tipo', 'inv_i', 'inv_f', 'ventas', 'obs'));

		$id = $app->request->post('id');
		$params['tipo'] = $app->request->post('tipo');
		$params['inv_inicial'] = $app->request->post('inv_i');
		$params['inv_final'] = $app->request->post('inv_f');
		$params['ventas'] = $app->request->post('ventas');
		$params['observaciones'] = $app->request->post('obs');

		$response = $db->edit("presentaciones", "idPresentacion", $id, $params);

		echoResponse(200, $response);
	});

	$app->post('/editReport', 'authenticate', function() use ($app, $db){
		verifyRequiredParams(array('id', 'marca'));

		$id = $app->request->post('id');
		$params['marca'] = $app->request->post('marca');

		$response = $db->edit("reportes", "idReporte", $id, $params);

		echoResponse(200, $response);
	});
    
    $app->get('/getLicx', 'authenticate', function() use ($db){
        $response = $db->getAll("codes");
		echoResponse(200, $response);
    });
    
    $app->post('/editLicx', 'authenticate', function() use ($app, $db){
        verifyRequiredParams(array('idcode'));
        $id = $app->request->post('idcode');
        $params['licx'] = $app->request->post('licx');
        
        $response = $db->edit("codes", "idcode", $id, $params);
		echoResponse(200, $response);
    });
    
	// MOBILE REQUESTS
	$app->post('/addPresentation', 'authenticate', function() use ($app, $db){
		verifyRequiredParams(array('idReporte', 'tipo'));

		$params['idReporte'] = $app->request->post('idReporte');
		$params['tipo'] = $app->request->post('tipo');

		$response = $db->add("presentaciones", $params);
	});

	$app->post('/addReport', 'authenticate', function() use ($app, $db){
		verifyRequiredParams(array('iduser', 'marca'));

		$params['idUsuario'] = $app->request->post('iduser');
		$params['marca'] = $app->request->post('marca');
		$params['fecha'] = date('d-m-y');

		$response = $db->add("reportes", $params);
	});

	//get pictures

	$app->post('/getInfo', 'authenticate', function() use ($app, $db){
		verifyRequiredParams(array('idReporte'));

		$id = $app->request->post('idReporte');
		
		$response = $db->get("reportes", "idReporte", $id);
		echoResponse(200, $response);
	});

	$app->post('/getLoc', 'authenticate', function() use ($app, $db){
		verifyRequiredParams(array('idReporte'));

		$id = $app->request->post('idReporte');
		
		$response = $db->get("ent_sal", "idReporte", $id);
		echoResponse(200, $response);
	});

	$app->post('/Presentations', 'authenticate', function() use ($app, $db){
		verifyRequiredParams(array('idReporte'));
		$id = $app->request->post('idReporte');

		$response = $db->get("presentaciones", "idReporte", $id);
		echoResponse(200, $response);
	});

	$app->post('/Reports', 'authenticate', function() use ($app, $db) {
		verifyRequiredParams(array('iduser'));

		$id = $app->request->post('iduser');
		$response = $db->get("reportes", "idUsuario", $id);
		echoResponse(200, $response);
	});	

	$app->post('/sendLoc', 'authenticate', function() use ($app, $db){
		verifyRequiredParams(array('ent', 'lat', 'long', 'dir', 'idReport', 'alt'));

		$params['entrada'] = $app->request->post('ent');
		$params['latitud'] = $app->request->post('lat');
		$params['longitud'] = $app->request->post('long');
		$params['altitud'] = $app->request->post('alt');
		$params['direccion'] = $app->request->post('dir');
		$params['idReporte'] = $app->request->post('idReport');

		$response = $db->add("ent_sal", $params);
	});

	$app->post('/storeImages', 'authenticate', function() use ($app, $db){
		verifyRequiredParams(array('image_data', 'image_tag', 'idReporte', 'folder'));

		$image_data = $app->request->post('image_data');
		$image_tag = $app->request->post('image_tag');
		$params['idReporte'] = $app->request->post('idReporte');
		$params['folder'] = $app->request->post('folder');
		
		$response = $db->add("pictures", $image_data, $image_tag, $params);
	});

	$app->post('/updateLoc', 'authenticate', function() use ($app, $db){
		verifyRequiredParams(array('idReport', 'date'));

		$id = $app->request->post('idReport');
		$params['salida'] = $app->request->post('date');

		$response = $db->edit("ent_sal", "idReporte", $id, $params);

		echoResponse(200, $response);
	});

	$app->post('/updatePresentation', 'authenticate', function() use ($app, $db){
		verifyRequiredParams(array('idPresentacion', 'inv_i', 'inv_f', 'ventas', 'obs'));

		$id = $app->request->post('idPresentacion');
		$params['inv_inicial'] = $app->request->post('inv_i');
		$params['inv_final'] = $app->request->post('inv_f');
		$params['ventas'] = $app->request->post('ventas');
		$params['observaciones'] = $app->request->post('obs');

		$response = $db->edit("presentaciones", "idPresentacion", $id, $params);

		echoResponse(200, $response);
	});

	$app->run();
?>