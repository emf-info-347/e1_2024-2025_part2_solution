$(document).ready(function () {
    // Lorsque le document est chargé, récupère un script externe
    $.getScript("javascripts/services/servicesHttp.js", function () {
        // Charge les tâches à afficher sur la page
        chargerTaches(chargerTachesSuccess, chargerTachesError);
        // Met à jour les composants en fonction de l'état de connexion de l'utilisateur
        updateConnection();
    });
});

/**
 * Fonction exécutée lorsque les tâches sont chargées avec succès.
 * @param {Array} data - Liste des tâches récupérées.
 * @param {string} textStatus - Statut de la requête.
 * @param {Object} jqXHR - Objet XMLHttpRequest utilisé pour la requête.
 */
function chargerTachesSuccess(data, textStatus, jqXHR) {
    var divTask = $("#task-list"); // Récupère l'élément où afficher les tâches
    var task = '';

    for (var i = 0; i < data.length; i++) {
        const taskData = data[i];
        const taskName = taskData.name || "Tâche sans nom"; // Nom de la tâche ou valeur par défaut
        const userId = taskData.userId; // ID de l'utilisateur propriétaire de la tâche
        // Formatage de la date d'échéance si elle existe
        const dueDate = taskData.due ? ` (Échéance : ${new Intl.DateTimeFormat('fr-CH').format(new Date(taskData.due))})` : "";
        // Vérifie si la tâche est complétée
        const isCompleted = taskData.status === "completed" ? true : false;

        // Génère le HTML pour chaque tâche
        task += `
        <div 
            class="task-item ${isCompleted ? 'completed' : ''}" 
            data-task-id="${taskData.id}"
        >
            <!-- En-tête de la tâche -->
            <div class="task-header" onclick="viewComments(${taskData.id})">
                <span>${taskName}${dueDate}</span>
                ${sessionStorage.getItem("logged") == userId && !isCompleted ? `<button class="complete-button" onclick="markAsCompleted(${taskData.id})">Réalisée</button>` : ""}
            </div>

            <!-- Zone des commentaires -->
            <div class="comments-section" id="comments-${taskData.id}" style="display: none;">
                <h4>Commentaires</h4>
                <div class="comments-list">
                    <!-- Les commentaires ajoutés dynamiquement apparaîtront ici -->
                </div>
                
                ${sessionStorage.getItem("logged") != -1 && !isCompleted ? `<textarea placeholder="Ajouter un commentaire..."></textarea><button onclick="addComment(${taskData.id})">Ajouter</button>` : ""}
                
            </div>
        </div>
        `;
    }

    divTask.html(task); // Met à jour le contenu de la liste des tâches
    divTask.trigger("change");// Déclenche un événement indiquant que la liste a été mise à jour
}

/**
 * Fonction exécutée en cas d'erreur lors du chargement des tâches.
 * @param {Object} jqXHR - Objet XMLHttpRequest utilisé pour la requête.
 * @param {string} textStatus - Statut de la requête.
 * @param {string} errorThrown - Erreur survenue lors de la requête.
 */
function chargerTachesError(jqXHR, textStatus, errorThrown) {
    console.log("chargerTachesError"); // Affiche un message dans la console
    console.log(textStatus); // Affiche le statut de la requête
    console.log(errorThrown); // Affiche l'erreur survenue
}

/**
 * Fonction exécutée lorsqu'un utilisateur se connecte.
 */
function openLoginPopup() {
    // Vérifie si l'utilisateur est déjà connecté
    if (sessionStorage.getItem("logged") === null || sessionStorage.getItem("logged") == -1) {
        // Affiche la fenêtre de connexion
        document.getElementById("login-popup").style.display = "block";
    } else {
        // Déconnecte l'utilisateur
        disconnectUser(
            function (answer) {
                if (answer.success === true) {
                    sessionStorage.setItem("logged", -1);
                    sessionStorage.removeItem("SESSION_ID");
                    updateConnection();
                    chargerTaches(chargerTachesSuccess, chargerTachesError);
                } else {
                    console.log(answer.error);
                }
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log("Déconnexion échouée");
                console.log(textStatus);
                console.log(errorThrown);
            }
        );
    }
}

/**
 * Fonction exécutée lorsqu'un utilisateur ferme la fenêtre de connexion.
 */
function closeLoginPopup() {
    document.getElementById("login-popup").style.display = "none";
}

/**
 * Fonction exécutée lorsqu'un utilisateur se connecte.
 */
function connect() {
    var loginText = $("#login-username").val(); // Récupère le nom d'utilisateur
    var passText = $("#login-password").val(); // Récupère le mot de passe

    // Vérifie les informations de connexion
    checkLogin(loginText, passText,
        // En cas de succès
        function (answer) {
            if (answer.success === true) {
                // Cache la fenêtre de connexion
                $("#login-popup").css("display", "none");

                // Enregistre l'état de connexion (facultatif)
                sessionStorage.setItem("logged", answer.pk_user);
                sessionStorage.setItem("SESSION_ID", answer.session_id)

                updateConnection(); // Met à jour l'affichage
                // Charge les tâches de l'utilisateur connecté
                chargerTaches(chargerTachesSuccess, chargerTachesError);
                $("#login-username").val(""); // Réinitialise le champ de nom d'utilisateur
                $("#login-password").val(""); // Réinitialise le champ de mot de passe
            } else {
                console.log(answer.message);
            }
        },
        // En cas d'échec
        function () {
            // Affiche un message d'erreur
            sessionStorage.setItem("logged", -1);
            sessionStorage.removeItem("SESSION_ID");
            updateConnection(); // Met à jour l'affichage
        }
    );
}


/**
 * Fonction exécutée lorsqu'un utilisateur se connecte ou se déconnecte.
 * Permet de mettre à jour l'affichage en fonction de l'état de connexion.
 */
function updateConnection() {
    if (sessionStorage.getItem("logged") === null || sessionStorage.getItem("logged") == -1) {
        $("#login-button").html("Se connecter");
        $("#task-form").css("display", "none");
    } else {
        $("#login-button").html("Se déconnecter");
        $("#task-form").css("display", "block");
    }
}

/**
 * Fonction exécutée lorsqu'un utilisateur ajoute une tâche.
 * @returns  {void} - Rien.
 */
function addTask() {
    const taskInput = document.getElementById("task-input").value.trim(); // Récupère le nom de la tâche
    const dueDateInput = document.getElementById("due-date-input").value; // Récupère la date d'échéance

    // Vérifie si le champ de la tâche est vide
    if (taskInput === "") {
        // Affiche un message d'erreur
        alert("Veuillez entrer une tâche !");
        // Arrête l'exécution de la fonction
        return;
    }

    // Envoie la tâche au serveur
    sendAddTask(taskInput, dueDateInput,
        // En cas de succès
        function () { chargerTaches(chargerTachesSuccess, chargerTachesError); },
        // En cas d'échec
        function () { console.log("Erreur"); });
}

/**
 * Fonction exécutée lorsqu'un utilisateur supprime une tâche.
 * @param {*} taskId - ID de la tâche à afficher en tant que complétée. 
 */
function markAsCompleted(taskId) {
    // Met à jour la tâche en tant que complétée
    sendMarkTaskAsCompleted(taskId,
        // En cas de succès
        function () {
            chargerTaches(chargerTachesSuccess, chargerTachesError);
        },
        // En cas d'échec
        function () {
            console.log(`Erreur lors de la mise à jour de la tâche ${taskId}`);
        }
    );
}

/**
 * Fonction exécutée lorsqu'un utilisateur demande à voir les commentaires d'une tâche.
 * @param {*} taskId - ID de la tâche dans laquelle il faut afficher les commentaires.
 * @returns {void} - Rien.
 */
function viewComments(taskId) {
    const commentsSection = document.getElementById(`comments-${taskId}`); // Récupère la section des commentaires

    // Vérifie si la section des commentaires existe
    if (!commentsSection) {
        // Affiche un message d'erreur
        console.error(`Section des commentaires introuvable pour la tâche ${taskId}`);
        return; // Arrête l'exécution de la fonction
    }

    // Récupère les commentaires de la tâche
    getComments(taskId, function (comments) {
        // Récupère la liste des commentaires
        const commentsList = commentsSection.querySelector(".comments-list");
        // Réinitialise la liste des commentaires
        commentsList.innerHTML = "";
        // Parcours les commentaires
        comments.forEach(comment => {

            // Récupère les informations du commentaire
            const userName = comment.user_name;
            const commentDate = comment.created_at;

            // Crée un élément HTML pour chaque commentaire
            const newComment = document.createElement("div");
            newComment.classList.add("comment");

            // Ajoute le contenu du commentaire
            newComment.innerHTML = `
            <div class="comment-header">
                <span class="comment-user">${userName}</span>
                <span class="comment-date">${commentDate}</span>
            </div>
            <div class="comment-content">
                ${comment.comment}
            </div>`;

            // Ajoute le commentaire à la liste
            commentsList.appendChild(newComment);
        });
    },
        // En cas d'erreur
        function () {
            console.log(`Erreur lors de la récupération des commentaires pour la tâche ${taskId}`);
        });

    // Affiche ou masque la section des commentaires
    if (commentsSection.style.display === "none" || commentsSection.style.display === "") {
        commentsSection.style.display = "block";
    } else {
        commentsSection.style.display = "none";
    }
}

/**
 * Fonction exécutée lorsqu'un utilisateur ajoute un commentaire à une tâche.
 * @param {*} taskId - ID de la tâche à laquelle ajouter un commentaire.
 */
function addComment(taskId) {
    // Récupère les éléments nécessaires pour ajouter un commentaire
    const commentsSection = document.getElementById(`comments-${taskId}`);
    const textarea = commentsSection.querySelector("textarea");
    const commentsList = commentsSection.querySelector(".comments-list");

    // Vérifie si le champ de commentaire n'est pas vide
    if (textarea.value.trim() !== "") {
        // Envoie le commentaire au serveur
        sendAddComment(taskId, textarea.value,
            // En cas de succès
            function () {
                chargerTaches(chargerTachesSuccess, chargerTachesError);
            },
            // En cas d'erreur
            function () {
                console.log(`Erreur lors de l'ajout du commentaire à la tâche ${taskId}`);
            }
        );
    } else {
        // Affiche un message d'erreur
        alert("Veuillez écrire un commentaire avant de l'ajouter.");
    }
}