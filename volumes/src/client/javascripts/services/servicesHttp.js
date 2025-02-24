/*
 * Couche de services HTTP (worker).
 */

// URL de base pour les appels HTTP.
var USERS_URL = "http://74.234.12.44:8081/users.php";
var TASKS_URL = "http://74.234.12.44:8082/tasks.php";

/**
 * Fonction permettant de demander la liste des tâches au serveur.
 * @param {type} Fonction de callback lors du retour avec succès de l'appel.
 * @param {type} Fonction de callback en cas d'erreur.
 */
function chargerTaches(successCallback, errorCallback) {
    $.ajax({
        type: "GET",
        dataType: "json",
        url: TASKS_URL,
        data: {
            action: "get_tasks"
        },
        success: successCallback,
        error: errorCallback
    });
}

/**
 * Fonction permettant de demander vérifier le login au serveur.
 * @param {*} login Le login de l'utilisateur.
 * @param {*} password Le mot de passe
 * @param {*} successCallback La fonction de callback en cas de succès.
 * @param {*} errorCallback La fonction de callback en cas d'erreur.
 */
function checkLogin(login, password, successCallback, errorCallback) {
    $.ajax({
        type: "POST",
        dataType: "json",
        url: USERS_URL,
        data: {
            action: "checkLogin",
            login: login,
            pwd: password
        },
        success: successCallback,
        error: errorCallback
    });
}

/**
 * Fonction permettant de demander la déconnexion de l'utilisateur.
 * @param {*} successCallback La fonction de callback en cas de succès.
 * @param {*} errorCallback La fonction de callback en cas d'erreur.  
 */
function disconnectUser(successCallback, errorCallback) {
    // Récupération du sessionID depuis les cookies
    const sessionID = sessionStorage.getItem("SESSION_ID");

    if (!sessionID) {
        console.error("Session ID manquant !");
        if (errorCallback) errorCallback({ error: "Session ID manquant" });
        return;
    }

    $.ajax({
        type: "Get",
        dataType: "json",
        url: USERS_URL,
        headers: {
            "Authorization": `Bearer ${sessionID}` // Envoi en header
        },
        data: {
            action: "disconnectUser"
        },
        success: successCallback,
        error: errorCallback
    });
}

/**
 * Fonction permettant de demander l'ajout d'une tâche.
 * @param {string} taskName Le nom de la tâche à ajouter.
 * @param {string} taskDue La date de la tâche à ajouter.
 * @param {function} successCallback La fonction de callback en cas de succès.
 * @param {function} errorCallback La fonction de callback en cas d'erreur.
 */
function sendAddTask(taskName, taskDue, successCallback, errorCallback) {
    // Récupération du sessionID depuis les cookies
    const sessionID = sessionStorage.getItem("SESSION_ID");

    if (!sessionID) {
        console.error("Session ID manquant !");
        if (errorCallback) errorCallback({ error: "Session ID manquant" });
        return;
    }

    $.ajax({
        type: "POST",
        dataType: "json",
        url: TASKS_URL,
        headers: {
            "Authorization": `Bearer ${sessionID}` // Envoi en header
        },
        data: {
            action: "add_task",
            taskName: taskName,
            taskDue: taskDue
        },
        success: successCallback,
        error: errorCallback
    });
}

/**
 * Fonction permettant de demander la completion d'une tâche.
 * @param {*} taskId L'identifiant de la tâche à marquer comme complétée.
 * @param {*} successCallback La fonction de callback en cas de succès.
 * @param {*} errorCallback La fonction de callback en cas d'erreur.
 */
function sendMarkTaskAsCompleted(taskId, successCallback, errorCallback) {
    // Récupération du sessionID depuis les cookies
    const sessionID = sessionStorage.getItem("SESSION_ID");

    if (!sessionID) {
        console.error("Session ID manquant !");
        if (errorCallback) errorCallback({ error: "Session ID manquant" });
        return;
    }

    $.ajax({
        type: "PUT",
        dataType: "json",
        url: TASKS_URL,
        headers: {
            "Authorization": `Bearer ${sessionID}` // Envoi en header
        },
        data: {
            action: "task_completed",
            taskId: taskId,
        },
        success: successCallback,
        error: errorCallback
    });
}

/**
 * Fonction permettant de demander l'ajout d'un commentaire.
 * @param {*} taskId L'identifiant de la tâche à laquelle ajouter un commentaire.
 * @param {*} comment Le commentaire à ajouter.
 * @param {*} successCallback La fonction de callback en cas de succès.
 * @param {*} errorCallback La fonction de callback en cas d'erreur.
 */
function sendAddComment(taskId, comment, successCallback, errorCallback) {
    // Récupération du sessionID depuis les cookies
    const sessionID = sessionStorage.getItem("SESSION_ID");

    if (!sessionID) {
        console.error("Session ID manquant !");
        if (errorCallback) errorCallback({ error: "Session ID manquant" });
        return;
    }

    $.ajax({
        type: "POST",
        dataType: "json",
        url: TASKS_URL,
        headers: {
            "Authorization": `Bearer ${sessionID}` // Envoi en header
        },
        data: {
            action: "add_comment",
            taskId: taskId,
            comment: comment
        },
        success: successCallback,
        error: errorCallback
    });
}

/**
 * Fonction permettant de demander la liste des commentaires d'une tâche.
 * @param {*} taskId L'identifiant de la tâche dont on veut les commentaires.
 * @param {*} successCallback La fonction de callback en cas de succès.
 * @param {*} errorCallback La fonction de callback en cas d'erreur. 
 */
function getComments(taskId, successCallback, errorCallback) {
    $.ajax({
        type: "GET",
        dataType: "json",
        url: TASKS_URL,
        data: {
            action: "get_comments",
            taskId: taskId
        },
        success: successCallback,
        error: errorCallback
    });
}

/**
 * Fonction utilitaire pour récupérer un cookie par son nom.
 * @param {string} name Le nom du cookie à récupérer.
 * @returns {string|null} La valeur du cookie ou null si absent.
 */
function getCookie(name) {
    const cookies = document.cookie.split('; ');
    for (let i = 0; i < cookies.length; i++) {
        const [key, value] = cookies[i].split('=');
        if (key === name) {
            return decodeURIComponent(value);
        }
    }
    return null;
}