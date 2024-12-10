<?php

class Patch {
    protected $pdo;
    
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    //update some parts to user info
    public function patchUser($body, $user_id) {
        $values = [];
        $errmsg = "";
        $code = 0;

        foreach ($body as $value){
            array_push($values, $value);
        }

        array_push($values, $user_id);

        try{
            $sqlString = "UPDATE users SET firstname = ?, lastname = ?, email = ? WHERE user_id = ?";
            $sql = $this->pdo->prepare ($sqlString);
            $sql->execute($values);

            $code = 200;
            $data = null;

            return array ("data"=>$data, "code"=>$code);

        }catch(\PDOException $e){
            $errmsg = $e->getMessage();
            $code = 400;
        }
            return array("errmsg" => $errmsg, "code:"=>$code);
    }

    public function archiveUser($user_id){
        
        $errmsg = "";
        $code = 0;
        
        try{
            $sqlString = "UPDATE users SET isdeleted=1 WHERE user_id = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute([$user_id]);

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

    //still have an error, it needs to pass the values array that comes from in the post function
    public function patchSong($body, $user_id) {
        $values = [];
        $errmsg = "";
        $code = 0;

        foreach ($body as $value){
            array_push($values, $value);
        }

        array_push($values, $user_id);

        try{
            $sqlString = "UPDATE songs SET lyrics = ?, chords = ? WHERE user_id = ?";
            $sql = $this->pdo->prepare ($sqlString);
            $sql->execute($values);

            $code = 200;
            $data = null;

            return array ("data"=>$data, "code"=>$code);

        }catch(\PDOException $e){
            $errmsg = $e->getMessage();
            $code = 400;
        }
            return array("errmsg" => $errmsg, "code:"=>$code);
    }
    
    

}
?>