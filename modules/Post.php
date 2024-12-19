<?php

include_once "Common.php";

class Post extends CommonMethods {

    protected $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

     //add song recrods into the db
    public function uploadSong($body, $file) {
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
                        $body['chord_lyrics'], 
                        str_replace(__DIR__, '', $uploadFilePath), // Save to the uploads dir 
                        $body['duration'],
                        $body['isdeleted']
                    ];
    
                    // SQL query to insert the song
                    $sqlString = "INSERT INTO songs (title, artist, chord_lyrics, mp3_path, duration, isdeleted) 
                                  VALUES (?, ?, ?, ?, ?, ?)";
                    $sql = $this->pdo->prepare($sqlString);
                    $sql->execute($values);
    
                    $code = 200;
                    $data = "Song uploaded!";

                    $this->logger("Alira", "POST", "Uploaded a file song");

                    return array("data" => $data, "code" => $code);
                } else {
                    $this->logger("Alira", "POST", "Uploaded a file song");
                    throw new \Exception("Failed to upload mp3 file.");
                }
            } else {
                throw new \Exception("No file uploaded.");
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

    //create playlist
    public function createPlaylist($body) {
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

                $this->logger("Alira", "POST", "Successfully created a playlist");

                return array ("data"=>$data, "code"=>$code);

            }catch(\PDOException $e){
                $errmsg = $e->getMessage();
                $code = 400;
            }
                $this->logger("Alira", "POST", "Failed to create a playlist");
                return array("errmsg" => $errmsg, "code:"=>$code);
    }

    //add song to playlist
    public function addSongToPlaylist($body) {
        $errmsg = "";
        $code = 0;
    
        session_start();
    
        if (!isset($_SESSION['username'])) {
            return array("errmsg" => "You must log in to add a song to a playlist", "code" => 401); // Error if not logged in
        }
    
        $username = $_SESSION['username'];
    
        $sqlString = "SELECT user_id FROM accounts WHERE username = ?";
        $sql = $this->pdo->prepare($sqlString);
        $sql->execute([$username]);
    
        if ($sql->rowCount() > 0) {
            $user = $sql->fetch();
            $user_id = $user['user_id'];
        } else {
            return array("errmsg" => "User not found", "code" => 404);
        }
    
        try {
            // check if playlist exist in the user account
            $sqlString = "SELECT playlist_id FROM userplaylist WHERE playlist_name = ? AND user_id = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute([$body['playlist_name'], $user_id]);
    
            if ($sql->rowCount() > 0) {
                $playlist = $sql->fetch();
                $playlist_id = $playlist['playlist_id'];
            } else {
                return array("errmsg" => "Playlist not found", "code" => 404);
            }
    
            // check if song exist
            $sqlString = "SELECT song_id FROM songs WHERE title = ? AND artist = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute([$body['title'], $body['artist']]);
    
            if ($sql->rowCount() > 0) {
                $song = $sql->fetch();
                $song_id = $song['song_id'];
            } else {
                return array("errmsg" => "Song not found", "code" => 404);
            }
    
            // Check if the song is already in the playlist
            $sqlString = "SELECT * FROM song_playlist WHERE playlist_id = ? AND song_id = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute([$playlist_id, $song_id]);
    
            if ($sql->rowCount() > 0) {
                return array("errmsg" => "Song already exists in the playlist", "code" => 409); // Conflict
            }
    
            // Add song to the playlist
            $sqlString = "INSERT INTO song_playlist (playlist_id, song_id) VALUES (?, ?)";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute([$playlist_id, $song_id]);
    
            $code = 200;
            $data = "Song added to playlist successfully.";

            $this->logger("Alira", "POST", "Successfully added a song to playlist");
    
            return array("data" => $data, "code" => $code);
    
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 400;

            $this->logger("Alira", "POST", "Failed to add a song to playlist");
            return array("errmsg" => $errmsg, "code" => $code);
        }
    }
    
}
?>