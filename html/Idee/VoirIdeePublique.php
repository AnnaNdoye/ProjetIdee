<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../connexion.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "idee";

$connexion = new mysqli($host, $user, $password, $database);

if ($connexion->connect_error) {
    die("Erreur lors de la connexion: " . $connexion->connect_error);
}

if (!isset($_GET['id'])) {
    die("ID de l'idée non spécifié.");
}

$idee_id = $_GET['id'];
$employe_id = $_SESSION['user_id'];

// Récupérer les détails de l'idée
$query = "
    SELECT idee.id_idee, idee.titre, idee.contenu_idee, idee.est_publique, idee.date_creation, idee.date_modification, idee.statut,
    categorie.nom_categorie, fichier.nom_fichier, fichier.type, fichier.contenu_fichier,
    employe.nom, employe.prenom, employe.photo_profil,
    (SELECT COUNT(*) FROM LikeIdee WHERE idee_id = idee.id_idee) AS like_count,
    (SELECT COUNT(*) FROM LikeIdee WHERE idee_id = idee.id_idee AND employe_id = ?) AS user_liked,
    (SELECT COUNT(*) FROM commentaire WHERE idee_id = idee.id_idee) AS comment_count
    FROM idee
    LEFT JOIN categorie ON idee.categorie_id = categorie.id_categorie
    LEFT JOIN fichier ON idee.id_idee = fichier.idee_id
    LEFT JOIN employe ON idee.employe_id = employe.id_employe
    WHERE idee.id_idee = ?
";

$stmt = $connexion->prepare($query);
if ($stmt === false) {
    die("Erreur lors de la préparation de la requête: " . $connexion->error);
}

$stmt->bind_param('ii', $employe_id, $idee_id);
if (!$stmt->execute()) {
    die("Erreur lors de l'exécution de la requête: " . $stmt->error);
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Idée non trouvée ou vous n'êtes pas autorisé à la voir.");
}

$idee = $result->fetch_assoc();

// Récupérer les commentaires
$comments_query = "
    SELECT commentaire.id_commentaire, commentaire.contenu, commentaire.date_creation, commentaire.date_modification, 
    employe.nom, employe.prenom, employe.photo_profil,
    (SELECT COUNT(*) FROM LikeCommentaire WHERE commentaire_id = commentaire.id_commentaire) AS like_count,
    (SELECT COUNT(*) FROM LikeCommentaire WHERE commentaire_id = commentaire.id_commentaire AND employe_id = ?) AS user_liked,
    commentaire.employe_id AS comment_employe_id
    FROM commentaire
    LEFT JOIN employe ON commentaire.employe_id = employe.id_employe
    WHERE commentaire.idee_id = ?
    ORDER BY commentaire.date_creation DESC
";

$comments_stmt = $connexion->prepare($comments_query);
if ($comments_stmt === false) {
    die("Erreur lors de la préparation de la requête: " . $connexion->error);
}

$comments_stmt->bind_param('ii', $employe_id, $idee_id);
if (!$comments_stmt->execute()) {
    die("Erreur lors de l'exécution de la requête: " . $comments_stmt->error);
}

$comments_result = $comments_stmt->get_result();
$comments = $comments_result->fetch_all(MYSQLI_ASSOC);

// Ajouter un commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'])) {
    $comment_content = $_POST['comment_content'];
    $comment_query = "
        INSERT INTO commentaire (contenu, employe_id, idee_id) 
        VALUES (?, ?, ?)
    ";
    $comment_stmt = $connexion->prepare($comment_query);
    if ($comment_stmt === false) {
        die("Erreur lors de la préparation de la requête: " . $connexion->error);
    }
    $comment_stmt->bind_param('sii', $comment_content, $employe_id, $idee_id);
    if (!$comment_stmt->execute()) {
        die("Erreur lors de l'exécution de la requête: " . $comment_stmt->error);
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

// Modifier un commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_comment_id'])) {
    $edit_comment_id = $_POST['edit_comment_id'];
    $new_content = $_POST['new_content'];
    $edit_query = "
        UPDATE commentaire 
        SET contenu = ?, date_modification = NOW() 
        WHERE id_commentaire = ? AND employe_id = ?
    ";
    $edit_stmt = $connexion->prepare($edit_query);
    if ($edit_stmt === false) {
        die("Erreur lors de la préparation de la requête: " . $connexion->error);
    }
    $edit_stmt->bind_param('sii', $new_content, $edit_comment_id, $employe_id);
    if (!$edit_stmt->execute()) {
        die("Erreur lors de l'exécution de la requête: " . $edit_stmt->error);
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

// Supprimer un commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id'])) {
    $delete_comment_id = $_POST['delete_comment_id'];

    // D'abord, supprimer les likes associés au commentaire
    $delete_likes_query = "
        DELETE FROM LikeCommentaire 
        WHERE commentaire_id = ?
    ";
    $delete_likes_stmt = $connexion->prepare($delete_likes_query);
    if ($delete_likes_stmt === false) {
        die("Erreur lors de la préparation de la requête: " . $connexion->error);
    }
    $delete_likes_stmt->bind_param('i', $delete_comment_id);
    if (!$delete_likes_stmt->execute()) {
        die("Erreur lors de l'exécution de la requête: " . $delete_likes_stmt->error);
    }

    // Puis, supprimer le commentaire
    $delete_query = "
        DELETE FROM commentaire 
        WHERE id_commentaire = ? AND employe_id = ?
    ";
    $delete_stmt = $connexion->prepare($delete_query);
    if ($delete_stmt === false) {
        die("Erreur lors de la préparation de la requête: " . $connexion->error);
    }
    $delete_stmt->bind_param('ii', $delete_comment_id, $employe_id);
    if (!$delete_stmt->execute()) {
        die("Erreur lors de l'exécution de la requête: " . $delete_stmt->error);
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

// Gérer les likes des idées
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like'])) {
    $like = $_POST['like'];
    
    if ($like == 'true') {
        $like_query = "
            INSERT INTO LikeIdee (employe_id, idee_id) 
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE employe_id = employe_id
        ";
    } else {
        $like_query = "
            DELETE FROM LikeIdee WHERE employe_id = ? AND idee_id = ?
        ";
    }

    $like_stmt = $connexion->prepare($like_query);
    if ($like_stmt === false) {
        die("Erreur lors de la préparation de la requête: " . $connexion->error);
    }
    $like_stmt->bind_param('ii', $employe_id, $idee_id);
    if (!$like_stmt->execute()) {
        die("Erreur lors de l'exécution de la requête: " . $like_stmt->error);
    }

    $like_stmt->close();
    exit(json_encode(['success' => true]));
}

// Gérer les likes des commentaires
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_comment'])) {
    $comment_id = $_POST['comment_id'];
    $like_comment = $_POST['like_comment'];
    
    if ($like_comment == 'true') {
        $like_comment_query = "
            INSERT INTO LikeCommentaire (employe_id, commentaire_id) 
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE employe_id = employe_id
        ";
    } else {
        $like_comment_query = "
            DELETE FROM LikeCommentaire WHERE employe_id = ? AND commentaire_id = ?
        ";
    }

    $like_comment_stmt = $connexion->prepare($like_comment_query);
    if ($like_comment_stmt === false) {
        die("Erreur lors de la préparation de la requête: " . $connexion->error);
    }
    $like_comment_stmt->bind_param('ii', $employe_id, $comment_id);
    if (!$like_comment_stmt->execute()) {
        die("Erreur lors de l'exécution de la requête: " . $like_comment_stmt->error);
    }

    $like_comment_stmt->close();
    exit(json_encode(['success' => true]));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png" href="../../static/img/icon.png">
    <link rel="stylesheet" href="../../static/css/style1.css">
    <link rel="stylesheet" href="../../static/css/style.css">
    <title>Voir Idée</title>
    <style>
        .container {
            width: 2000px;
            margin: auto;
            padding: 20px;
        }
        .idea-details {
            border: 1px solid #ddd;
            padding: 100px;
            border-radius: 8px;
            background: #f9f9f9;
            position: relative;
        }
        .creator-info {
            display: flex;
            align-items: center;
            position: absolute;
            top: 10px;
            left: 10px;
        }
        .creator-info img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .creator-info .name {
            font-weight: bold;
        }
        .idea-dates {
            text-align: right;
            font-size: 0.9em;
            color: #666;
        }
        .status {
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            font-weight: bolder;
            top: 10px;
            right: 10px;
            text-align: right;
        }
        .status.soumis { background: orange; color: white; margin-left: 1175px;}
        .status.approuvé { background: green; color: white; margin-left: 1175px;}
        .status.rejeté { background: red; color: white; margin-left: 1175px;}
        .status.implémenté { background: blue; color: white; margin-left: 1175px;}
        .like-container {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .like-button {
            background: none;
            border: none;
            cursor: pointer;
            text-align: right;
            font-size: 1.5rem;
            transition: transform 0.2s ease-in-out;
        }
        .like-button:hover {
            transform: scale(1.2);
        }
        .like-count {
            margin-left: 5px;
            font-size: 1.2rem;
        }
        .comments-section {
            margin-top: 40px;
        }
        .comment {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
            position: relative;
        }
        .comment:last-child {
            border-bottom: none;
        }
        .comment .creator-info {
            position: static;
            margin-bottom: 10px;
        }
        .comment .like-container {
            justify-content: flex-start;
            position: absolute;
            bottom: 10px;
            right: 10px;
        }
        .comment .like-count {
            margin-left: 5px;
        }

    .edit-comment-form {
        margin-top: 10px;
    }

    .edit-button {
        margin-top: 10px;
        margin-right: 10px;
    }
        .container {
            width: 2000px;
            margin: auto;
            padding: 20px;
        }
        .idea-details {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
        }
        .idea-details h2 {
            margin-top: 0;
        }
        .comments-section {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
        }
        .comment {
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 10px;
            padding: 20px;
        }
        .comment:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .like-button, .like-comment-button {
            background: none;
            border: none;
            color: #007BFF;
            cursor: pointer;
            text-align: right;
            margin-left: 1250px;
        }
        .like-button.liked, .like-comment-button.liked {
            color: #FF6600;
        }
        .edit-comment, .delete-comment {
            cursor: pointer;
            color: #4f5964;
        }
        .text{
            width: 1350px;
            resize: none;
            border-radius: 5px;
            padding: 10px;
        }
        .subouton{
            background-color: #007BFF;
            padding: 20px;
            border-radius: 5px;
            border: none;
            color: white;
            display: inline-block;
            font-weight: bold;
            margin-bottom: 50px;
            cursor: pointer;
        }
        .subouton:hover {
            background-color: #0056b3;
        }
        .download-button{
            margin-left: 500px;
        }
        .navigation{
            padding: 10px 20px;
            background-color: #FF6600;
            border-radius: 5px;
            color: white;
        }
        .navigation a{
            font-weight: bolder;
            text-decoration: none;
            color: white;
        }
        .contenu_idee{
            padding: 20px;
            border-radius: 5px;
            background-color: #e3e5e7;
            margin-bottom: 50px;
        }
        .edit-comment-form textarea{
            width: 500px;
            resize: none;
            padding: 10px;
            border-radius: 5px;
        }
        .delete-comment-form button, .edit-comment-form button{
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .delete-comment-form button{
            background-color: red;
        }

        .edit-comment-form button{
            background-color: green;
            margin-top: 10px;
        }

        .delete-comment-form button:hover{
            background-color: rgb(189, 1, 1);
        }

        .edit-comment-form button:hover{
            background-color: rgb(1, 97, 1);
        }
        .comment-actions {
            display: flex;
            align-items: center;
        }

    </style>
</head>
<body>
<div class="header">
    <div class="logo" onclick="location.href='../accueil.html'">
        <img src="../../static/img/icon.png" alt="Logo">
        <div>
            <h1>Orange</h1>
            <h3><span class="for-ideas">for ideas</span></h3>
        </div>
    </div>
    <div class="navigation">
        <i class="fas fa-arrow-left"></i>
        <a href="IdeePublique.php">Retour</a>
    </div>
</div>
<div class="container">
    <div class="idea-details">
        <div class="creator-info">
                <img src="<?php echo $idee['photo_profil']; ?>" alt="Photo de profil">
                <div class="name"><?php echo $idee['prenom'] . ' ' . $idee['nom']; ?></div>
        </div>
        <div class="idea-dates">
            <div>Créée le : <?php echo $idee['date_creation']; ?></div>
            <div>Modifiée le : <?php echo $idee['date_modification']; ?></div>
        </div>
        <h1>Titre : <?php echo $idee['titre']; ?></h1><br>
        <div class="contenu_idee">
            Contenu : <p><?php echo nl2br($idee['contenu_idee']); ?></p><br>
        </div>
        <div class="status <?php echo strtolower($idee['statut']); ?>">
            Statut: <?php echo ucfirst($idee['statut']); ?>
        </div><br><br>
        <p><strong>Catégorie:</strong> <?php echo htmlspecialchars($idee['nom_categorie']); ?></p><br>
        <?php if ($idee['nom_fichier']) : 
            $file = "../../database/idee/" . $idee['contenu_fichier'];
            $file_extension = pathinfo($file, PATHINFO_EXTENSION);
        ?>
            <?php if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'jpe', 'avi'])) : ?>
                <img src="<?php echo $file; ?>" alt="Fichier" style="max-width: 600px;"><br>
                <a class="download-button" href="<?php echo $file; ?>" download>Télécharger</a>
            <?php elseif (in_array($file_extension, ['pdf'])) : ?>
            <embed src="<?php echo $file; ?>" type="application/pdf" width="600" height="600"><br>
            <a class="download-button" href="<?php echo $file; ?>" download>Télécharger</a>
            <?php elseif (in_array($file_extension, ['doc', 'docx', 'mpp', 'gz', 'zip', 'odp', 'odt', 'ods', 'xlsx', 'pptx', 'txt'])) : ?>
            <p><a href="<?php echo $file; ?>" target="_blank">Voir le document</a></p><br>
            <a class="download-button" href="<?php echo $file; ?>" download>Télécharger</a>
            <?php else : ?>
                <p>Type de fichier non supporté pour l'affichage</p>
            <?php endif; ?>
            <br>
        <?php endif; ?>

        <p>
            <button class="like-button <?php echo $idee['user_liked'] ? 'liked' : ''; ?>" onclick="toggleLike()">
                <i class="fas fa-thumbs-up"></i> <span id="like-count"><?php echo $idee['like_count']; ?></span>
            </button>
        </p>
    </div>

    <div class="comments-section">
        <h3>Commentaires (<?php echo $idee['comment_count']; ?>)</h3>
        <form method="POST">
            <textarea class="text" name="comment_content" rows="10" cols="50" placeholder="Ajouter un commentaire" required></textarea><br>
            <button class="subouton" type="submit"> Ajouter un commentaire</button>
        </form>
        <?php foreach ($comments as $comment): ?>
            <div class="comment" id="comment-<?php echo $comment['id_commentaire']; ?>">
            <div class="creator-info">
            <img src="<?php echo $comment['photo_profil']; ?>" alt="Photo de profil">
            <div class="name"><?php echo $comment['prenom'] . ' ' . $comment['nom']; ?></div>
        </div>
                <p><?php echo nl2br(htmlspecialchars($comment['contenu'])); ?></p>
                <div class="idea-dates">
                    <p><small>Créée le : <?php echo htmlspecialchars($comment['date_creation']); ?></small></p>
                    <?php if ($comment['date_modification']): ?>
                        <p><small>Modifiée le : <?= htmlspecialchars($comment['date_modification']) ?></small></p>
                    <?php endif; ?>
                </div>
                <button class="like-comment-button <?php echo $comment['user_liked'] ? 'liked' : ''; ?>" data-comment-id="<?php echo $comment['id_commentaire']; ?>" onclick="toggleLikeComment(<?php echo $comment['id_commentaire']; ?>)">
                    <i class="fas fa-thumbs-up"></i> <span id="like-comment-count-<?php echo $comment['id_commentaire']; ?>"><?php echo $comment['like_count']; ?></span> J'aime
                </button>
                <?php if ($comment['comment_employe_id'] == $employe_id): ?>
                    <div class="comment-actions">
                        <form method="post" class="edit-comment-form">
                            <input type="hidden" name="edit_comment_id" value="<?= $comment['id_commentaire'] ?>">
                            <textarea name="new_content" rows="2" required><?= htmlspecialchars($comment['contenu']) ?></textarea>
                            <button type="submit">Modifier</button>
                        </form>

                        <form method="post" class="delete-comment-form">
                            <input type="hidden" name="delete_comment_id" value="<?= $comment['id_commentaire'] ?>">
                            <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')">Supprimer</button>
                        </form>
                    </div>
                    <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="espace"></div>
    <div class="footer">
        <h4 class="footer-left"><a href="mailto:support@orange.com" style="text-decoration: none; color: white;">Contact</a></h4>
        <h4 class="footer-right">© Orange/Juin2024</h4>
    </div>

<script>
function toggleLike() {
    var xhr = new XMLHttpRequest();
    var url = window.location.href;
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                var likeButton = document.querySelector(".like-button");
                var likeCount = document.getElementById("like-count");
                if (likeButton.classList.contains("liked")) {
                    likeButton.classList.remove("liked");
                    likeCount.textContent = parseInt(likeCount.textContent) - 1;
                } else {
                    likeButton.classList.add("liked");
                    likeCount.textContent = parseInt(likeCount.textContent) + 1;
                }
            }
        }
    };
    xhr.send("like=" + (document.querySelector(".like-button").classList.contains("liked") ? "false" : "true"));
}

function toggleLikeComment(commentId) {
    var xhr = new XMLHttpRequest();
    var url = window.location.href;
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                var likeButton = document.querySelector(".like-comment-button[data-comment-id='" + commentId + "']");
                var likeCount = document.getElementById("like-comment-count-" + commentId);
                if (likeButton.classList.contains("liked")) {
                    likeButton.classList.remove("liked");
                    likeCount.textContent = parseInt(likeCount.textContent) - 1;
                } else {
                    likeButton.classList.add("liked");
                    likeCount.textContent = parseInt(likeCount.textContent) + 1;
                }
            }
        }
    };
    xhr.send("like_comment=" + (document.querySelector(".like-comment-button[data-comment-id='" + commentId + "']").classList.contains("liked") ? "false" : "true") + "&comment_id=" + commentId);
}

function editComment(commentId) {
    var commentContent = document.getElementById("comment-content-" + commentId).textContent.trim();
    var newContent = prompt("Modifier le commentaire :", commentContent);
    if (newContent !== null && newContent.trim() !== "") {
        var xhr = new XMLHttpRequest();
        var url = window.location.href;
        xhr.open("POST", url, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                window.location.reload(); // Recharger la page après modification
            }
        };
        xhr.send("edit_comment_id=" + commentId + "&new_content=" + encodeURIComponent(newContent));
    }
}

// Fonction pour supprimer un commentaire
function deleteComment(commentId) {
    var confirmDelete = confirm("Voulez-vous vraiment supprimer ce commentaire ?");
    if (confirmDelete) {
        var xhr = new XMLHttpRequest();
        var url = window.location.href;
        xhr.open("POST", url, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                window.location.reload(); // Recharger la page après suppression
            }
        };
        xhr.send("delete_comment_id=" + commentId);
    }
}

document.getElementById('likeForm').addEventListener('submit', function(e) 
        {
            e.preventDefault();
            var formData = new FormData(this);
            fetch('', 
            {
                method: 'POST',
                body: formData
            }).then(response => response.json())
            .then(data => 
            {
                if (data.success) 
                {
                    location.reload();
                }
            });
        });

        document.querySelectorAll('.likeCommentForm').forEach(function(form) 
        {
            form.addEventListener('submit', function(e) 
            {
                e.preventDefault();
                var formData = new FormData(this);
                fetch('', 
                {
                    method: 'POST',
                    body: formData
                }).then(response => response.json())
                .then(data => 
                {
                    if (data.success) 
                    {
                        location.reload();
                    }
                });
            });
        });

        function toggleEditForm(commentId) {
        var form = document.querySelector(`.edit-comment-form input[value='${commentId}']`).parentNode;
        if (form.style.display === "none") {
            form.style.display = "block";
        } else {
            form.style.display = "none";
        }
    }

    document.querySelectorAll('.likeCommentForm').forEach(function(form) {
        form.addEventListener('submit', function(e) 
        {
            e.preventDefault();
            var formData = new FormData(this);
            fetch('',
            {
                method: 'POST',
                body: formData
            }).then(response => response.json())
            .then(data => 
            {
                if (data.success) 
                {
                    location.reload();
                }
            });
        });
    });
</script>
</body>
</html>