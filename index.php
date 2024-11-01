<?php
    header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE');
    header('Access-Control-Allow-Origin: http://localhost:3000');
    header('Access-Control-Allow-Headers: *');
    header('Content-Type: application/json');
        
    include_once('class/users.php');
    include_once('class/files.php');
    include_once('utils.php');

    $uri=$_SERVER['REQUEST_URI'];
    $uri_explode=explode('?',$uri);
    $link = explode('/',$uri_explode[0]);
    $method=$_SERVER['REQUEST_METHOD'];
    $cmd = $link[1];
	
    if(isset($link[2])){
        $sub_cmd = $link[2];
    }
    if(isset($link[3])){
        $subsub_cmd = $link[3];
    }
    
    $user = new Users();
    $file = new Files();
	
	$headers = apache_request_headers();
    
    switch($method){
        case 'GET':
                switch($cmd){
                    case 'logout':
						if(!isset($headers['Authorization'])){
							echo jsonMessage(403,['success'=>false,'message'=>'Login failed (no header)']);
						}else{
							$authorization = explode(' ',$headers['Authorization']);
							$token = $authorization[1];
                            $userId = checkToken($token);
                            if(!$userId){
                                echo jsonMessage(403,['success'=>false,'message'=>"Login failed (token invalid, $token)"]);
                            }else{
                                $user -> userLogout($token);
                            }
                        }
                        break;
                    case 'shared':
						if(!isset($headers['Authorization'])){
							echo jsonMessage(403,['success'=>false,'message'=>'Login failed']);
						}else{
							$authorization = explode(' ',$headers['Authorization']);
							$token = $authorization[1];
                            $userId = checkToken($token);
                            if(!$userId){
                                echo jsonMessage(403,['success'=>false,'message'=>'Login failed']);
                            }else{
                                $file -> getShared($userId);
                            }
                        }
                        break;
                    case 'files':
                        if(isset($sub_cmd)){
                            switch($sub_cmd){
                                case 'disk':
									if(!isset($headers['Authorization'])){
										echo jsonMessage(403,['success'=>false,'message'=>'Login failed']);
									}else{
										$authorization = explode(' ',$headers['Authorization']);
										$token = $authorization[1];
                                        $userId = checkToken($token);
                                        if(!$userId){
                                            echo jsonMessage(403,['success'=>false,'message'=>'Login failed']);
                                        }else{
                                            $file -> getDisk($userId);
                                        }
                                    }
                                    break;
                                default:
									if(!isset($headers['Authorization'])){
										echo jsonMessage(403,['success'=>false,'message'=>'Login failed']);
									}else{
										$authorization = explode(' ',$headers['Authorization']);
										$token = $authorization[1];
                                        $userId = checkToken($token);
                                        if(!$userId){
                                            echo jsonMessage(403,['success'=>false,'message'=>'Login failed']);
                                        }else{
                                            $fileId = $sub_cmd;
                                            $file->getFile($userId,$fileId);
                                        }
                                    }
                                    break;
                            }
                        }
                        break;
					default:
						echo jsonMessage(404,['message'=>'Not found']);
						break;
                }
                break;
        case 'POST':
            switch($cmd){
                case 'authorization':
                        $postData = file_get_contents('php://input');
                        $data = json_decode($postData, true);
                        $user->userLogin($data);
                    break;
                case 'registration':
                        $postData = file_get_contents('php://input');
                        $data = json_decode($postData, true);
                        $user->userRegister($data);
                    break;
                case 'files':
                    if(isset($sub_cmd)){
						if(!isset($headers['Authorization'])){
							echo jsonMessage(403,['success'=>false,'message'=>'Login failed']);
						}else{
							$authorization = explode(' ',$headers['Authorization']);
							$token = $authorization[1];
                            $userId = checkToken($token);
                            if(!$userId){
                                echo jsonMessage(403,['success'=>false,'message'=>'Login failed']);
                            }else{
                                $fileId = $sub_cmd;
                                $postData = file_get_contents('php://input');
                                $data = json_decode($postData, true);
                                $file->addAccess($userId,$fileId,$data);
                            }
                        }
                    }else{
						if(!isset($headers['Authorization'])){
							echo jsonMessage(403,['success'=>false,'message'=>'Login failed']);
						}else{
							$authorization = explode(' ',$headers['Authorization']);
							$token = $authorization[1];
                            $userId = checkToken($token);
                            if(!$userId){
                                echo jsonMessage(403,['success'=>false,'message'=>'Login failed']);
                            }else{
                                if($_FILES != null){
                                    foreach($_FILES as $value){
                                        $file->uploadFile($userId,$value);
                                    }
                                }else{
                                    echo jsonMessage(422,['success'=>false,'message'=>['file'=>'No files found']]);
                                }
                            }
                        }
                    }
                    break;
            }
            break;
        case 'DELETE':
            switch($cmd){
                case 'files':
					if(!isset($headers['Authorization'])){
						echo jsonMessage(403,['success'=>false,'message'=>'Login failed']);
					}else{
						$authorization = explode(' ',$headers['Authorization']);
						$token = $authorization[1];
                        $userId = checkToken($token);
                        if(!$userId){
                            echo jsonMessage(403,['success'=>false,'message'=>'Login failed']);
                        }else{
                            if(isset($subsub_cmd)){
                                if($subsub_cmd == 'access'){
                                    $fileId = $sub_cmd;
                                    $postData = file_get_contents('php://input');
                                    $data = json_decode($postData, true);
                                    $file->removeAccess($userId,$fileId,$data);
                                }
                            }else{
                                $fileId = $sub_cmd;
                                $file->deleteFile($userId,$fileId);
                            }
                        }
                    }
                    break;
            }
            break;
        case 'PATCH':
            if(isset($sub_cmd)){
                if(!isset($headers['Authorization'])){
                    echo jsonMessage(403,['success'=>false,'message'=>'Login failed']);
                }else{
                    $authorization = explode(' ',$headers['Authorization']);
                    $token = $authorization[1];
                    $userId = checkToken($token);
                    if(!$userId){
                        echo jsonMessage(403,['success'=>false,'message'=>'Login failed']);
                    }else{
                        $fileId = $sub_cmd;
                        $postData = file_get_contents('php://input');
                        $data = json_decode($postData, true);
                        $file->renameFile($userId,$fileId,$data);
                    }
                }
            }
            break;
    }	