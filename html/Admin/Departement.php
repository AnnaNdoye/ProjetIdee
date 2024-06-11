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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png" href="../../static/img/icon.png">
    <link rel="stylesheet" href="../../static/css/style1.css">
    <link rel="stylesheet" href="../../static/css/style5.css">
    <title>Départements</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
            table-layout: fixed; /* Ajouté pour fixer la taille des colonnes */
        }

        th, td {
            border: 1px solid #ddd;
            padding: 16px; /* Agrandi la taille des cellules */
            text-align: left;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px; /* Agrandi les champs de saisie */
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
            margin-right: 10px; /* Ajoute un espacement entre les boutons */
        }

        .button-group button.update {
            background-color: #4CAF50;
            color: white;
        }

        .button-group button.cancel {
            background-color: #f44336;
            color: white;
        }

        .add-department-btn, .return-home-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 20px 10px; /* Espacement entre les boutons */
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
                        <button class="cancel">Annuler</button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="button-container">
        <a class="return-home-btn" href="AccueilAdmin.php"><i class="fas fa-arrow-left"></i>Retour à l'accueil</a>
        <button class="add-department-btn" id="add-department-btn">Ajouter Département</button>
    </div>

    <div class="footer">
        <h4 class="footer-left"><a href="mailto:support@orange.com" style="text-decoration: none; color: white;">Contact</a></h4>
        <h4 class="footer-right">© Orange/Juin2024</h4>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addDepartmentBtn = document.getElementById('add-department-btn');
            const table = document.querySelector('table');

            addDepartmentBtn.addEventListener('click', function () {
                const newRow = document.createElement('tr');
                newRow.innerHTML = `
                    <td>New</td>
                    <td><input type="text" name="new_nom_departement" placeholder="Nom du département"></td>
                    <td>
                        <div class="button-group">
                            <button class="update">Ajouter</button>
                            <button class="cancel">Annuler</button>
                        </div>
                    </td>
                `;
                table.appendChild(newRow);
                const updateButton = newRow.querySelector('.update');
            const cancelButton = newRow.querySelector('.cancel');

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

            cancelButton.addEventListener('click', function () {
                newRow.remove();
            });
        });
    });
</script>

