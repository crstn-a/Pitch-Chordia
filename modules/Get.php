<?php

    include_once "Common.php";

    class Get extends CommonMethods {

        protected $pdo;

        public function __construct(\PDO $pdo){
            $this->pdo = $pdo;
        }

        //retrieved user song on database
        public function getSong($song_id = null) {
            
            session_start();
        
            if (!isset($_SESSION['username'])) {
                return array("errmsg" => "You must log in to view the uploaded songs", "code" => 401); // Error if not logged in
            }
        
            $username = $_SESSION['username'];
        
            // Fetch user_id from the database using username
            $sqlString = "SELECT user_id FROM accounts WHERE username = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute([$username]);
        
            if ($sql->rowCount() > 0) {
                $user = $sql->fetch(PDO::FETCH_ASSOC);
                $user_id = $user['user_id'];
            } else {
                return array("errmsg" => "User not found", "code" => 404);
            }

            
            //get song refactored
            $condition = "isdeleted = 0";
            if($song_id != null) {
                $condition .= " AND song_id = $song_id";
            }
            $result = $this->getData("songs", $condition, $this->pdo);
            if ($result['code'] == 200) {
                $this->logger("Alira", "GET", "Successfully retrieved songs");
                return $this->sendResponse($result['data'], "Successfully retrieved records.", "Success", $result['code']);
            }
            $this->logger("Alira", "GET", "Failed to retrieved songs");
            return $this->sendResponse(null, "Failed retrieved records of Songs", "Failed", $result['code']);
        } 

        //retrieved user playlist on db
        public function getUserPlaylist($filter = null) {
            $errmsg = "";
            $code = 0;
            $playlists = [];
        
            session_start();
        
            if (!isset($_SESSION['username'])) {
                return array("errmsg" => "You must log in to view your playlists", "code" => 401); // Error if not logged in
            }
        
            $username = $_SESSION['username'];
        
            // Fetch user_id from the database using username
            $sqlString = "SELECT user_id FROM accounts WHERE username = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute([$username]);
        
            if ($sql->rowCount() > 0) {
                $user = $sql->fetch(PDO::FETCH_ASSOC);
                $user_id = $user['user_id'];
            } else {
                return array("errmsg" => "User not found", "code" => 404);
            }
        
            try {
                // Base query
                $sqlString = "
                    SELECT 
                        up.playlist_id, 
                        up.playlist_name, 
                        s.song_id, 
                        s.title AS song_title, 
                        s.artist, 
                        s.chord_lyrics, 
                        s.mp3_path, 
                        s.duration
                    FROM 
                        userplaylist up
                    LEFT JOIN 
                        song_playlist sp ON up.playlist_id = sp.playlist_id
                    LEFT JOIN 
                        songs s ON sp.song_id = s.song_id
                    WHERE 
                        up.user_id = ?";
                
                // Adjust the query based on the filter
                if ($filter !== null) {
                    if (is_numeric($filter)) {
                        // Filter by playlist ID
                        $sqlString .= " AND up.playlist_id = ?";
                    } else {
                        // Filter by playlist name
                        $sqlString .= " AND up.playlist_name = ?"; //you can filter the playlist_name if it's just one word
                    }
                }
        
                $sql = $this->pdo->prepare($sqlString);
    
                if ($filter !== null) {
                    $sql->execute([$user_id, $filter]);
                } else {
                    $sql->execute([$user_id]);
                }
        
                if ($sql->rowCount() > 0) {
                    $rows = $sql->fetchAll(PDO::FETCH_ASSOC);
        
                    // Group songs by playlist
                    foreach ($rows as $row) {
                        $playlist_id = $row['playlist_id'];
                        if (!isset($playlists[$playlist_id])) {
                            $playlists[$playlist_id] = [
                                'playlist_name' => $row['playlist_name'],
                                'songs' => []
                            ];
                        }
                        if (!empty($row['song_id'])) { // If a song exists in the playlist
                            $playlists[$playlist_id]['songs'][] = [
                                'song_id' => $row['song_id'],
                                'song_title' => $row['song_title'],
                                'artist' => $row['artist'],
                                'chord_lyrics' => $row['chord_lyrics'],
                                'mp3_path' => $row['mp3_path'],
                                'duration' => $row['duration']
                            ];
                        }
                    }
        
                    $code = 200;
                    $this->logger("Alira", "GET", "Successfully retrieved playlist");
                } else {
                    $errmsg = "No playlists found for this user";
                    $code = 404;
                    $this->logger("Alira", "GET", "Failed to retrieve playlist");
                }
        
                return array("data" => array_values($playlists), "errmsg" => $errmsg, "code" => $code);
        
            } catch (\PDOException $e) {
                $errmsg = $e->getMessage();
                $code = 400;
                return array("errmsg" => $errmsg, "code" => $code);
            }
        }
        

        //retrieved log files
        public function getLogs($date){

            session_start();
        
            if (!isset($_SESSION['username'])) {
                return array("errmsg" => "You must log in to view the log files", "code" => 401); // Error if not logged in
            }
        
            $username = $_SESSION['username'];
        
            // Fetch user_id from the database using username
            $sqlString = "SELECT user_id FROM accounts WHERE username = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute([$username]);
        
            if ($sql->rowCount() > 0) {
                $user = $sql->fetch(PDO::FETCH_ASSOC);
                $user_id = $user['user_id'];
            } else {
                return array("errmsg" => "User not found", "code" => 404);
            }


            $filename = "./logs/" . $date . ".log";
            
            $logs = array();
            try{
                $file = new SplFileObject($filename);
                while(!$file->eof()){
                    array_push($logs, $file->fgets());
                }
                $remarks = "success";
                $message = "Successfully retrieved logs.";
            }
            catch(Exception $e){
                $remarks = "failed";
                $message = $e->getMessage();
            }
            
    
            return $this->sendResponse(array("logs"=>$logs), $remarks, $message, 200);
        }

    }
?>