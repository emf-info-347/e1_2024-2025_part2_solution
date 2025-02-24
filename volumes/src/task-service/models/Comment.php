<?php
// Classe Comment, implémente l'interface JsonSerializable
// Cette classe représente un commentaire associé à une tâche
class Comment implements JsonSerializable {
    public $id;
    public $comment;
    public $created_at;
    public $fk_task;
    public $user_name;

    // Constructeur de la classe
    public function __construct($id, $comment, $created_at, $fk_task, $user_name) {
        $this->id = $id;
        $this->comment = $comment;
        $this->created_at = $created_at;
        $this->fk_task = $fk_task;
        $this->user_name = $user_name;
    }

    // Implémentation de l'interface JsonSerializable
    public function jsonSerialize():mixed {
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'fk_task' => $this->fk_task,
            'user_name' => $this->user_name
        ];
    }
}
?>
