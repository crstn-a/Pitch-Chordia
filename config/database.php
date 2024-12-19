<?php
    //meta data
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=utf-8");
    header("Access-Control-Allow-Methods: POST, GET, PATCH, OPTIONS");
    header("Access-Control-Max-Age: 3600");

    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Auth-User");

    if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Auth-User");
        header("HTTP/1.1 200 OK");
        die();
      }
      
    date_default_timezone_set("Asia/Manila");

    define("SERVER", "localhost");
    define("DATABASE", "pitchordia_db");
    define("USER", "root");
    define("PASSWORD", "");
    define("TOKEN_KEY", "A1FD31441A4ADE5E2F9A1CF776EFC");

    class Connection {
        protected $connectionString = "mysql:host=" . SERVER . ";dbname=" . DATABASE . ";charset=utf8";
        protected $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false 
        ];

        public function connect() {
            return new \PDO($this->connectionString, USER, PASSWORD, $this->options);
        }
    }
?>