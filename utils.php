<?php
    if(!function_exists('conn')){
        function conn(){
            $connect = mysqli_connect('127.0.0.1','root','','restPHP');
            return $connect;
        }
    }
    if(!function_exists('jsonMessage')){
        function jsonMessage($status, $params) {
			http_response_code($status);
            $response = array(
                'body' => $params
            );
            return json_encode($response,1);
        }
    }
    if(!function_exists('checkToken')){
        function checkToken($token) {
            $conn = conn();
            $sql = "SELECT user_id FROM sessions WHERE token = '$token'";
            $result = mysqli_query($conn,$sql);
            $result_mass = mysqli_fetch_assoc($result);
            if($result_mass == Null){
                return false;
            }
            return $result_mass['user_id'];
        }
    }
?>
