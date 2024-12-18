<?php

include_once "Common.php";

class Patch extends CommonMethods {
    protected $pdo;
    
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
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
    
            //allowed fields to update
            $allowedFields = ['title', 'artist', 'chord_lyrics'];
    
            
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

            $this->logger("Alira", "PATCH", "Successfully update song details");
            return array("data" => $data, "code" => $code);
    
        } catch (\PDOException $e) {
            $this->logger("Alira", "PATCH", "Failed to update song details");
            $errmsg = $e->getMessage();
            $code = 400;
        } catch (\Exception $e) {
            $errmsg = $e->getMessage();
            $code = 400;
        }
    
        return array("errmsg" => $errmsg, "code" => $code);
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
            $this->logger("Alira", "PATCH", "Successfully archived song");
            return array("data" => $data, "code" => $code);
    
        } catch (\PDOException $e) {
            $this->logger("Alira", "PATCH", "Failed to archived song");
            $errmsg = $e->getMessage();
            $code = 400;
        } catch (\Exception $e) {
            $errmsg = $e->getMessage();
            $code = 400;
        }
    
        return array("errmsg" => $errmsg, "code" => $code);
    }
    
    public function patchUserPlaylist($body, $playlist_id) {
        $errmsg = "";
        $code = 0;
    
        try {
            // Check if the song_id exists
            $checkSql = "SELECT playlist_id FROM userplaylist WHERE playlist_id = ? AND isdeleted = 0";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->execute([$playlist_id]);
    
            if ($checkStmt->rowCount() === 0) {
                throw new \Exception("Song not found or has been deleted.");
            }
    
            //allowed fields to patch
            $allowedFields = ['playlist_name'];
    
            
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
            $values[] = $playlist_id;
    
            $sqlString = "UPDATE userplaylist SET " . implode(", ", $fields) . " WHERE playlist_id = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute($values);
    
            $code = 200;
            $data = "Playlist updated successfully!";
            $this->logger("Alira", "PATCH", "Successfully update playlist");
            return array("data" => $data, "code" => $code);
    
        } catch (\PDOException $e) {
            $this->logger("Alira", "PATCH", "Failed to update playlist");
            $errmsg = $e->getMessage();
            $code = 400;
        } catch (\Exception $e) {
            $errmsg = $e->getMessage();
            $code = 400;
        }
    
        return array("errmsg" => $errmsg, "code" => $code);
    }

    public function archivePlaylist($playlist_id) {
        $errmsg = "";
        $code = 0;
    
        try {
            // Check if the playlist ID exists and is not already archived
            $stringSql = "SELECT playlist_name FROM userplaylist WHERE playlist_id = ? AND isdeleted = 0";
            $stringSql = $this->pdo->prepare($stringSql);
            $stringSql->execute([$playlist_id]);
    
            if ($stringSql->rowCount() === 0) {
                throw new \Exception("Playlist not found.");
            }
    
            // Archive the song by setting isdeleted to 1
            $stringSql = "UPDATE userplaylist SET isdeleted = 1 WHERE playlist_id = ?";
            $stringSql = $this->pdo->prepare($stringSql);
            $stringSql->execute([$playlist_id]);
    
            $code = 200;
            $data = "Playlist archived successfully!";
            $this->logger("Alira", "PATCH", "Successfully archived playlist");
            return array("data" => $data, "code" => $code);
    
        } catch (\PDOException $e) {
            $this->logger("Alira", "PATCH", "Failed to update playlist");
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