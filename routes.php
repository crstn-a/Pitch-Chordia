<?php 

//import files
require_once "./config/database.php";
require_once "./modules/Get.php";
require_once "./modules/Post.php";
require_once "./modules/Patch.php";
require_once "./modules/Authentication.php";

$db = new Connection();
$pdo = $db->connect();

//instantiate post & get class
$post = new Post ($pdo);
$get= new Get ($pdo);
$patch = new Patch($pdo);
$authentication = new Authentication($pdo);


//retrieved and endpoints and split
if (isset($_REQUEST['request'])){
    $request = explode ("/", $_REQUEST['request']);
}
else {
    echo "URL does not exist.";
}

switch ($_SERVER['REQUEST_METHOD']) {
    case "GET":
        if ($authentication->isAuthorized()){
            switch ($request[0]) {
                case 'song':
                    if (count ($request) > 1) {
                        echo json_encode($get->getSong($request[1]));
                    }
                    else {
                        echo json_encode($get->getSong());
                    }
                    break;

                case 'playlist':
                    if (count ($request) > 1) {
                        echo json_encode($get->getUserPlaylist($request[1]));
                    }
                    else {
                        echo json_encode($get->getUserPlaylist());
                    }
                    break;
                
                default:
                    http_response_code(401);
                    echo "This is invalid endpoint";
                    break;
            }
        }
        else {
            echo "Unauthorized User";
        }

        break;

            case "POST":
                $body = json_decode(file_get_contents("php://input"));
                switch ($request[0]) {
                    case 'register': //registration endpoint -- add account
                        echo json_encode($authentication->addAccount($body));
                    break;

                    case 'login': //login endpoint 
                        echo json_encode($authentication->login($body));
                        break;
    
                    case 'song':
                        $body = $_POST;
                        $file = $_FILES;
                        echo json_encode($post->postSong($body, $file));
                        break;

                    case 'create-playlist':
                        echo json_encode($post->postUserPlaylist($body));
                        break;
                    
                    default:
                        http_response_code(401);
                        echo "This is invalid endpoint";
                        break;
                }
            break;

            
            case "PATCH":
                $body = json_decode(file_get_contents("php://input"));
                switch($request[0]){
                    case "song":
                        $file = $_FILES;
                        echo json_encode($patch->patchSong($body, $request[1]));
                    break;
                }
            break;


            case "DELETE":
                switch($request[0]){
                    case "song":
                        $file = $_FILES;
                        echo json_encode($patch->archiveSong($request[1]));
                        break;
                    }
            break;

            default:
                http_response_code(400);
                echo "Invalid Request Method.";
            break;
                
        }


?>