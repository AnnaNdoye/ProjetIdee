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

// Récupérer les informations de l'idée
$query = "
    SELECT idee.id_idee, idee.titre, idee.contenu_idee, idee.est_publique, idee.date_creation, idee.date_modification, idee.statut,
    categorie.nom_categorie, fichier.nom_fichier, fichier.type, fichier.contenu_fichier,
    employe.nom, employe.prenom, employe.photo_profil
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

$stmt->bind_param('i', $idee_id);
if (!$stmt->execute()) {
    die("Erreur lors de l'exécution de la requête: " . $stmt->error);
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Idée non trouvée ou vous n'êtes pas autorisé à la voir.");
}

$idee = $result->fetch_assoc();

// Récupérer les likes
$query_likes = "SELECT COUNT(*) as like_count FROM LikeIdee WHERE idee_id = ?";
$stmt_likes = $connexion->prepare($query_likes);
$stmt_likes->bind_param('i', $idee_id);
$stmt_likes->execute();
$result_likes = $stmt_likes->get_result();
$like_data = $result_likes->fetch_assoc();
$like_count = $like_data['like_count'];

// Vérifier si l'utilisateur a liké
$query_user_like = "SELECT * FROM LikeIdee WHERE employe_id = ? AND idee_id = ?";
$stmt_user_like = $connexion->prepare($query_user_like);
$stmt_user_like->bind_param('ii', $employe_id, $idee_id);
$stmt_user_like->execute();
$result_user_like = $stmt_user_like->get_result();
$user_liked = $result_user_like->num_rows > 0;

// Récupérer les commentaires
$query_comments = "
    SELECT commentaire.id_commentaire, commentaire.contenu, commentaire.date_creation, employe.nom, employe.prenom, employe.photo_profil
    FROM commentaire
    JOIN employe ON commentaire.employe_id = employe.id_employe
    WHERE commentaire.idee_id = ?
    ORDER BY commentaire.date_creation DESC
";

$stmt_comments = $connexion->prepare($query_comments);
$stmt_comments->bind_param('i', $idee_id);
$stmt_comments->execute();
$result_comments = $stmt_comments->get_result();
$comments = $result_comments->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$connexion->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png" href="../../static/img/icon.png">
    <link rel="stylesheet" href="../../static/css/style1.css">
    <link rel="stylesheet" href="styles.css">
    <title>Voir Idée</title>
    <style>
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }
        .idea-details {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            background: #f9f9f9;
            position: relative;
        }
        .creator-info {
            display: flex;
            align-items: center;
            position: absolute;
            top: 10px;
            right: 10px;
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
            font-weight: bold;
        }
        .status.soumis { background: #ffecb3; }
        .status.approuve { background: #c8e6c9; }
        .status.rejete { background: #ffcdd2; }
        .status.implemente { background: #b3e5fc; }
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
            transition: transform 0.2s ease;
        }
        .like-button:focus {
            outline: none;
        }
        .thumb-icon {
            transition: color 0.2s ease, transform 0.2s ease;
        }
        .like-button:hover .thumb-icon {
            color: #888;
            transform: scale(1.1);
        }
        .liked .thumb-icon {
            color: #007BFF;
            transform: scale(1.2);
        }
        .like-count {
            margin-left: 10px;
            font-size: 1.5rem;
            transition: color 0.2s ease;
        }
        .liked + .like-count {
            color: #007BFF;
        }
        .comments-section {
            margin-top: 40px;
        }
        .comment-form textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            resize: vertical;
            margin-bottom: 10px;
        }
        .comment-form button {
            background: #ff7f00;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .comment-form button:hover {
            background: #e06b00;
        }
        .comment {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            background: #f5f5f5;
        }
        .comment .comment-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .comment .comment-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .comment .comment-info .name {
            font-weight: bold;
        }
        .comment .comment-info .date {
            font-size: 0.9em;
            color: #666;
        }
        .comment .comment-content {
            margin-bottom: 10px;
        }
        .comment .comment-actions {
            text-align: right;
        }
        .comment .comment-actions button {
            background: none;
            border: none;
            cursor: pointer;
            color: #007BFF;
            font-size: 0.9em;
        }
        .comment .comment-actions button:hover {
            text-decoration: underline;
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
            <strong>
                <a href="MesIdees.php"><i class="fas fa-arrow-left"></i> Retour</a>
            </strong>
        </div>
    </div>

    <div class="container">
        <h1>Voir Idée</h1>
        <div class="idea-details">
            <div class="creator-info">
                <img src="data:image/jpeg;base64,<?php echo base64_encode($idee['photo_profil']); ?>" alt="Photo de profil" id="profile-img">
                <div>
                    <span class="name"><?php echo htmlspecialchars($idee['prenom']) . ' ' . htmlspecialchars($idee['nom']); ?></span>
                    <div class="idea-dates">
                        <p><strong>Créé le:</strong> <?php echo htmlspecialchars($idee['date_creation']); ?></p>
                        <p><strong>Modifié le:</strong> <?php echo htmlspecialchars($idee['date_modification']); ?></p>
                    </div>
                </div>
            </div>
            <div class="idea-info">
                <p class="status <?php echo strtolower(htmlspecialchars($idee['statut'])); ?>">
                    <strong>Statut:</strong> <?php echo htmlspecialchars($idee['statut']); ?>
                </p>
                <h2><?php echo htmlspecialchars($idee['titre']); ?></h2>
                <hr>
                <p><?php echo nl2br(htmlspecialchars($idee['contenu_idee'])); ?></p>
                <?php if ($idee['nom_fichier']) : ?>
                    <p><strong>Fichier :</strong> <a href="data:<?php echo htmlspecialchars($idee['type']); ?>;base64,<?php echo base64_encode($idee['contenu_fichier']); ?>" target="_blank"><?php echo htmlspecialchars($idee['nom_fichier']); ?></a></p>
                <?php endif; ?>
                <div class="like-container">
                    <button id="likeButton" class="like-button <?php echo $user_liked ? 'liked' : ''; ?>">
                        <span id="thumbIcon" class="thumb-icon"><i class="<?php echo $user_liked ? 'fas' : 'far'; ?> fa-thumbs-up"></i></span>
                    </button>
                    <span id="likeCount" class="like-count"><?php echo $like_count; ?></span>
                </div>
            </div>
        </div>

        <div class="comments-section">
            <h3>Commentaires</h3>
            <div class="comment-form">
                <textarea id="commentContent" placeholder="Ajouter un commentaire"></textarea>
                <button onclick="addComment()">Envoyer</button>
            </div>
            <div id="comments">
                <?php foreach ($comments as $comment) : ?>
                    <div class="comment" data-comment-id="<?php echo $comment['id_commentaire']; ?>">
                        <div class="comment-info">
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($comment['photo_profil']); ?>" alt="Photo de profil">
                            <div>
                                <span class="name"><?php echo htmlspecialchars($comment['prenom']) . ' ' . htmlspecialchars($comment['nom']); ?></span>
                                <span class="date"><?php echo htmlspecialchars($comment['date_creation']); ?></span>
                            </div>
                        </div>
                        <div class="comment-content">
                            <?php echo nl2br(htmlspecialchars($comment['contenu'])); ?>
                        </div>
                        <div class="comment-actions">
                            <button onclick="editComment(<?php echo $comment['id_commentaire']; ?>)">Modifier</button>
                            <button onclick="deleteComment(<?php echo $comment['id_commentaire']; ?>)">Supprimer</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="espace"></div>
    <div class="footer">
        <h4 class="footer-left"><a href="mailto:support@orange.com" style="text-decoration: none; color: white;">Contact</a></h4>
        <h4 class="footer-right">© Orange/Juin2024</h4>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const likeButton = document.getElementById('likeButton');
            const likeCount = document.getElementById('likeCount');
            const thumbIcon = document.querySelector('.thumb-icon i');
            let isLiked = <?php echo json_encode($user_liked); ?>;
            let count = <?php echo $like_count; ?>;

            likeButton.addEventListener('click', () => {
                fetch('like_idee.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        idee_id: <?php echo $idee_id; ?>
                    })
                }).then(response => response.json()).then(data => {
                    if (data.success) {
                        if (isLiked) {
                            count--;
                            likeButton.classList.remove('liked');
                            thumbIcon.classList.remove('fas');
                            thumbIcon.classList.add('far');
                        } else {
                            count++;
                            likeButton.classList.add('liked');
                            thumbIcon.classList.remove('far');
                            thumbIcon.classList.add('fas');
                        }
                        isLiked = !isLiked;
                        likeCount.textContent = count;
                    } else {
                        alert('Erreur lors de la mise à jour du like.');
                    }
                }).catch(error => {
                    console.error('Erreur:', error);
                });
            });
        });

        function addComment() {
            const commentContent = document.getElementById('commentContent').value;

            fetch('add_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    idee_id: <?php echo $idee_id; ?>,
                    contenu: commentContent
                })
            }).then(response => response.json()).then(data => {
                if (data.success) {
                    const newComment = `
                        <div class="comment" data-comment-id="${data.comment.id_commentaire}">
                            <div class="comment-info">
                                <img src="data:image/jpeg;base64,${data.comment.photo_profil}" alt="Photo de profil">
                                <div>
                                    <span class="name">${data.comment.prenom} ${data.comment.nom}</span>
                                    <span class="date">${data.comment.date_creation}</span>
                                </div>
                            </div>
                            <div class="comment-content">
                                ${data.comment.contenu}
                            </div>
                            <div class="comment-actions">
                                <button onclick="editComment(${data.comment.id_commentaire})">Modifier</button>
                                <button onclick="deleteComment(${data.comment.id_commentaire})">Supprimer</button>
                            </div>
                        </div>
                    `;
                    document.getElementById('comments').insertAdjacentHTML('afterbegin', newComment);
                    document.getElementById('commentContent').value = '';
                } else {
                    alert('Erreur lors de l\'ajout du commentaire.');
                }
            }).catch(error => {
                console.error('Erreur:', error);
            });
        }

        function editComment(commentId) {
            // Logique pour éditer un commentaire
        }

        function deleteComment(commentId) {
            // Logique pour supprimer un commentaire
        }
    </script>
</body>
</html>
