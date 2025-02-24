<?php
// utilise le fichier du service de base de données
require_once 'services/ServiceDatabase.php';
// utilise les classes Task et Comment
require_once 'models/Task.php';
require_once 'models/Comment.php';

// Classe ServiceTask
// Cette classe permet de gérer les tâches
class ServiceTask {

    // Méthode pour obtenir toutes les tâches
    public function getTasks() {
        // Exécute une requête SQL pour obtenir toutes les tâches
        $rows = ServiceDatabase::getInstance()->executeQuery("SELECT * FROM t_tasks ORDER BY status ASC, due_date ASC;");
        
        // Convertir les lignes en objets Task
        $tasks = [];
        foreach ($rows as $row) {
            $tasks[] = new Task($row['pk_task'], $row['name'], $row['status'], $row['due_date'], $row['FK_user']);
        }

        return $tasks; // Retourne un tableau d'objets Task
    }

    // Méthode pour ajouter une tâche
    public function addTask($taskName, $taskDue) {
        // Vérifie si l'utilisateur est connecté via la session stockée en base
        $userId = $this->verifySession(); // Récupère l'ID utilisateur via la base de données

        // Exécute une requête SQL pour ajouter une tâche
        $query = "";
        $params = [];

        // Vérifie si la date d'échéance est vide
        if (empty($taskDue)) {
            $query = "INSERT INTO t_tasks (name, status, FK_user) VALUES (:name, 'pending', :idUser)";
            $params = [':name' => $taskName, ':idUser' => $userId];
        } else {
            $query = "INSERT INTO t_tasks (name, status, due_date, FK_user) VALUES (:name, 'pending', :due_date, :idUser)";
            $params = [':name' => $taskName, ':due_date' => $taskDue, ':idUser' => $userId];
        }

        // Exécute la requête
        ServiceDatabase::getInstance()->executeQuery($query, $params);

        return true;
    }

    // Méthode pour mettre une tâche en statut "completed"
    public function completeTask($taskId) {
        // Vérifie si l'utilisateur est connecté via la session stockée en base
        $userId = $this->verifySession(); // Récupère l'ID utilisateur via la base de données

        // Exécute une requête SQL pour mettre une tâche en statut "completed" uniquement si elle appartient à l'utilisateur
        $affectedRows = ServiceDatabase::getInstance()->executeQuery(
            "UPDATE t_tasks SET status = 'completed' WHERE pk_task = :id AND FK_user = :idUser",
            [':id' => $taskId, ':idUser' => $userId]
        );

        // Vérifie si la mise à jour a affecté au moins une ligne
        return $affectedRows > 0;
    }


    // Méthode pour commenter une tâche
    public function commentTask($taskId, $comment) {
        // Vérifie si l'utilisateur est connecté via la session stockée en base
        $userId = $this->verifySession(); // Récupère l'ID utilisateur via la base de données
        
        // Exécute une requête SQL pour ajouter un commentaire
        ServiceDatabase::getInstance()->executeQuery(
            "INSERT INTO t_comments (fk_task, fk_user, comment) VALUES (:fk_task, :fk_user, :comment)",
            [
                ':fk_task' => $taskId,
                ':fk_user' => $userId, // Correction ici
                ':comment' => $comment
            ]
        );
    }

    // Méthode pour obtenir les commentaires d'une tâche
    public function getComments($taskId) {
        // Exécute une requête SQL pour obtenir les commentaires d'une tâche
        $rows = ServiceDatabase::getInstance()->executeQuery("SELECT c.pk_comment, c.fk_task, u.username AS author, c.comment, c.created_at
        FROM t_comments c INNER JOIN t_users u ON c.fk_user = u.pk_user
        WHERE c.fk_task = :task_id; ",  [':task_id' => $taskId]);

        // Convertir les lignes en objets Comment
        $comments = [];
        foreach ($rows as $row) {
            $comments[] = new Comment($row['pk_comment'], $row['comment'], $row['created_at'], $row['fk_task'], $row['author']);
        }
        return $comments;
    }

    public function verifySession() {
        // Vérification du cookie SESSION_ID
        $sessionId = $_COOKIE['SESSION_ID'] ?? null;
    
        // Vérification du header Authorization si le cookie est absent
        if (!$sessionId) {
            $headers = getallheaders(); // Récupère tous les headers envoyés
            if (isset($headers['Authorization']) && strpos($headers['Authorization'], 'Bearer ') === 0) {
                $sessionId = substr($headers['Authorization'], 7); // Récupère la valeur après "Bearer "
            }
        }
    
        // Si toujours pas de session, renvoyer 401
        if (!$sessionId) {
            http_response_code(401);
            echo json_encode(['error' => 'Session non valide']);
            exit;
        }
    
        // Vérification en base de données
        $session = ServiceDatabase::getInstance()->executeQuery(
            "SELECT user_id FROM t_sessions WHERE pk_session = ? AND expires_at > NOW()", 
            [$sessionId]
        );
    
        if (!$session || empty($session[0])) {
            http_response_code(401);
            echo json_encode(['error' => 'Session expirée ou invalide']);
            exit;
        }
    
        return $session[0]['user_id']; // Retourne l'ID de l'utilisateur connecté
    }
    
    
}
?>
