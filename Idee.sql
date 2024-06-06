CREATE DATABASE idee;

USE idee;

CREATE TABLE Department(
    id_departement INT PRIMARY KEY AUTO_INCREMENT,
    nom_departement VARCHAR(100)
);

CREATE TABLE Employe(
    id INT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    sexe ENUM('Masculin', 'Féminin') NOT NULL,
    poste VARCHAR(50),
    departement_id VARCHAR(50),
    FOREIGN KEY(departement_id) REFERENCES Departement(id_departement)
);

CREATE TABLE Categorie (
    id_categorie INTEGER PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50) NOT NULL,
    description VARCHAR(255)
);

CREATE TABLE Idee (
    id_idee INTEGER PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(100) NOT NULL,
    description TEXT,
    est_publique BOOLEAN NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME ON UPDATE CURRENT_TIMESTAMP,
    employe_id INTEGER,
    categorie_id INTEGER,
    statut ENUM(''),
    FOREIGN KEY (employe_id) REFERENCES Employe(id_employe),
    FOREIGN KEY (categorie_id) REFERENCES Categorie(id_categorie)
);

CREATE TABLE Fichier (
    id_fichier INTEGER PRIMARY KEY AUTO_INCREMENT,
    NomFichier VARCHAR(255) NOT NULL,
    Type ENUM('excel', 'pdf', 'image'),
    taille DOUBLE,
    idee_id INTEGER,
    FOREIGN KEY (idee_id) REFERENCES Idee(id_idee)
);

CREATE TABLE Commentaire (
    id_commentaire INTEGER PRIMARY KEY AUTO_INCREMENT,
    contenu TEXT NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME ON UPDATE CURRENT_TIMESTAMP,
    employe_id INTEGER,
    idee_id INTEGER,
    FOREIGN KEY (employe_id) REFERENCES Employe(id_employe),
    FOREIGN KEY (idee_id) REFERENCES Idee(id_idee)
);