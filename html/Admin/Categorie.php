<?php
session_start();

// Vérifiez si l'utilisateur est connecté en tant qu'administrateur, sinon redirigez vers la page de connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: ../connexion.php");
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

// Récupérer les catégories depuis la base de données
$query = "SELECT id_categorie, nom_categorie, description_categorie FROM Categorie";
$result = $connection->query($query);

$categorieArray = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categorieArray[] = $row;
    }
}

// Gérer l'ajout de catégorie
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $nomCategorie = $_POST['new_nom_categorie'];
    $descriptionCategorie = $_POST['new_description_categorie'];

    $insertQuery = "INSERT INTO Categorie (nom_categorie, description_categorie) VALUES (?, ?)";
    $stmt = $connection->prepare($insertQuery);
    $stmt->bind_param("ss", $nomCategorie, $descriptionCategorie);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Erreur lors de l'ajout de la catégorie : " . $stmt->error;
    }

    $stmt->close();
}

// Gérer la mise à jour de catégorie
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_category'])) {
    $idCategorie = $_POST['id_categorie'];
    $nomCategorie = $_POST['nom_categorie'];
    $descriptionCategorie = $_POST['description_categorie'];

    $updateQuery = "UPDATE Categorie SET nom_categorie = ?, description_categorie = ? WHERE id_categorie = ?";
    $stmt = $connection->prepare($updateQuery);
    $stmt->bind_param("ssi", $nomCategorie, $descriptionCategorie, $idCategorie);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Erreur lors de la mise à jour de la catégorie : " . $stmt->error;
    }

    $stmt->close();
}

// Gérer la suppression de catégorie
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_category'])) {
    $idCategorie = $_POST['id_categorie'];

    $deleteQuery = "DELETE FROM Categorie WHERE id_categorie = ?";
    $stmt = $connection->prepare($deleteQuery);
    $stmt->bind_param("i", $idCategorie);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Erreur lors de la suppression de la catégorie : " . $stmt->error;
    }

    $stmt->close();
}

$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png" href="../../static/img/icon.png">
    <link rel="stylesheet" type="text/css" href="../../static/css/style1.css">
    <link rel="stylesheet" type="text/css" href="../../static/css/style5.css">
    <title>Catégories</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 16px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        th.description-col, td.description-col {
            width: 50%;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            margin-top: 8px;
            margin-bottom: 16px;
            resize: vertical;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .button-group button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }

        .button-group button.update {
            background-color: #4CAF50;
            color: white;
        }

        .button-group button.delete {
            background-color: #f44336;
            color: white;
        }

        .add-category-btn, .return-home-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 20px 10px;
        }

        .add-category-btn {
            background-color: #007BFF;
            color: white;
        }

        .return-home-btn {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
        }

        .return-home-btn i {
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
        <div class="connect_entete">
            <a href="../connexion.php">
                <i class="fas fa-user"></i>
                <span>Se déconnecter</span>
            </a>
        </div>
    </div>

    <div class="button-container">
        <a class="return-home-btn" href="AccueilAdmin.php"><i class="fas fa-arrow-left"></i>Retour à l'accueil</a>
        <button class="add-category-btn" id="add-category-btn">Ajouter Catégorie</button>
    </div>

    <div>
        <h2>Liste des Catégories</h2>
    </div>

    <div class="main-content">
        <table>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th class="description-col">Description</th>
                <th>Action</th>
            </tr>
            <?php foreach ($categorieArray as $categorie): ?>
            <tr>
                <td><?php echo $categorie['id_categorie']; ?></td>
                <td><input type="text" name="nom_categorie" value="<?php echo $categorie['nom_categorie']; ?>"></td>
                <td class="description-col"><textarea name="description_categorie"><?php echo $categorie['description_categorie']; ?></textarea></td>
                <td>
                    <div class="button-group">
                        <button class="update" data-id="<?php echo $categorie['id_categorie']; ?>">Mettre à jour</button>
                        <button class="delete" data-id="<?php echo $categorie['id_categorie']; ?>">Supprimer</button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="button-container">
        <a class="return-home-btn" href="AccueilAdmin.php"><i class="fas fa-arrow-left"></i>Retour à l'accueil</a>
        <button class="add-category-btn" id="add-category-btn">Ajouter Catégorie</button>
    </div>

    <div class="footer">
        <h4 class="footer-left"><a href="mailto:support@orange.com" style="text-decoration: none; color: white;">Contact</a></h4>
        <h4 class="footer-right">© Orange/Juin2024</h4>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addCategoryBtn = document.getElementById('add-category-btn');
            const table = document.querySelector('table');

            addCategoryBtn.addEventListener('click', function () {
                const newRow = document.createElement('tr');
                newRow.innerHTML = `
                    <td>New</td>
                    <td><input type="text" name="new_nom_categorie" placeholder="Nom de la catégorie"></td>
                    <td class="description-col"><textarea name="new_description_categorie" placeholder="Description de la catégorie"></textarea></td>
                    <td>
                        <div class="button-group">
                            <button class="update">Ajouter</button>
                            <button class="delete">Annuler</button>
                        </div>
                    </td>
                `;
                table.appendChild(newRow);
                newRow.scrollIntoView({ behavior: 'smooth' });

                const updateButton = newRow.querySelector('.update');
                const deleteButton = newRow.querySelector('.delete');

                updateButton.addEventListener('click', function () {
                    const nom = newRow.querySelector('input[name="new_nom_categorie"]').value;
                    const description = newRow.querySelector('textarea[name="new_description_categorie"]').value;

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.style.display = 'none';

                    const nomInput = document.createElement('input');
                    nomInput.type = 'hidden';
                    nomInput.name = 'new_nom_categorie';
                    nomInput.value = nom;
                    form.appendChild(nomInput);

                    const descriptionInput = document.createElement('input');
                    descriptionInput.type = 'hidden';
                    descriptionInput.name = 'new_description_categorie';
                    descriptionInput.value = description;
                    form.appendChild(descriptionInput);

                    const addCategoryInput = document.createElement('input');
                    addCategoryInput.type = 'hidden';
                    addCategoryInput.name = 'add_category';
                    addCategoryInput.value = '1';
                    form.appendChild(addCategoryInput);

                    document.body.appendChild(form);
                    form.submit();
                });

                deleteButton.addEventListener('click', function () {
                    newRow.remove();
                });
            });

            // Gérer la mise à jour et la suppression
            document.querySelectorAll('.update').forEach(button => {
                button.addEventListener('click', function () {
                    const row = button.closest('tr');
                    const id = button.dataset.id;
                    const nom = row.querySelector('input[name="nom_categorie"]').value;
                    const description = row.querySelector('textarea[name="description_categorie"]').value;

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.style.display = 'none';

                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'id_categorie';
                    idInput.value = id;
                    form.appendChild(idInput);

                    const nomInput = document.createElement('input');
                    nomInput.type = 'hidden';
                    nomInput.name = 'nom_categorie';
                    nomInput.value = nom;
                    form.appendChild(nomInput);

                    const descriptionInput = document.createElement('input');
                    descriptionInput.type = 'hidden';
                    descriptionInput.name = 'description_categorie';
                    descriptionInput.value = description;
                    form.appendChild(descriptionInput);

                    const updateCategoryInput = document.createElement('input');
                    updateCategoryInput.type = 'hidden';
                    updateCategoryInput.name = 'update_category';
                    updateCategoryInput.value = '1';
                    form.appendChild(updateCategoryInput);

                    document.body.appendChild(form);
                    form.submit();
                });
            });

            document.querySelectorAll('.delete').forEach(button => {
                button.addEventListener('click', function () {
                    if (!confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')) {
                        return;
                    }

                    const id = button.dataset.id;

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.style.display = 'none';

                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'id_categorie';
                    idInput.value = id;
                    form.appendChild(idInput);

                    const deleteCategoryInput = document.createElement('input');
                    deleteCategoryInput.type = 'hidden';
                    deleteCategoryInput.name = 'delete_category';
                    deleteCategoryInput.value = '1';
                    form.appendChild(deleteCategoryInput);

                    document.body.appendChild(form);
                    form.submit();
                });
            });
        });
    </script>
</body>
</html>
