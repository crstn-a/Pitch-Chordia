<?php

    class CommonMethods {

        protected function logger($user, $method, $action) {
            //datetime, user, method, message -> text file.log
            $logFilename = date("Y-m-d"). ".log";
            $datetime = date("Y-m-d H:i:s");
            $logMessage = "$datetime, $method, $user, $action" . PHP_EOL;
            file_put_contents("./logs/$logFilename", $logMessage, FILE_APPEND | LOCK_EX);
        }

        private function generateInsertString($tablename, $body){
            $keys = array_keys($body);
            $fields = implode(",", $keys);
            $parameter_array = [];

            for($i = 0; $i < count($keys); $i++){
                $parameter_array[$i] = "?";
            }

            $parameters = implode(',', $parameter_array);
            $sql = "INSERT INTO $tablename($fields) VALUES ($parameters)";
            return $sql;
        }

        //for get data table name refactor
        protected function getData($tableName, $condition, \PDO $pdo){
            $data = array();
            $errmsg = "";
            $code = 0;

            $sqlString = "SELECT * FROM $tableName WHERE $condition";
            
            try{
                if($result = $pdo->query($sqlString)->fetchAll()){
                    foreach($result as $record){
                        array_push($data, $record);
                    }
                    $result = null;
                    $code = 200;
                    return array("code"=>$code, "data"=>$data); 
                }
                else{
                    $errmsg = "No data found";
                    $code = 404;
                }
            }
            catch(\PDOException $e){
                $errmsg = $e->getMessage();
                $code = 403;
            }
            return array("code"=>$code, "errmsg"=>$errmsg);
        }

        //post data from the tablename
        public function postData($tableName, $body, \PDO $pdo){
            $values = [];
            $errmsg = "";
            $code = 0;
    
    
            foreach($body as $value){
                array_push($values, $value);
            }
            
            try{
                $sqlString = $this->generateInsertString($tableName, $body);
                $sql = $pdo->prepare($sqlString);
                $sql->execute($values);
    
                $code = 200;
                $data = null;
    
                return array("data"=>$data, "code"=>$code);
            }
            catch(\PDOException $e){
                $errmsg = $e->getMessage();
                $code = 400;
            }
    
            
            return array("errmsg"=>$errmsg, "code"=>$code);
        }  

        public function sendResponse($data, $message, $remarks, $statusCode){

            $status = array (
                "message"=> $message,
                "remarks"=> $remarks
            );

            http_response_code($statusCode);

            return array(
                "payload"=> $data,
                "status"=> $status,
                "prepared by" => "Team Alira",
                "date_generated" => date_create()
            );
        }

}

?>