<?php
session_start();

// Vérifiez si l'utilisateur est connecté en tant qu'administrateur, sinon redirigez vers la page de connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: ConnexionAdmin.php");
    exit();
}

// Connexion à la base de données
$host = "localhost";
$user = "root";
$password = "";
$database = "idee";

$connection = new mysqli($host, $user, $password, $database);

if ($connection->connect_error) {
    die("Erreur de connexion à la base de données : " . $connection->connect_error);
}

// Récupérer les idées depuis la base de données
$query = "
    SELECT idee.id_idee, idee.titre, idee.contenu_idee, idee.date_creation, idee.date_modification, idee.statut,
    categorie.nom_categorie
    FROM idee
    JOIN categorie ON idee.categorie_id = categorie.id_categorie
    WHERE idee.est_publique = 1
    ";

$result = $connection->query($query);

$ideearray = array();

if ($result->num_rows > 0) 
{
    while ($row = $result->fetch_assoc()) 
    {
        $ideearray[] = $row;
    }
}

// Gérer la mise à jour de statut
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_idee'])) {
    $statut = $_POST['statut'];
    $idIdee = $_POST['id_idee'];

    $updateQuery = "UPDATE idee SET statut = ? WHERE id_idee = ?";
    $stmt = $connection->prepare($updateQuery);
    $stmt->bind_param("si", $statut, $idIdee);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Erreur lors du changement de statut de l'idée : " . $stmt->error;
    }

    $stmt->close();
}

// Gérer la suppression de l'idée
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_idee'])) {
    $idIdee = $_POST['id_idee'];

    // Supprimer les likes associés aux commentaires de l'idée
    $deleteLikeCommentQuery = "
        DELETE lc FROM likecommentaire lc
        INNER JOIN commentaire c ON lc.commentaire_id = c.id_commentaire
        WHERE c.idee_id = ?";
    $stmt = $connection->prepare($deleteLikeCommentQuery);
    $stmt->bind_param("i", $idIdee);
    $stmt->execute();
    $stmt->close();

    // Supprimer les commentaires associés à l'idée
    $deleteCommentQuery = "DELETE FROM commentaire WHERE idee_id = ?";
    $stmt = $connection->prepare($deleteCommentQuery);
    $stmt->bind_param("i", $idIdee);
    $stmt->execute();
    $stmt->close();

    // Supprimer les likes associés à l'idée
    $deleteLikeIdeeQuery = "DELETE FROM likeidee WHERE idee_id = ?";
    $stmt = $connection->prepare($deleteLikeIdeeQuery);
    $stmt->bind_param("i", $idIdee);
    $stmt->execute();
    $stmt->close();

    // Supprimer les fichiers associés
    $fileQuery = "SELECT contenu_fichier FROM fichier WHERE idee_id = ?";
    $stmt = $connection->prepare($fileQuery);
    $stmt->bind_param("i", $idIdee);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $filePath = $row['contenu_fichier'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $stmt->close();

    // Supprimer les enregistrements de fichiers associés
    $deleteFileQuery = "DELETE FROM fichier WHERE idee_id = ?";
    $stmt = $connection->prepare($deleteFileQuery);
    $stmt->bind_param("i", $idIdee);
    $stmt->execute();
    $stmt->close();

    // Supprimer l'idée
    $deleteQuery = "DELETE FROM idee WHERE id_idee = ?";
    $stmt = $connection->prepare($deleteQuery);
    $stmt->bind_param("i", $idIdee);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Erreur lors de la suppression de l'idée : " . $stmt->error;
    }

    $stmt->close();
}

$connection->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png" href="../../static/img/icon.png">
    <link rel="stylesheet" href="../../static/css/style1.css">
    <link rel="stylesheet" href="../../static/css/style5.css">
    <link rel="stylesheet" href="../../static/css/StatutIdee.css">
    <title>Catégories</title>
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
        <div class="connect_entete">
            <a href="ConnexionAdmin.php">
                <i class="fas fa-user"></i>
                <span>Se déconnecter</span>
            </a>
        </div>
    </div>

    <div class="button-container">
        <a class="return-home-btn" href="AccueilAdmin.php"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>
    </div>

    <div>
        <h2>Statuts des idées</h2>
    </div>

    <div class="main-content">
        <table>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Contenu</th>
                <th>Date de création</th>
                <th>Date de modification</th>
                <th>Statut</th>
                <th>Categorie</th>
                <th class="large">Actions</th>
            </tr>
            <?php foreach ($ideearray as $idee): ?>
            <tr>
                <td><?php echo $idee['id_idee']; ?></td>
                <td><?php echo $idee['titre']; ?></td>
                <td class="noncentre"><?php echo $idee['contenu_idee']; ?></td>
                <td><?php echo $idee['date_creation']; ?></td>
                <td><?php echo $idee['date_modification']; ?></td>
                <td class="statuts statut-<?php echo strtolower($idee['statut']); ?>"><?php echo $idee['statut']; ?></td>
                <td><?php echo $idee['nom_categorie']; ?></td>
                <td class="action">
                    <div class="button-group">
                        <form method="POST" action="">
                            <input type="hidden" name="id_idee" value="<?php echo $idee['id_idee']; ?>">
                            <select name="statut">
                                <option value="Soumis" <?php echo $idee['statut'] == 'Soumis' ? 'selected' : ''; ?>>Soumis</option>
                                <option value="Approuvé" <?php echo $idee['statut'] == 'Approuvé' ? 'selected' : ''; ?>>Approuvé</option>
                                <option value="Rejeté" <?php echo $idee['statut'] == 'Rejeté' ? 'selected' : ''; ?>>Rejeté</option>
                                <option value="Implémenté" <?php echo $idee['statut'] == 'Implémenté' ? 'selected' : ''; ?>>Implémenté</option>
                            </select>
                            <button type="submit" name="update_idee" class="update">Mettre à jour</button>
                        </form>
                        <form method="POST" action="" onsubmit="return confirmDelete();">
                            <input type="hidden" name="id_idee" value="<?php echo $idee['id_idee']; ?>">
                            <button type="submit" name="delete_idee" class="delete">Supprimer l'idée</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="button-container">
        <a class="return-home-btn" href="AccueilAdmin.php"><i class="fas fa-arrow-left"></i>Retour à l'accueil</a>
    </div>

    <?php
        include("../barrefooter.html");
    ?>

    <script>
            // Gérer la mise à jour et la suppression
            document.querySelectorAll('.update').forEach(button => 
            {
                button.addEventListener('click', function () 
                {
                    const row = button.closest('tr');
                    const id = button.dataset.id;
                    const statut = row.querySelector('select[name="statut"]').value;

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.style.display = 'none';

                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'id_idee';
                    idInput.value = id;
                    form.appendChild(idInput);

                    const statutInput = document.createElement('input');
                    statutInput.type = 'hidden';
                    statutInput.name = 'statut';
                    statutInput.value = statut;
                    form.appendChild(statutInput);

                    const updateideeInput = document.createElement('input');
                    updateideeInput.type = 'hidden';
                    updateideeInput.name = 'update_idee';
                    updateideeInput.value = '1';
                    form.appendChild(updateideeInput);

                    document.body.appendChild(form);
                    form.submit();
                });
            });

            document.querySelectorAll('.delete').forEach(button => 
            {
                button.addEventListener('click', function () 
                {
                    const id = button.dataset.id;

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.style.display = 'none';

                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'id_idee';
                    idInput.value = id;
                    form.appendChild(idInput);

                    const deleteideeInput = document.createElement('input');
                    deleteideeInput.type = 'hidden';
                    deleteideeInput.name = 'delete_idee';
                    deleteideeInput.value = '1';
                    form.appendChild(deleteideeInput);

                    document.body.appendChild(form);
                    form.submit();
                });
            });
        function confirmDelete() 
        {
            return confirm("Êtes-vous sûr de vouloir supprimer cette idée ?");
        }
    </script>
</body>
</html>

