<?php

    include_once "Common.php";

    class Get extends CommonMethods {

        protected $pdo;

        public function __construct(\PDO $pdo){
            $this->pdo = $pdo;
        }

        //retrieved user song on database
        public function getSong($song_id = null) {
            $condition = "isdeleted = 0";
            if($song_id != null) {
                $condition .= " AND song_id = $song_id";
            }
            $result = $this->getData("songs", $condition, $this->pdo);
            if ($result['code'] == 200) {
                return $this->sendResponse($result['data'], "Successfully retrieved records.", "Success", $result['code']);
            }
            return $this->sendResponse(null, "Failed retrieved records of Songs", "Failed", $result['code']);
        } 

        public function getUserPlaylist() {
            $errmsg = "";
            $code = 0;
            $playlists = [];
        
            session_start();
        
            if (!isset($_SESSION['username'])) {
                return array("errmsg" => "You must log in to view your playlists", "code" => 401);  // Error if not logged in
            }
        
            $username = $_SESSION['username'];
        
            // Fetch user_id from the database using username
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
                $sqlString = "SELECT playlist_name FROM userplaylist WHERE user_id = ?";
                $sql = $this->pdo->prepare($sqlString);
                $sql->execute([$user_id]);
        
                if ($sql->rowCount() > 0) {
                    $playlists = $sql->fetchAll(PDO::FETCH_ASSOC); 
                    $code = 200;
                } else {
                    $errmsg = "No playlists found for this user";
                    $code = 404;
                }
        
                return array("data" => $playlists, "errmsg" => $errmsg, "code" => $code);
        
            } catch (\PDOException $e) {
                $errmsg = $e->getMessage();
                $code = 400;
                return array("errmsg" => $errmsg, "code" => $code); 
            }
        }
        

    }
?>