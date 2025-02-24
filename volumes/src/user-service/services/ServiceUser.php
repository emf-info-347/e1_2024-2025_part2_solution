<?php
// Utilisation de la classe ServiceDatabase
require_once 'services/ServiceDatabase.php';

// Classe ServiceUser
// Cette classe permet de gérer les utilisateurs
class ServiceUser{
    // Méthode pour vérifier la connexion d'un utilisateur
    function checklogin($username, $password) {
        $user = ServiceDatabase::getInstance()->executeQuery("SELECT * FROM t_users WHERE username = :login", [':login' => $username]);
        // Vérifie si un utilisateur a été trouvé
        if (!$user || empty($user[0])) {
            return ['success' => false, 'message' => 'Utilisateur non trouvé'];
        }
        $user = $user[0];
        if (password_verify($password, $user['password'])) {
            $sessionId = bin2hex(random_bytes(32)); // Générer un ID de session sécurisé
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // Expiration dans 1h
            
            // Sauvegarde en base
            ServiceDatabase::getInstance()->executeQuery("INSERT INTO t_sessions (pk_session, user_id, expires_at) VALUES (?, ?, ?)", 
            [$sessionId, $user['pk_user'], $expiresAt]);
    
            // Stocker dans un cookie sécurisé
            setcookie("SESSION_ID", $sessionId, [
                'expires' => time() + 3600,
                'path' => '/',
                'secure' => false, // Mettre `true` en production avec HTTPS
                'httponly' => true, // Empêche l'accès JavaScript
                'samesite' => 'None' // Mettre `None` si frontend ≠ backend
            ]);
            //var_dump(headers_list());
    
            return ['success' => true, 'session_id' => $sessionId, 'pk_user' => $user['pk_user']];
        }
    
        return ['success' => false, 'message' => 'Identifiants incorrects'];
    }
    

    public function disconnect() {
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
            return json_encode(['success' => false, 'error' => 'Aucune session trouvée']);
        }
    
        // Supprime la session et vérifie le nombre de lignes affectées
        $deletedRows = ServiceDatabase::getInstance()->executeQuery("DELETE FROM t_sessions WHERE pk_session = ?", [$sessionId]);
    
        setcookie("SESSION_ID", "", time() - 3600, "/"); // Supprimer le cookie
    
        if ($deletedRows > 0) {
            return json_encode(['success' => true]);
        } else {
            http_response_code(400);
            return json_encode(['success' => false, 'error' => 'Session introuvable ou déjà supprimée']);
        }
    }
    

}

?>