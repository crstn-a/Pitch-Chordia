<?php

    include_once "Common.php";

    class Get extends CommonMethods {

        protected $pdo;

        public function __construct(\PDO $pdo){
            $this->pdo = $pdo;
        }


        //retrieved user data on database
        public function getUser($user_id = null) {
            $condition = "isdeleted = 0";
            if ($user_id != null) {
                $condition .= " AND song_id = $user_id";
            }
            $result = $this->getData("users", $condition, $this->pdo);
            if ($result['code'] == 200) {
                return $this->sendResponse($result['data'], "Successfully retrieved records.", "Success", $result['code']);
            }
            return $this->sendResponse(null, "Failed retrieved records", "Failed", $result['code']);
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
            return $this->sendResponse(null, "Failed retrieved records", "Failed", $result['code']);
        } 
    }
?>