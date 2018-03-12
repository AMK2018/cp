<?php 
	class DbHandler
	{
		private $host;
	    private $db_name;
	    private $db_username;
	    private $db_password;

	    public function __construct( $db_host, $db_name, $db_username, $db_password ){
	        $this->host = $db_host;
	        $this->db_name = $db_name;
	        $this->db_username = $db_username;
	        $this->db_password = $db_password;
	   	}

	   	public function connect(){
	   	  	try{
			    $this->dbh = new PDO( 'mysql: host=' . $this->host . ';dbname=' . $this->db_name, $this->db_username, $this->db_password );
			    $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
			 catch ( PDOException $e) {
		        $error = "Error!: " . $e->getMessage() . '<br />';
		        echo $error;
		        return FALSE;
		    }
		    return TRUE;
		}

		public function add($table, array $data){
		    try{
		        $key_string = "";
		        $value_string = "";
		        foreach( $data as $key => $value){
		            $key_string .= $key . ",";
		            $value_string .= ":" . $key . ",";
		        }
		        $key_string = substr( $key_string, 0, -1 );
		        $value_string = substr( $value_string, 0, -1 );
		        $query = "INSERT INTO " . $table . " (" . $key_string . ") VALUES (" . $value_string . ")" ;
		        $stmt = $this->dbh->prepare( $query );
		        foreach( $data as $key => &$value ){
		            $stmt->bindParam( ":".$key, $value );
		        }
		        $count = $stmt->execute();
		        $id = $this->dbh->lastInsertId();
		        if( $count > 0 ){
		            $success = TRUE;
		            $msg = "Record with id: ". $id ." has been added successfully";
		        } else {
		            $success = FALSE;
		            $msg = "Something went wrong!";
		        }
		     }
		     catch( PDOException $e ){
		         $msg = "Error!: " . $e->getMessage();
		         $success = FALSE;
		     }
		     return array(
		        'msg' => $msg,
		        'success' => $success
		     );
		}
        
        public function addPic($table, $ImageData, $ImageName, $params){
			try {
				
				$idReporte = $params['idReporte'];
		        
		        $foldername = $params['folder'];
		        
		        $ImageName = str_replace(" ","",$ImageName);
		        
		        $foldername = str_replace(" ","",$foldername."-".$idReporte);

		        
				$uploadImagePath = "../../../Pictures/$foldername/$ImageName.png";
		        
		        $ImagePath = "Pictures/$foldername/$ImageName.png";
		        
		        createPath('../../../Pictures/'.$foldername);
		        
				$ServerURL = "http://control-personal.amicus-marketing.com/$ImagePath";

				$InsertSQL = "INSERT INTO ".$table." (idReporte,ruta) values ('$idReporte','$ServerURL')";

				if(mysqli_query($con, $InsertSQL)){
		            if(file_exists($uploadImagePath)){
		                chmod($uploadImagePath,0755); 
		                unlink($uploadImagePath);
		            }
		            
					file_put_contents($uploadImagePath,base64_decode($ImageData));

		 			$msg = "FILE UPLOADED";
					$success = TRUE;
				}
					 
			    
			} catch (PDOException $e ) {
				 $msg = "Error!: " . $e->getMessage();
		         $success = FALSE;
			}

			return array(
		        'msg' => $msg,
		        'success' => $success
		     );
		}

		function createPath($path) {
	        if (is_dir($path)) return true;
	        $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
	        $return = createPath($prev_path);
	        return ($return && is_writable($prev_path)) ? mkdir($path) : false;
	    }
	    
		public function get($table, $idName, $id){    
		    try{
		        $query = "SELECT * FROM " . $table;
	            $query .= " WHERE ".$idName." = :id";
	            $stmt = $this->dbh->prepare( $query );
	            $stmt->bindParam( ':id', $id );
		        
		        $count = $stmt->execute();
		        if( $count > 0 ){
		            $success = TRUE;
		            $msg =  $stmt->fetchAll(PDO::FETCH_ASSOC);
		        } else {
		            $success = FALSE;
		        	$msg = 'No data';
		        }
		    }
		    catch( PDOException $e ){
		        $msg = "Error!: " . $e->getMessage() . "<br />";
		        $success = FALSE;
		    };

		    return array(
		        'stuff' => $msg,
		        'success' => $success
		    );
		}

		public function login($table, $username, $password){    
		    try{
		        $query = "SELECT * FROM ". $table." WHERE username = :user AND password = :pass";
	            $stmt = $this->dbh->prepare( $query );
	            $stmt->bindParam( ':user', $username );
	            $stmt->bindParam( ':pass', $password );
		        
		        $count = $stmt->execute();
		        if( $count > 0 ){
		            $success = TRUE;
		            $msg =  $stmt->fetchAll(PDO::FETCH_ASSOC);
		        } else {
		            $success = FALSE;
		        	$msg = 'No data';
		        }
		    }
		    catch( PDOException $e ){
		        $msg = "Error!: " . $e->getMessage() . "<br />";
		        $success = FALSE;
		    };

		    return array(
		        'stuff' => $msg,
		        'success' => $success
		    );
		}

		public function getAll($table){    
		    try{
		        $query = "SELECT * FROM " . $table;
		        $stmt = $this->dbh->prepare( $query );
		        $count = $stmt->execute();
		        if( $count > 0 ){
		            $success = TRUE;
		            $msg =  $stmt->fetchAll(PDO::FETCH_ASSOC);
		        } else {
		            $success = FALSE;
		        	$msg = 'No data';
		        }
		    }
		    catch( PDOException $e ){
		        $msg = "Error!: " . $e->getMessage() . "<br />";
		        $success = FALSE;
		    };

		    return array(
		        'stuff' => $msg,
		        'success' => $success
		    );
		}
		
	public function getFiles($dir){
			try{
			    $directorio = opendir($dir); //ruta actual
			    $response['stuff'] = array();
			    while ($archivo = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
			    {
			    	$stuff = array();
			        if (!is_dir($archivo))//verificamos si es o no un directorio
			        {
			            $success = TRUE;
			            $type = "file";
			            $stuff['file'] = $archivo;
			            array_push($response["stuff"], $stuff);
			        }
			        $response['type'] = $type;
			        $response['success'] = $success;
			    }
			}
		    catch( PDOException $e ){
		        $msg = "Error!: " . $e->getMessage() . "<br />";
		        $success = FALSE;
		        $response['msg'] = $msg;
		        $response['success'] = $success;
		    };

		    return $response;
		}
		
		public function getLicx($table, $serial = false, $mac = false){
			try {
				$query = "SELECT * FROM " . $table;
				if($serial && $mac){
					$query .= " WHERE serial = :sn AND mac = :mac";
					$stmt = $this->dbh->prepare($query);
					$stmt->bindParam(':sn', $serial);
					$stmt->bindParam(':mac', $mac);
				}else{
					$msg = "Error: Incomplete parameters";
				}

				$count = $stmt->execute();
				if( $count > 0 ){
					$success = TRUE;
		            $msg =  $stmt->fetchAll(PDO::FETCH_ASSOC);
				}else{
					$success = FALSE;
		        	$msg = 'No data';
				}

			} catch (PDOException $e) {
				$msg = "Error!: " . $e->getMessage() . "<br />";
		        $success = FALSE;
			}

			 return array(
		        'info' => $msg,
		        'success' => $success
		    );
		}

		public function remove($table, $idName, $id){
			try{
		        $query = "DELETE FROM " . $table . " WHERE ".$idName." = :id";
	            $stmt = $this->dbh->prepare( $query );
	            $stmt->bindParam( ':id', $id );
		        
		        $count = $stmt->execute();
		        if( $count > 0 ){
		            $success = TRUE;
		            $msg =  "1";
		        } else {
		            $success = FALSE;
		        	$msg = '0';
		        }
		    }
		    catch( PDOException $e ){
		        $msg = "Error!: " . $e->getMessage() . "<br />";
		        $success = FALSE;
		    };

		    return array(
		        'stuff' => $msg,
		        'success' => $success
		    );
		}

		public function removeFile($table, $path){
			try{
				if(unlink($path)){
					$path = str_replace("../", "",$path);
            		$ruta = "http://control-personal.amicus-marketing.com/".$path;  
			        $query = "DELETE FROM " . $table . " WHERE ruta = :ruta";
		            $stmt = $this->dbh->prepare( $query );
		            $stmt->bindParam( ':ruta', $ruta );
			        
			        $count = $stmt->execute();
			        if( $count > 0 ){
			            $success = TRUE;
			            $msg =  "success";
			        } else {
			            $success = FALSE;
			        	$msg = 'error';
			        }
			    }else{
			    	$success = FALSE;
			        $msg = 'error';
			    }
		    }
		    catch( PDOException $e ){
		        $msg = "Error!: " . $e->getMessage() . "<br />";
		        $success = FALSE;
		    };

		    return array(
		        'stuff' => $msg,
		        'success' => $success
		    );
		}

		public function removeFolder($table, $path, $names){
			try{
				
		    	$s = explode('-', $names);
		    	$id = $s[1];
		    	$query = "DELETE FROM " . $table . " WHERE idReporte = :id";
		    	$stmt = $this->dbh->prepare( $query );
		        $stmt->bindParam( ':id', $id );
		        $count = $stmt->execute();
		        if( $count > 0 ){
		             if (empty($path)) { 
        	             $success = FALSE;
        	             $msg = 'error';
        	             return;
        	         }
        	        
        	        if(is_file($path)){
        	           //@unlink($path);
        	        }else{
        	            //array_map(__FUNCTION__, glob($path.'/*'));
        	        }
        	       
        	        @rmdir($path);
        	        
		            $success = TRUE;
		            $msg =  "success";
		        } else {
		            $success = FALSE;
		        	$msg = 'error';
		        }
			    
		    }
		    catch( PDOException $e ){
		        $msg = "Error!: " . $e->getMessage() . "<br />";
		        $success = FALSE;
		    };

		    return array(
		        'stuff' => $msg,
		        'success' => $success
		    );
		}

		public function edit($table, $idName, $id, $update_array){

		    $success = '';
		    $msg = '';
		    $set_list = array();
		    $bind = array();

		    try{
		        foreach( $update_array as $column => $value ){
		            $set_list[] = $column . ' = ' . ':'. $column;
		            $bind[":" . $column] = $value;
		        }

		        $query = "UPDATE " . $table . " SET ";
		        foreach( $set_list as $set ){
		            $query .= $set . ',';
		        }

		        $query = rtrim( $query, ',' );
		        $query = $query . ' WHERE '.$idName.' = :id';
		        $stmt = $this->dbh->prepare( $query );
		 
		        foreach( $bind as $bound_name => $bound_value ){
		            $stmt->bindValue( $bound_name, $bound_value );
		        }

		        $stmt->bindParam( ':id', $id );
		        $count = $stmt->execute();

		        if( $count > 0 ){
		            $success = TRUE;
		            $msg = 'Update was good.';
		        } else {
		            $success = FALSE;
		            $msg = 'Something went wrong!';
		        }
		    }
		    catch( PDOException $e ){
		        $msg = "Error!: " . $e->getMessage() . "<br />";
		        $sucess = FALSE;
		    }

		    return array(
		        'msg' => $msg,
		        'success' => $success
		    );
		}
	}

?>