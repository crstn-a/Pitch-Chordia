<?php
    class Get {

        protected $pdo;

        public function __construct(\PDO $pdo){
            $this->pdo = $pdo;
        }


        //retrieved user data on database
        public function getUser($user_id = null) {
            $sqlString = "SELECT * FROM users WHERE isdeleted = 0";
            if ($user_id != null) {
                $sqlString .= " AND user_id=" . $user_id;
            }

            $data = array ();
            $errmsg = "";
            $code = 0;

            try {
                if ($result = $this->pdo->query($sqlString)->fetchAll()){
                    foreach($result as $record){
                        array_push($data, $record);
                    }
                    $result = null;
                    $code = 200;
                    return array("code"=>$code, "data"=>$data);
                }
                else {
                    $errmsg = "No data found";
                    $code = 404;
                }
            }
            catch (\PDOException $e){
                $errmsg = $e->getMessage();
                $code = 403;
            }
            return array("code"=>$code, "errmsg"=>$errmsg);
        }

        //retrieved user playlist on database
        public function getPlaylist($playlist_id = null) {
            $sqlString = "SELECT * FROM playlists";
            if ($playlist_id != null) {
                $sqlString .= " WHERE playlist_id=" . $playlist_id;
            }

            $data = array ();
            $errmsg = "";
            $code = 0;

            try {
                if ($result = $this->pdo->query($sqlString)->fetchAll()){
                    foreach($result as $record){
                        array_push($data, $record);
                    }
                    $result = null;
                    $code = 200;
                    return array("code"=>$code, "data"=>$data);
                }
                else {
                    $errmsg = "No data found";
                    $code = 404;
                }
            }
            catch (\PDOException $e){
                $errmsg = $e->getMessage();
                $code = 403;
            }
            return array("code"=>$code, "errmsg"=>$errmsg);
        }

        //retrieved user song on database
        public function getSong($song_id = null) {
            $sqlString = "SELECT * FROM songs";
            if ($song_id != null) {
                $sqlString .= " WHERE song_id=" . $song_id;
            }

            $data = array ();
            $errmsg = "";
            $code = 0;

            try {
                if ($result = $this->pdo->query($sqlString)->fetchAll()){
                    foreach($result as $record){
                        array_push($data, $record);
                    }
                    $result = null;
                    $code = 200;
                    return array("code"=>$code, "data"=>$data);
                }
                else {
                    $errmsg = "No data found";
                    $code = 404;
                }
            }
            catch (\PDOException $e){
                $errmsg = $e->getMessage();
                $code = 403;
            }
            return array("code"=>$code, "errmsg"=>$errmsg);
        }

    }

?>