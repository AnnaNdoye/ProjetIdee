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
    (SELECT COUNT(*) FROM LikeCommentaire WHERE commentaire_id = commentaire.id_commentaire AND employe_id = ?) AS user_liked
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

// Gérer les likes
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

//modifier commentaires
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


//supprimer commenaires
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id'])) {
    $delete_comment_id = $_POST['delete_comment_id'];
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
            max-width: 1500px;
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
        }
        .status.soumis { background: #ffecb3; }
        .status.approuvé { background: #c8e6c9; }
        .status.rejeté { background: #ffcdd2; }
        .status.implémenté { background: #b3e5fc; }
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
        <strong><a href="IdeePublique.php">Retour</a></strong>
    </div>
</div>
    <div class="container">
        <div class="idea-details">
            <div class="creator-info">
                <img src="<?php echo $idee['photo_profil']; ?>" alt="Photo de profil">
                <div class="name"><?php echo $idee['prenom'] . ' ' . $idee['nom']; ?></div>
            </div>
            <div class="idea-dates">
                <div>Créé le: <?php echo $idee['date_creation']; ?></div>
                <div>Dernière modification: <?php echo $idee['date_modification']; ?></div>
            </div>
            <h1><?php echo $idee['titre']; ?></h1>
            <p><?php echo nl2br($idee['contenu_idee']); ?></p>
            <?php if ($idee['nom_fichier']): ?>
                <div>
                    <a href="data:<?php echo $idee['type']; ?>;base64,<?php echo base64_encode($idee['contenu_fichier']); ?>" download="<?php echo $idee['nom_fichier']; ?>">Télécharger le fichier associé</a>
                </div>
            <?php endif; ?>
            <div class="like-container">
                <form id="likeForm" method="post">
                    <input type="hidden" name="like" value="<?php echo $idee['user_liked'] ? 'false' : 'true'; ?>">
                    <button type="submit" class="like-button">
                        <i class="fas fa-thumbs-up" style="color:<?php echo $idee['user_liked'] ? 'blue' : 'grey'; ?>;"></i>
                    </button>
                </form>
                <span class="like-count"><?php echo $idee['like_count']; ?></span>
            </div>
            <div class="status <?php echo strtolower($idee['statut']); ?>">
                Statut: <?php echo ucfirst($idee['statut']); ?>
            </div>
        </div>
        <div class="comments-section">
            <h2>Commentaires (<?php echo $idee['comment_count']; ?>)</h2>
            <?php foreach ($comments as $comment): ?>
    <div class="comment">
        <div class="creator-info">
            <img src="<?php echo $comment['photo_profil']; ?>" alt="Photo de profil">
            <div class="name"><?php echo $comment['prenom'] . ' ' . $comment['nom']; ?></div>
        </div>
        <p><?php echo nl2br($comment['contenu']); ?></p>
        <div class="like-container">
            <form class="likeCommentForm" method="post">
                <input type="hidden" name="comment_id" value="<?php echo $comment['id_commentaire']; ?>">
                <input type="hidden" name="like_comment" value="<?php echo $comment['user_liked'] ? 'false' : 'true'; ?>">
                <button type="submit" class="like-button">
                    <i class="fas fa-thumbs-up" style="color:<?php echo $comment['user_liked'] ? 'blue' : 'grey'; ?>;"></i>
                </button>
            </form>
            <span class="like-count"><?php echo $comment['like_count']; ?></span>
        </div>
        <?php if (isset($comment['employe_id']) && $comment['employe_id'] == $employe_id): ?>
            <form method="post" class="edit-comment-form" style="display:none;">
                <input type="hidden" name="edit_comment_id" value="<?php echo $comment['id_commentaire']; ?>">
                <textarea name="new_content" rows="3"><?php echo htmlspecialchars($comment['contenu']); ?></textarea>
                <button type="submit">Enregistrer</button>
            </form>
            <button class="edit-button" onclick="toggleEditForm(<?php echo $comment['id_commentaire']; ?>)">Modifier</button>
            <form method="post" style="display:inline;">
                <input type="hidden" name="delete_comment_id" value="<?php echo $comment['id_commentaire']; ?>">
                <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?');">Supprimer</button>
            </form>
        <?php endif; ?>
        <div class="comment-dates">
            <div>Créé le: <?php echo $comment['date_creation']; ?></div>
            <?php if ($comment['date_modification']): ?>
                <div>Modifié le: <?php echo $comment['date_modification']; ?></div>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

            <form method="post">
                <label for="comment_content">Ajouter un commentaire:</label>
                <textarea name="comment_content" id="comment_content" rows="4" required></textarea>
                <button type="submit">Envoyer</button>
            </form>
        </div>
    </div>
<div class="espace"></div>
    <div class="footer">
        <h4 class="footer-left"><a href="mailto:support@orange.com" style="text-decoration: none; color: white;">Contact</a></h4>
        <h4 class="footer-right">© Orange/Juin2024</h4>
    </div>
    <script>
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