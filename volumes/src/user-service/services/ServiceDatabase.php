<?php
// utilise le fichier de configuration pour la connexion à la base de données
include_once('configConnexion.php');

// Classe ServiceDatabase
// Cette classe permet de gérer la connexion à la base de données
class ServiceDatabase {
    private static $instance = null; // Instance unique de la classe
    private $pdo; // Objet PDO pour la connexion à la base de données

    /**
     * Constructeur privé pour empêcher l'instanciation directe.
     */
    private function __construct() {
        try {
            $this->pdo = new PDO(DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
            // Active les exceptions pour PDO
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    /**
     * Méthode pour obtenir l'instance unique de la classe.
     * 
     * @return ServiceDatabase L'instance unique.
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new ServiceDatabase();
        }
        return self::$instance;
    }

    /**
     * Empêche le clonage de l'instance.
     */
    private function __clone() {}

    /**
     * Méthode unifiée pour exécuter des requêtes SQL.
     * 
     * @param string $query Requête SQL.
     * @param array $params Tableau de paramètres pour la requête préparée.
     * @return mixed Résultat de la requête (array pour SELECT, int pour INSERT/UPDATE/DELETE).
     */
    public function executeQuery($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);

            // Vérifie si la requête est un SELECT
            $isSelect = strtoupper(explode(' ', trim($query))[0]) === 'SELECT';

            if ($isSelect) {
                // Retourne les résultats pour une requête SELECT
                return $stmt->fetchAll();
            }

            // Retourne le nombre de lignes affectées pour INSERT/UPDATE/DELETE
            return $stmt->rowCount();
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    /**
     * Méthode pour executer plusieurs requêtes SQL dans une transaction.
     * @param array $queries Tableau de requêtes SQL.
     * @param array $params Tableau de tableaux de paramètres pour les requêtes préparées.
     */
    public function executeQueries($queries = [], $params = [])
    {
        try {
            // Démarre une transaction
            $this->pdo->beginTransaction();
            $paramNo = 0;
            // Exécute chaque requête avec ses paramètres
            foreach ($queries as $query) {
                $stmt = $this->pdo->prepare($query);
                $stmt->execute($params[$paramNo]);
                $paramNo++;
            }
            // Valide la transaction
            $this->pdo->commit();            

        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            // Annule la transaction
            $this->pdo->rollBack();
            die();
        }
    }
}
