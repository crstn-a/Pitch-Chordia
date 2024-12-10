<?php

class Post {

    protected $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    //add user info into the db
    public function postUser($body) {
        $values = [];
        $errmsg = "";
        $code = 0;

        foreach ($body as $value){
            array_push($values, $value);
        }

        try{
            $sqlString = "INSERT INTO users (firstname, lastname, email, isdeleted) VALUES (?, ?, ?, ?)";
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

     //add song recrods into the db
     public function postSong($body, $file) {
        $values = [];
        $errmsg = "";
        $code = 0;
    
        try {
            // checks if the dir of uploads exist
            $uploadDir = __DIR__ . '/uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
    
            // process of uploading a file 
            if (isset($file['file']) && $file['file']['error'] === UPLOAD_ERR_OK) {
                $fileName = basename($file['file']['name']);
                $uploadFilePath = $uploadDir . $fileName;
    
                if (move_uploaded_file($file['file']['tmp_name'], $uploadFilePath)) {
                    // values for insertion
                    $values = [ 
                        $body['title'], 
                        $body['artist'], 
                        $body['lyrics'], 
                        $body['chords'],
                        str_replace(__DIR__, '', $uploadFilePath), // Save to the uploads dir 
                        $body['duration']
                    ];
    
                    // SQL query to insert the song
                    $sqlString = "INSERT INTO songs (title, artist, lyrics, chords, mp3_path, duration, isdeleted) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $sql = $this->pdo->prepare($sqlString);
                    $sql->execute($values);
    
                    $code = 200;
                    $data = "Song uploaded and data saved successfully!";
                    return array("data" => $data, "code" => $code);
                } else {
                    throw new \Exception("Failed to move uploaded file.");
                }
            } else {
                throw new \Exception("No file uploaded or an error occurred during the upload.");
            }
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