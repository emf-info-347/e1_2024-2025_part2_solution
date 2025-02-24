<?php
// Classe Task, implémente l'interface JsonSerializable
// Cette classe représente une tâche associée à un utilisateur
class Task implements JsonSerializable {
    public $id;
    public $name;
    public $status;
    public $due;
    public $userId;

    // Constructeur de la classe
    public function __construct($id, $name, $status, $due, $userId) {
        $this->id = $id;
        $this->name = $name;
        $this->status = $status;
        $this->due = $due;
        $this->userId = $userId;
    }

    // Implémentation de l'interface JsonSerializable
    public function jsonSerialize():mixed {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'due' => $this->due,
            'userId' => $this->userId
        ];
    }
}
?>
