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
    
    public function archiveSong($song_id) {
        $errmsg = "";
        $code = 0;
    
        try {
            // Check if the song ID exists and is not already archived
            $stringSql = "SELECT song_id FROM songs WHERE song_id = ? AND isdeleted = 0";
            $stringSql = $this->pdo->prepare($stringSql);
            $stringSql->execute([$song_id]);
    
            if ($stringSql->rowCount() === 0) {
                throw new \Exception("Song not found.");
            }
    
            // Archive the song by setting isdeleted to 1
            $stringSql = "UPDATE songs SET isdeleted = 1 WHERE song_id = ?";
            $stringSql = $this->pdo->prepare($stringSql);
            $stringSql->execute([$song_id]);
    
            $code = 200;
            $data = "Song archived successfully!";
            return array("data" => $data, "code" => $code);
    
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 400;
        } catch (\Exception $e) {
            $errmsg = $e->getMessage();
            $code = 400;
        }
    
        return array("errmsg" => $errmsg, "code" => $code);
    }

    public function patchSong($body, $song_id) {
        $errmsg = "";
        $code = 0;
    
        try {
            // Check if the song_id exists
            $checkSql = "SELECT song_id FROM songs WHERE song_id = ? AND isdeleted = 0";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->execute([$song_id]);
    
            if ($checkStmt->rowCount() === 0) {
                throw new \Exception("Song not found or has been deleted.");
            }
    
            //allowed fields to patch
            $allowedFields = ['title', 'artist', 'lyrics', 'chords', 'duration'];
    
            
            $fields = [];
            $values = [];
            foreach ($allowedFields as $field) {
                if (isset($body->$field)) {
                    $fields[] = "$field = ?";
                    $values[] = $body->$field;
                }
            }
    
            // Check if there are any fields to update
            if (empty($fields)) {
                throw new \Exception("No valid fields provided to update.");
            }
    
            // Add the song_id to the values for the WHERE ...
            $values[] = $song_id;
    
            $sqlString = "UPDATE songs SET " . implode(", ", $fields) . " WHERE song_id = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute($values);
    
            $code = 200;
            $data = "Song updated successfully!";
            return array("data" => $data, "code" => $code);
    
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 400;
        } catch (\Exception $e) {
            $errmsg = $e->getMessage();
            $code = 400;
        }
    
        return array("errmsg" => $errmsg, "code" => $code);
    }
    

}
?>