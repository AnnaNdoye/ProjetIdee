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

// Récupérer les départements depuis la base de données
$query = "SELECT id_departement, nom_departement FROM Department";
$result = $connection->query($query);

$departmentArray = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departmentArray[] = $row;
    }
}

// Gérer l'ajout de département
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_department'])) {
    $nomDepartement = $_POST['new_nom_departement'];

    $insertQuery = "INSERT INTO Department (nom_departement) VALUES (?)";
    $stmt = $connection->prepare($insertQuery);
    $stmt->bind_param("s", $nomDepartement);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Erreur lors de l'ajout du département : " . $stmt->error;
    }

    $stmt->close();
}

// Gérer la mise à jour de département
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_department'])) {
    $idDepartement = $_POST['id_departement'];
    $nomDepartement = $_POST['nom_departement'];

    $updateQuery = "UPDATE Department SET nom_departement = ? WHERE id_departement = ?";
    $stmt = $connection->prepare($updateQuery);
    $stmt->bind_param("si", $nomDepartement, $idDepartement);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Erreur lors de la mise à jour du département : " . $stmt->error;
    }

    $stmt->close();
}

// Gérer la suppression de département
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_department'])) {
    $idDepartement = $_POST['id_departement'];

    $deleteQuery = "DELETE FROM Department WHERE id_departement = ?";
    $stmt = $connection->prepare($deleteQuery);
    $stmt->bind_param("i", $idDepartement);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Erreur lors de la suppression du département : " . $stmt->error;
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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png" href="../../static/img/icon.png">
    <link rel="stylesheet" href="../../static/css/style1.css">
    <link rel="stylesheet" href="../../static/css/style5.css">
    <title>Départements</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        th{
            background-color: #ff6600;
            color: white;
        }

        th:hover{
            color: #000;
        }

        th, td {
            border: 3px solid #ddd;
            padding: 16px;
            text-align: center;
        }

        input[type="text"] {
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

        .add-department-btn, .return-home-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 20px 10px;
        }

        .add-department-btn {
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

        .return-home-btn:hover{
            background-color: #565e67;
        }

        .add-department-btn:hover{
            background-color: #005abb;
        }

        .button-group .update:hover{
            background-color: rgb(0, 115, 15);
        }

        .button-group .delete:hover{
            background-color: #930000;
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
        <button class="add-department-btn" id="add-department-btn-top">Ajouter Département</button>
    </div>

    <div>
        <h2>Liste des Départements</h2>
    </div>

    <div class="main-content">
        <table>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Action</th>
            </tr>
            <?php foreach ($departmentArray as $department): ?>
            <tr>
                <td><?php echo $department['id_departement']; ?></td>
                <td><input type="text" name="nom_departement" value="<?php echo $department['nom_departement']; ?>"></td>
                <td>
                    <div class="button-group">
                        <button class="update" data-id="<?php echo $department['id_departement']; ?>">Mettre à jour</button>
                        <button class="delete" data-id="<?php echo $department['id_departement']; ?>">Supprimer</button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="button-container">
        <a class="return-home-btn" href="AccueilAdmin.php"><i class="fas fa-arrow-left"></i>Retour à l'accueil</a>
        <button class="add-department-btn" id="add-department-btn-bottom">Ajouter Département</button>
    </div>

    <div class="footer">
        <h4 class="footer-left"><a href="mailto:support@orange.com" style="text-decoration: none; color: white;">Contact</a></h4>
        <h4 class="footer-right">© Orange/Juin2024</h4>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addDepartmentBtns = document.querySelectorAll('#add-department-btn-top, #add-department-btn-bottom');
            const table = document.querySelector('table');

            addDepartmentBtns.forEach(button => {
                button.addEventListener('click', function () {
                    const newRow = document.createElement('tr');
                    newRow.innerHTML = `
                        <td>New</td>
                        <td><input type="text" name="new_nom_departement" placeholder="Nom du département"></td>
                        <td>
                            <div class="button-group">
                                <button class="update">Ajouter</button>
                                <button class="delete">Annuler</button>
                            </div>
                        </td>
                    `;
                    table.appendChild(newRow);
                    newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });

                    const updateButton = newRow.querySelector('.update');
                    const deleteButton = newRow.querySelector('.delete');

                    updateButton.addEventListener('click', function () {
                        const nom = newRow.querySelector('input[name="new_nom_departement"]').value;

                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.style.display = 'none';

                        const nomInput = document.createElement('input');
                        nomInput.type = 'hidden';
                        nomInput.name = 'new_nom_departement';
                        nomInput.value = nom;
                        form.appendChild(nomInput);

                        const addDepartmentInput = document.createElement('input');
                        addDepartmentInput.type = 'hidden';
                        addDepartmentInput.name = 'add_department';
                        addDepartmentInput.value = '1';
                        form.appendChild(addDepartmentInput);

                        document.body.appendChild(form);
                        form.submit();
                    });

                    deleteButton.addEventListener('click', function () {
                        newRow.remove();
                    });
                });
            });

            // Gérer la mise à jour et la suppression
            document.querySelectorAll('.update').forEach(button => {
                button.addEventListener('click', function () {
                    const row = button.closest('tr');
                    const id = button.dataset.id;
                    const nom = row.querySelector('input[name="nom_departement"]').value;

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.style.display = 'none';

                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'id_departement';
                    idInput.value = id;
                    form.appendChild(idInput);

                    const nomInput = document.createElement('input');
                    nomInput.type = 'hidden';
                    nomInput.name = 'nom_departement';
                    nomInput.value = nom;
                    form.appendChild(nomInput);

                    const updateDepartmentInput = document.createElement('input');
                    updateDepartmentInput.type = 'hidden';
                    updateDepartmentInput.name = 'update_department';
                    updateDepartmentInput.value = '1';
                    form.appendChild(updateDepartmentInput);

                    document.body.appendChild(form);
                    form.submit();
                });
            });

            document.querySelectorAll('.delete').forEach(button => {
                button.addEventListener('click', function () {
                    if (confirm('Êtes-vous sûr de vouloir supprimer ce département ?')) {
                        const id = button.dataset.id;

                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.style.display = 'none';

                        const idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id_departement';
                        idInput.value = id;
                        form.appendChild(idInput);

                        const deleteDepartmentInput = document.createElement('input');
                        deleteDepartmentInput.type = 'hidden';
                        deleteDepartmentInput.name = 'delete_department';
                        deleteDepartmentInput.value = '1';
                        form.appendChild(deleteDepartmentInput);

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
</body>
</html>
