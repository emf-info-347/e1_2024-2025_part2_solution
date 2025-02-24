<?php
header("Access-Control-Allow-Origin: http://74.234.12.44:8080");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Utilisation de la classe ServiceTask
require_once 'services/ServiceTask.php';

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
                        // Si l'action est 'get_tasks'
                        case 'get_tasks':
                            $serviceTask = new ServiceTask(); // Créer une instance de ServiceTask
                            $tasks = $serviceTask->getTasks(); // Récupérer toutes les tâches
                            echo json_encode($tasks); // Retourner les tâches en JSON
                            break;
                        // Si l'action est 'get_comments'
                        case 'get_comments':
                            // Vérifier si l'identifiant de la tâche est défini
                            if(isset($_GET['taskId']))
                            {
                                $serviceTask = new ServiceTask(); // Créer une instance de ServiceTask
                                $comments = $serviceTask->getComments($_GET['taskId']); // Récupérer les commentaires de la tâche
                                echo json_encode($comments); // Retourner les commentaires en JSON
                            }
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
                        // Si l'action est 'add_task'
                        case 'add_task':
                            // Vérifier si le nom de la tâche est défini
                            if(isset($_POST['taskName']))
                            {
                                $serviceTask = new ServiceTask(); // Créer une instance de ServiceTask
                                $serviceTask->addTask($_POST['taskName'], $_POST['taskDue']); // Ajouter une tâche
                                echo $serviceTask ? json_encode(['status' => 'success']): json_encode(['status' => 'error']); // Retourner le statut de l'ajout
                            }else{
                                echo json_encode(['status' => 'error : taskName not set']); // Retourner une erreur
                            }
                            
                            break;
                        // Si l'action est 'add_comment'
                        case 'add_comment':
                            // Vérifier si l'identifiant de la tâche et le commentaire sont définis
                            if(isset($_POST['taskId']) && isset($_POST['comment']))
                            {
                                $serviceTask = new ServiceTask(); // Créer une instance de ServiceTask
                                $serviceTask->commentTask($_POST['taskId'], $_POST['comment']); // Ajouter un commentaire
                                echo $serviceTask ? json_encode(['status' => 'success']): json_encode(['status' => 'error']); // Retourner le statut de l'ajout
                            }else{
                                echo json_encode(['status' => 'error : taskId or comment not set']); // Retourner une erreur
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
                
                // Récupérer les données brutes de la requête PUT
                $input = file_get_contents('php://input');
                parse_str($input, $putData); // Si les données sont en JSON, les décoder
                
                // Vérifier si l'action est définie
                if(isset($putData['action']))
                {
                    // Vérifier l'action
                    switch ($putData['action']) {
                        // Si l'action est 'task_completed'
                        case 'task_completed':
                            // Vérifier si l'identifiant de la tâche est défini
                            if(isset($putData['taskId']))
                            {
                                $serviceTask = new ServiceTask(); // Créer une instance de ServiceTask
                                $serviceTask->completeTask($putData['taskId']); // Mettre la tâche en statut "completed"
                                echo $serviceTask ? json_encode(['status' => 'success']): json_encode(['status' => 'error']); // Retourner le statut de la mise à jour    
                            }else{
                                echo json_encode(['status' => 'error : taskId not set']); // Retourner une erreur
                            }

                            break;
                        // Si l'action n'est pas reconnue
                        default:
                            echo json_encode(['error' => 'Action PUT non reconnue']); // Retourner une erreur
                            break;
                    }
                }
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