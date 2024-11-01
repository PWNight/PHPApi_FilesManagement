<?php
  include('./utils.php');
  class Files{
    private $fullName;
    private $name;
    private $email;
    private $userType;
    private $userId;
    private $ext;
    private $conn;

    public function getShared($userId){
      $this->conn = conn();
      //Получение доступных пользователю файлов (author)
      $this->userId = $userId;
      $result = mysqli_query($this->conn,"SELECT files.id, files.name, files.url FROM files INNER JOIN files_users ON files.id = files_users.file_id WHERE files_users.user_id = $this->userId AND files_users.type = 'co-author'");
      $array_files = mysqli_fetch_all($result);
      $responce = [];
      foreach($array_files as $value){
        $responce[] = ['file_id'=>$value[0],'name'=>$value[1],'url'=>$value[2]];
      }
      echo jsonMessage(200,$responce);
    }
    public function getDisk($userId){
      $this->conn = conn();
      //Получение доступных пользователю файлов (author)
      $this->userId = $userId;
      $result = mysqli_query($this->conn,"SELECT files.id, files.name, files.url FROM files INNER JOIN files_users ON files.id = files_users.file_id WHERE files_users.user_id = $this->userId AND files_users.type = 'author'");
      $array_files = mysqli_fetch_all($result);
      $responce = [];
      foreach($array_files as $fileValue){
        $result = mysqli_query($this->conn,"SELECT CONCAT(users.first_name, users.last_name), users.email, files_users.type FROM files INNER JOIN files_users ON files.id = files_users.file_id INNER JOIN users ON files_users.user_id = users.id WHERE files.id = '$fileValue[0]'");
        $array_users = mysqli_fetch_all($result);
        $accesses = [];
        foreach($array_users as $userValue){
          $accesses[] = ['fullname'=>$userValue[0],'email'=>$userValue[1],'type'=>$userValue[2]];
        }
        $responce[] = ['file_id'=>$fileValue[0],'name'=>$fileValue[1],'url'=>$fileValue[2], 'accesses'=>$accesses];
      }
      echo jsonMessage(200,$responce);
    }
    public function getFile($userId,$fileId){
      $this->conn = conn();
      $this->userId = $userId;
      //Проверка на существование файла
      $result = mysqli_query($this->conn,"SELECT * FROM files WHERE id = '$fileId'");
      $result_mass = mysqli_fetch_assoc($result);
      if($result_mass == Null){
        echo jsonMessage(403,['success'=>false,'message'=>"No file found"]);
        return;
      }
      //Проверка на наличие доступа к файлу
      $result = mysqli_query($this->conn,"SELECT name FROM files INNER JOIN files_users ON files.id = files_users.file_id WHERE user_id = '$this->userId' AND file_id = '$fileId'");
      $result_mass = mysqli_fetch_assoc($result);
      if($result_mass == Null){
        echo jsonMessage(403,['success'=>false,'message'=>"No access to file"]);
        return;
      }
      $fileName = $result_mass['name'];
      header('Content-type: application/octet-stream');
      header('Content-Length: '.filesize("upload/$fileName"));
      header("Content-Disposition: attachment; filename='$fileName'");
      readfile("upload/$fileName");
    }
    public function deleteFile($userId,$fileId){
      $this->conn = conn();
      $this->userId = $userId;
      //Проверка на существование файла
      $result = mysqli_query($this->conn,"SELECT * FROM files WHERE id = '$fileId'");
      $result_mass = mysqli_fetch_assoc($result);
      if($result_mass == Null){
        echo jsonMessage(403,['success'=>false,'message'=>"No file found"]);
        return;
      }
      //Проверка на наличие доступа к файлу
      $result = mysqli_query($this->conn,"SELECT type FROM files_users WHERE file_id = '$fileId' AND user_id = '$this->userId'");
      $result_mass = mysqli_fetch_assoc($result);
      if($result_mass == Null || $result_mass['type'] != 'author'){
        echo jsonMessage(403,['success'=>false,'message'=>"No access to delete this file"]);
        return;
      }
      //Удаления файла из БД и папки upload
      mysqli_query($this->conn,"DELETE FROM files WHERE id = '$fileId'");
      mysqli_query($this->conn,"DELETE FROM files_users WHERE file_id = '$fileId'");
      //Возврат ответа
      echo jsonMessage(200,['success'=>true,'message'=>'Success']);
    }
    public function renameFile($userId,$fileId,$data){
      $this->conn = conn();
      $this->userId = $userId;
      //Проверка поля name
      if(!isset($data['name']) || $data['name'] == ''){
        echo jsonMessage(422,['success'=>false,'message'=>['name'=>'Field name can not be empty']]);
        return;
      }
     //Проверка на существование файла
      $result = mysqli_query($this->conn,"SELECT * FROM files WHERE id = '$fileId'");
      $result_mass = mysqli_fetch_assoc($result);
      if($result_mass == Null){
        echo jsonMessage(403,['success'=>false,'message'=>"No file found"]);
        return;
      }
      //Проверка на уникальность названия
      $this->name = $data['name'];
      $fileName = $result_mass['name'];
      $this->fullName = explode('.',$fileName);

      if($this->name === $this->fullName[0]){
        echo jsonMessage(403,['success'=>false,'message'=>"Enter unique name"]);
        return;
      }
      $this->ext = end($this->fullName);
      $this->name = "$this->name.$this->ext";

      $isExists = false;
      $dublicatsCount = 0;

      $uploadDir = scandir('upload');
      foreach($uploadDir as $value){
        if(str_contains($value,$this->name)){
          $this->fullName = explode('.',$value);
          $this->ext = end($this->fullName);
          $dublicatsCount++;
          $isExists = true;
        }
      }

      if($isExists){
        $dublicatsCount++;
        $this->name = $data['name'];
        $this->name = "$this->name ($dublicatsCount).$this->ext";
      }
      //Проверка на наличие доступа к файлу
      $result = mysqli_query($this->conn,"SELECT type FROM files_users WHERE file_id = '$fileId' AND user_id = '$this->userId'");
      $result_mass = mysqli_fetch_assoc($result);
      if($result_mass == Null || $result_mass['type'] != 'author'){
        echo jsonMessage(403,['success'=>false,'message'=>"You not author of this file"]);
        return;
      }
      //Изменение названия в БД и в папке upload
      $result = mysqli_query($this->conn,"SELECT name FROM files WHERE id = '$fileId'");
      $result_mass = mysqli_fetch_assoc($result);
      //Переименование файла в хранилище
      if(!rename("./upload/$fileName","./upload/$this->name")){
        echo jsonMessage(418,['success'=>false,'message'=>"File rename error."]);
        return;
      }
      mysqli_query($this->conn,"UPDATE files SET name = '$this->name' WHERE id = '$fileId'");
      //Возврат ответа
      echo jsonMessage(200,['success'=>true,'message'=>'Renamed']);
    }
    public function uploadFile($userId,$FILE){
      $this->conn = conn();
      $success = true;
	  
      $arrayAlownedTypes = ['doc','pdf', 'docx', 'zip', 'jpeg', 'jpg', 'png'];
      $this->userId = $userId;
      $responce = [];
      $errors = [];
    
      $direct = 'upload';
      $fullFileName = $FILE['name'];
      $fileName = explode('.',$fullFileName);
      $fileName = $fileName[0];
      $fileTempName = $FILE["tmp_name"];
      $fileId = uniqid(true);
      $fileId = substr($fileId,4); 
      $isExists = false;
      $dublicatsCount = 0;

      $uploadDir = scandir('upload');
      foreach($uploadDir as $value){
        if(str_contains($value,$fullFileName)){
          $isExists = true;
          $dublicatsCount++;
        }
      }
      $fileType = explode('.',$fullFileName);
      $fileType = end($fileType);
      $filePath = "$direct/$fileName.$fileType";

      if($isExists){
        $dublicatsCount++;
        $fileName = "$fileName ($dublicatsCount).$fileType";
      }else{
        $fileName = "$fileName.$fileType";
      }

      if($FILE['size'] > 2097152){
          $success = false;
          $errors += ['success'=>false,'message'=>["$fileName" => "File size more then 2 mb"]];
      }else{
          $typeCheck = false;
  
          foreach($arrayAlownedTypes as $alownedType){
              if($fileType == $alownedType){
                  $typeCheck = true;
              }
          }
          if(!$typeCheck){
              $success = false;
              $errors += ['success'=>false,'message'=>["$fileName" => "$fileType is not alowned. Alowned types: doc, pdf, docx, zip, jpeg, jpg, png"]];
          }else{
            mysqli_query($this->conn,"INSERT INTO files(id,name,url) VALUES('$fileId','$fileName','http://localhost/files/$fileId')");
            mysqli_query($this->conn,"INSERT INTO files_users(file_id,user_id,type) VALUES('$fileId',$this->userId,'author')");
            if(!move_uploaded_file($fileTempName, $filePath)){
              $success = false;
              $errors += ['success'=>false,'message'=>["$fileName" => "File upload error"]];
            }else{
              $responce += ['success'=>true,'message'=>'Success','name'=>$fileName,'url'=>"http://localhost/files/$fileId",'file_id'=>$fileId];
            }
          }
      }
      if(!$success){
        echo jsonMessage(401,$errors);
        return;
      }
      echo jsonMessage(200,$responce);
    }
    public function addAccess($userId,$fileId,$data){
      $this->conn = conn();
	  
      if($data['email'] == ''){
        echo jsonMessage(422,['success'=>false,'message'=>['email'=>'Field email can not be empty']]);
        return;
      }
      $this->email = $data['email'];
      $this->userId = $userId;
      //Проверка существования указанного файла
      $result = mysqli_query($this->conn,"SELECT * FROM files WHERE id = '$fileId'");
      $result_mass = mysqli_fetch_assoc($result);
      if($result_mass == Null){
        echo jsonMessage(401,['success'=>false,'message'=>"File not found."]);
        return;
      }
      //Проверка на наличие доступа к файлу
      $result = mysqli_query($this->conn,"SELECT type FROM files_users WHERE file_id = '$fileId' AND user_id = '$this->userId'");
      if(!$result){
        echo jsonMessage(418,['success'=>false,'message'=>"SQL Request error."]);
        return;
      }
      $result_mass = mysqli_fetch_assoc($result);
      if($result_mass == Null || $result_mass['type'] != 'author'){
        echo jsonMessage(403,['success'=>false,'message'=>"You not author of this file"]);
        return;
      }
      //Проверка существования указанного пользователя и получение его почты и ФИ
      $result = mysqli_query($this->conn,"SELECT * FROM users WHERE email = '$this->email'");
      if(!$result){
        echo jsonMessage(418,['success'=>false,'message'=>"SQL Request error."]);
        return;
      }
      $arrayCoAuthor = mysqli_fetch_assoc($result);
      if($arrayCoAuthor == Null){
        echo jsonMessage(401,['success'=>false,'message'=>"User not found"]);
        return;
      }
      $this->email = $arrayCoAuthor['email'];
      $this->name = $arrayCoAuthor['first_name'].$arrayCoAuthor['last_name'];
      $this->userId = $arrayCoAuthor['id'];
      //Проверка на наличие co-author статуса у добавляемого
      $result = mysqli_query($this->conn,"SELECT type FROM files_users WHERE user_id = '$this->userId' AND file_id = '$fileId'");
      if(!$result){
        echo jsonMessage(418,['success'=>false,'message'=>"SQL Request error."]);
        return;
      }
      $result_mass = mysqli_fetch_assoc($result);
      if($result_mass != Null){
        if($result_mass['type'] == 'co-author'){
          echo jsonMessage(401,['success'=>false,'message'=>"User $this->email already co-author of file $fileId"]);
          return;
        }elseif($result_mass['type'] == 'author'){
          echo jsonMessage(401,['success'=>false,'message'=>"You trying to add youself? https://i.pinimg.com/originals/f0/26/32/f0263258e8be4229af123d8e603e2104.jpg"]);
          return;
        }
      }
      //Внесение данных в таблицу files_users
      $result = mysqli_query($this->conn,"INSERT INTO files_users(file_id, user_id,type) VALUES('$fileId',$this->userId,'co-author')");
      if(!$result){
        echo jsonMessage(418,['success'=>false,'message'=>"SQL Request error."]);
        return;
      }
      //Генерация ответа со списком всех пользователей, имеющих доступ к файлу
      $result = mysqli_query($this->conn,"SELECT CONCAT(users.first_name, users.last_name), users.email, files_users.type FROM files INNER JOIN files_users ON files.id = files_users.file_id INNER JOIN users ON files_users.user_id = users.id WHERE files.id = '$fileId'");
      if(!$result){
        echo jsonMessage(418,['success'=>false,'message'=>"SQL Request error."]);
        return;
      }
      $array_users = mysqli_fetch_all($result);
      $accesses = [];
      foreach($array_users as $value){
        $accesses[] = ['fullname'=>$value[0],'email'=>$value[1],'type'=>$value[2]];
      }
      echo jsonMessage(200,$accesses); 
    }
    public function removeAccess($userId,$fileId,$data){
      $this->conn = conn();
      //Проверка поля email
      if(!isset($data['email']) || $data['email'] == ''){
        echo jsonMessage(422,['success'=>false,'message'=>['email'=>'Field email can not be empty']]);
        return;
      }
      $this->email = $data['email'];
      $result = mysqli_query($this->conn,"SELECT id FROM users WHERE email = '$this->email'");
      $result_mass = mysqli_fetch_assoc($result);
      $this->userId = $result_mass['id'];
      //Проверка на попытку удалить самого себя
      if($userId == $this->userId){
        echo jsonMessage(418,['success'=>false,'message'=>"You can not remove yourself"]);
        return;
      }
      //Проверка на наличие файла
      $result = mysqli_query($this->conn,"SELECT * FROM files WHERE id = '$fileId'");
      $result_mass = mysqli_fetch_assoc($result);
      if($result_mass == Null){
        echo jsonMessage(403,['success'=>false,'message'=>'File not found']);
        return;
      }
      //Проверка на наличие доступа к файлу
      $result = mysqli_query($this->conn,"SELECT type FROM files_users WHERE file_id = '$fileId' AND user_id = '$userId'");
      $result_mass = mysqli_fetch_assoc($result);
      if($result_mass == Null || $result_mass['type'] != 'author'){
        echo jsonMessage(403,['success'=>false,'message'=>"You not author of this file"]);
        return;
      }
      //Проверка на наличие пользователя в списке доступа к файлу
      $result = mysqli_query($this->conn,"SELECT * FROM files_users WHERE file_id = '$fileId' AND user_id = '$this->userId'");
      $result_mass = mysqli_fetch_assoc($result);
      if($result_mass == Null){
        echo jsonMessage(403,['success'=>false,'message'=>"User not found in accesses list"]);
        return;
      }
      //Удаление пользователя из files_users с file_id = $fileId
      mysqli_query($this->conn,"DELETE FROM files_users WHERE user_id = '$this->userId' AND file_id = '$fileId'");
      //Генерация ответа со списком всех пользователей, имеющих доступ к файлу
      $result = mysqli_query($this->conn,"SELECT CONCAT(users.first_name, users.last_name), users.email, files_users.type FROM files INNER JOIN files_users ON files.id = files_users.file_id INNER JOIN users ON files_users.user_id = users.id WHERE files.id = '$fileId'");
      $array_users = mysqli_fetch_all($result);
      $accesses = [];
      foreach($array_users as $value){
        $accesses[] = ['fullname'=>$value[0],'email'=>$value[1],'type'=>$value[2]];
      }
      echo jsonMessage(200,$accesses);
    }
  }