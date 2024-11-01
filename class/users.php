	<?php
	include_once('./utils.php');
	class Users{
		private $conn;
		private $email;
		private $password;
		private $firstName;
		private $lastName;
		private $id;
		private $token;

		public function userLogin($data){
		  $this->conn = conn();
		  
		  //Получение данных из json body
		  $this->email = $data['email'];
		  $this->password = $data['password'];
		  
		  //Переменная с условием валидации почты и пароля
		  $validation = (filter_var($this->email, FILTER_VALIDATE_EMAIL) && strlen($this->password) > 8 && preg_match("#[0-9]+#",$this->password) && preg_match("#[A-Z]+#",$this->password) && preg_match("#[a-z]+#",$this->password));
		  
		  //Проверка на наличие всех заполненных данных
		  if(!isset($this->email,$this->password)){
			//Сборка массива из незаполненных полей
			$responce = [];
			if(!isset($this->email)){
				$responce += ["email"=>['field empty']];
			}
			if(!isset($this->password)){
				$responce += ["password"=>['field empty']];
			}
			//Возвращение сообщения
			echo jsonMessage(422,['success'=>false,'message'=>$responce]);
			return;
		  }
		  
		  if(!$validation){
			//Собираем массив из невалидных полей
			$responce = [];
			if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)){
			  $responce += ["email"=>'Invalid email'];
			}
			if(strlen($this->password) <= '8') {
				$responce += ["password"=>'Your Password Must Contain At Least 8 Characters'];
			}
			elseif(!preg_match("#[0-9]+#",$this->password)) {
				$responce += ["password"=>'Your Password Must Contain At Least 1 Number'];
			}
			elseif(!preg_match("#[A-Z]+#",$this->password)) {
				$responce += ["password"=>'Your Password Must Contain At Least 1 Capital Letter'];
			}
			elseif(!preg_match("#[a-z]+#",$this->password)) {
				$responce += ["password"=>'Your Password Must Contain At Least 1 Lowercase Letter'];
			}
			//Возвращение сообщения
			echo jsonMessage(422,['success'=>false,'message'=>$responce]);
			return;
		  }

		  //Проверяем существование пользователя
		  $result = mysqli_query($this->conn,"SELECT * FROM users WHERE email = '$this->email' AND password = '$this->password'");
		  $result_mass = mysqli_fetch_assoc($result);
		  if($result_mass == Null){
			echo jsonMessage(401,['success'=>false,'message'=>"Login failed"]);
			return;
		  }
		  
		  //Если авторизация удачная, то добавляем токен в таблицу сессий.
		  $this->id = $result_mass['id'];
		  $this->token = md5($this->id.$this->password);
		  
		  //Проверка на существование сессии
		  $result = mysqli_query($this->conn,"SELECT * FROM sessions WHERE token = '$this->token'");
		  $result_mass = mysqli_fetch_assoc($result);
		  if($result_mass != Null){
			$dateOpen = $result_mass['date_open'];
			echo jsonMessage(401,['success'=>false,'message'=>"Session already exists, created by $dateOpen"]);
			return;
		  }
		  
		  //Создание сессии и возвращение сообщения
		  mysqli_query($this->conn,"INSERT INTO sessions(user_id,token) VALUES ('$this->id','$this->token')");
		  echo jsonMessage(200,['success'=>true,'message'=>"Success",'token'=>"$this->token"]);
		}
		public function userRegister($data){
		  $this->conn = conn();
		  $this->email = $data['email'];
		  $this->password = $data['password'];
		  $this->firstName = $data['firstName'];
		  $this->lastName = $data['lastName'];

		  $validation = (filter_var($this->email, FILTER_VALIDATE_EMAIL) && strlen($this->password) > 8 && preg_match("#[0-9]+#",$this->password) && preg_match("#[A-Z]+#",$this->password) && preg_match("#[a-z]+#",$this->password));
		  if(!isset($this->email,$this->password,$this->firstName,$this->lastName)){
			$responce = [];
			if(!isset($this->email)){
				$responce += ["email"=>['Field empty']];
			}
			if(!isset($this->password)){
				$responce += ["password"=>['Field empty']];
			}
			if(!isset($this->firstName)){
				$responce += ["firstName"=>['field empty']];
			}
			if(!isset($this->lastName)){
				$responce += ["lastName"=>['field empty']];
			}
			echo jsonMessage(422,['success'=>false,'message'=>$responce]);
			return;
		  }
		  if(!$validation){
			$responce = [];
			if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)){
			  $responce += ["email"=>'Invalid email'];
			}
			if(strlen($this->password) <= '8') {
				$responce += ["password"=>'Your Password Must Contain At Least 8 Characters'];
			}
			elseif(!preg_match("#[0-9]+#",$this->password)) {
				$responce += ["password"=>'Your Password Must Contain At Least 1 Number'];
			}
			elseif(!preg_match("#[A-Z]+#",$this->password)) {
				$responce += ["password"=>'Your Password Must Contain At Least 1 Capital Letter'];
			}
			elseif(!preg_match("#[a-z]+#",$this->password)) {
				$responce += ["password"=>'Your Password Must Contain At Least 1 Lowercase Letter'];
			}
			echo jsonMessage(422,['success'=>false,'message'=>$responce]);
			return;
		  }
	
		  $result = mysqli_query($this->conn,"SELECT * FROM users WHERE email = '$this->email' AND password = '$this->password'");
		  $result_mass = mysqli_fetch_assoc($result);
		  if($result_mass != Null){
			echo jsonMessage(401,['success'=>false,'message'=>'Already registred']);
			return;
		  }
		  mysqli_query($this->conn,"INSERT INTO users(email,password,first_name,last_name) VALUES('$this->email', '$this->password','$this->firstName','$this->lastName')");
		  $this->token = md5($this->email.$this->password);
		  echo jsonMessage(200,['success'=>true,'message'=>"Success",'token'=>"$this->token"]);
		}
		public function userLogout($token){
		  $this->conn = conn();
		  mysqli_query($this->conn,"DELETE FROM sessions WHERE token = '$token'");
		  echo jsonMessage(200,['success'=>true,'message'=>'Success']);
		}
	}
	?>