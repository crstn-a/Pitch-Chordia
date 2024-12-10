<?php

    class CommonMethods {


        protected function getData($tableName, $condition, \PDO $pdo){
            $sqlString = "SELECT * FROM $tableName WHERE $condition";
       
            $data = array();
            $errmsg = "";
            $code = 0;
            
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

        public function sendResponse ($data, $message, $remarks, $statusCode){

            $status = array (
                "message"=> $message,
                "remarks"=> $remarks
            );

            http_response_code($statusCode);

            return array(
                "payload"=> $data,
                "status"=> $status,
                "date_generated" => date_create()
            );
        }

    }
?>