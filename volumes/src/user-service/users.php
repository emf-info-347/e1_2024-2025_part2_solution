<?php
header("Access-Control-Allow-Origin: http://74.234.12.44:8080");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Utilisation de la classe ServiceUser
require_once 'services/ServiceUser.php';

// Vérifier si la méthode de la requête est définie
if (isset($_SERVER['REQUEST_METHOD']))
	{
        // Vérifier la méthode de la requête
        switch ($_SERVER['REQUEST_METHOD']) {
            // Si la méthode est GET
            case "GET":
                // Vérifier si l'action est définie
                if(isset($_GET['action'])) {
                    // Vérifier l'action
                    switch ($_GET['action']) {
                        // Si l'action est 'disconnectUser'
                        case 'disconnectUser':
                            $serviceUser = new ServiceUser(); // Créer une instance de ServiceUser
                            $user = $serviceUser->disconnect(); // Déconnecter l'utilisateur
                            echo $user;
                            break;
                        default:
                            echo json_encode(['error' => 'Action GET non reconnue']); // Retourner une erreur
                    }
                }
                break;
            // Si la méthode est POST
            case "POST":
                // Vérifier si l'action est définie
                if(isset($_POST['action'])) {
                    // Vérifier l'action
                    switch ($_POST['action']) {
                        // Si l'action est 'checkLogin'
                        case 'checkLogin':
                            // Vérifier si le login et le mot de passe sont définis
                            if(isset($_POST['login']) && isset($_POST['pwd']))
                            {
                                $serviceUser = new ServiceUser(); // Créer une instance de ServiceUser
                                $user = $serviceUser->checklogin($_POST['login'], $_POST['pwd'] ); // Vérifier le login et le mot de passe
                                echo json_encode($user); // Retourner l'identifiant de l'utilisateur
                            }
                            break;
                        // Si l'action n'est pas reconnue
                        default:
                            echo json_encode(['error' => 'Action POST non reconnue']);
                    }
                }
            
                break;
            // Si la méthode est PUT
            case "PUT":
                break;
            // Si la méthode est DELETE
            case "DELETE":
                break;
            // Si la méthode n'est pas reconnue
            default:
                echo json_encode(['error' => 'Requete non reconnue']);
                break;
        }

    }
?>