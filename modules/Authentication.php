<?php

class Authentication {

    protected $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function isAuthorized(){
        //compare request token to db token
        $headers = array_change_key_case(getallheaders(),CASE_LOWER);
        return $this->getToken() === $headers['authorization'];
    }

    private function getToken(){
        $headers = array_change_key_case(getallheaders(),CASE_LOWER);

        $sqlString = "SELECT token FROM accounts WHERE username=?";
        try{
            $stmt = $this->pdo->prepare($sqlString);
            $stmt->execute([$headers['x-auth-user']]);
            $result = $stmt->fetchAll()[0];
            return $result['token'];
        }
        catch(Exception $e){
            echo $e->getMessage();
        }
        return "";
    }


    ##JWT Generator
    private function generateHeader() {
        //required fields alg(orithm) and type
        $header = [
            "alg"=>"HS256",
            "typ" => "JWT",
            "app" => "pitch-chordia",
            "dev" => "Cristina Alipio,"
        ];
        return base64_encode(json_encode($header));
    }

    private function generatePayLoad($account_id, $username) {
        //modifieable
        //what's in here will be hash
        $payload = [
            "account_id" => $account_id,
            "username" => $username,
            "by" => "Cristina Alipio",
            "email" => "202311208@gordoncollege.edu.ph",
            "date" => date_create(),
            "expiration" => date("Y-m_d H:i:s")
        ];
        return base64_encode(json_encode($payload));
    }

    //signature
    private function generateToken($account_id, $username){
        $header = $this->generateHeader();
        $payload = $this->generatePayload($account_id, $username);
        $signature = hash_hmac("sha256", "$header.$payload", TOKEN_KEY);
        return "$header.$payload." . base64_encode($signature);
    }

    ##Admin Authentication
    //compare hash value that generate (input password) to the existing hash value
    private function isSamePassword ($inputPassword, $existingHash) {
        $hash = crypt($inputPassword, $existingHash);
        return $hash === $existingHash;
    }

    private function encryptPassword ($password){
        $hashFormat = "$2y$10$"; //blowfish
        $saltLength = 22; //to have a different variation of hash value
        $salt = $this->generateSalt($saltLength);
        return crypt($password, $hashFormat . $salt);
    }

    private function generateSalt($length) {
        //random character generator
        //hash
        $urs = md5 (uniqid(mt_rand(), true)); //generate random char or hash
        $b64String = base64_encode($urs); //generate the hash into base64
        $mb64String = str_replace("+", ".", $b64String); //the generate hash will replace the + sign into dot
        return substr($mb64String, 0, $length);
    }


    public function saveToken($token, $username){
        
        $errmsg = "";
        $code = 0;
        
        try{
            $sqlString = "UPDATE accounts SET token=? WHERE username = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute( [$token, $username] );

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

    //validate the admin - login
    public function login($body){
        $username = $body->username;
        $password = $body->password;

        $code = 0;
        $payload = "";
        $remarks = "";
        $message = "";

        try {
            //it retrieve the record from the database 
            $sqlString = "SELECT admin_id, username, password, token FROM accounts WHERE username = ?";
            $stmt = $this->pdo->prepare($sqlString); //statement to prevent sql injection
            $stmt->execute([$username]);

            //check if the record is existing or not
            if($stmt->rowCount() > 0){
                $result = $stmt->fetchAll()[0]; //fetch the result of record of the admin
                if ($this->isSamePassword($password, $result['password'])); {
                    //return user info
                    $code = 200;
                    $remarks = "Success";
                    $message = "Logged in successfully";

                    //generate token function, 
                    //this is where (login function) the token is happening
                    //it needs to define first before it throws in the payload.
                    $token = $this->generateToken($result['admin_id'], $result['username']);
                    $token_arr = explode('.', $token);
                    $this->saveToken($token_arr[2], $result['username']);
                    $payload = array("admin_id" => $result['admin_id'], "username" => $result['username'], "token"=>$token_arr[2]); //throw the data to the user or frontend
                }
            }
            else {
                $code = 401;
                $payload = "null"; //throw the data to the user or frontend 
                $remarks = "Login failed";
                $message = "Username does not exist";
            }
        } 
        catch (\PDOException $e) {
            $errmsg = "The user is not found";
            $code = 400;
        }
        return array("code"=> $code, "payload"=> $payload, "remarks" => $remarks, "message"=> $message);
    }

    //register - add user records into the db - creating new user
    public function addAccount($body){
        $values = [];
        $errmsg = "";
        $code = 0;

        $body->password = $this->encryptPassword($body->password);

        foreach($body as $value){
            array_push($values, $value);
        }
        
        try{
            $sqlString = "INSERT INTO accounts (admin_id, firstname, lastname, username, password) VALUES (?,?,?,?,?)";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute($values);

            $code = 200;
            $data = null;

            return array("data"=>$data, "code"=>$code);
        }
        catch(\PDOException $e){
            $message = $e->getMessage();
            $remarks = "failed";
            $code = 400;
        }

        
        return array("errmsg"=>$errmsg, "code"=>$code);
    }
}

?>