<?php

class Post {

    protected $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

     //add song recrods into the db
     public function postSong($body, $file) {
        $values = [];
        $errmsg = "";
        $code = 0;
    
        try {
            if (isset($_POST['song'])) {
                session_start();
            } if (!isset($_SESSION['user_id'])) {
                die ("You must log in first to upload a song.");
            }

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

    public function postUserPlaylist($body) {
        $values = [];
        $errmsg = "";
        $code = 0;

        session_start();

        if(!isset($_SESSION['username'])){
            die ("You must log in first to create a playlist");
        }

        $username = $_SESSION['username'];

        //fetch user id from the db using username
        $sqlString = "SELECT user_id FROM accounts WHERE username = ?";
        $sql = $this->pdo->prepare ($sqlString);
        $sql->execute([$username]);

        //check if the user id is existing in the db
        if($sql->rowCount() > 0) {
            $user = $sql->fetch();
            $user_id = $user['user_id'];
        } 
        else {
            die("User not found");
        }

        foreach ($body as $value){
            array_push($values, $value);
        }

        array_push($values, $user_id);

        try{
            $sqlString = "INSERT INTO userplaylist (playlist_name, user_id) VALUES (?, ?)";
            $sql = $this->pdo->prepare ($sqlString);
            $sql->execute($values);

            $code = 200;
            $data = "Playlist created successfully.";

            return array ("data"=>$data, "code"=>$code);

        }catch(\PDOException $e){
            $errmsg = $e->getMessage();
            $code = 400;
        }
            return array("errmsg" => $errmsg, "code:"=>$code);
    }

}

?>