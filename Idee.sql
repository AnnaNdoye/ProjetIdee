--Active: 1718130434832@@127.0.0.1@3306@idee
CREATE DATABASE idee;

USE idee;

CREATE TABLE Department(
    id_departement INTEGER PRIMARY KEY AUTO_INCREMENT,
    nom_departement VARCHAR(100)
);

CREATE TABLE Employe(
    id_employe INTEGER PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    sexe ENUM('Masculin', 'Féminin') NOT NULL,
    poste VARCHAR(50),
    departement_id INTEGER,
    is_admin BOOLEAN,
    photo_profil VARCHAR(255), -- Stockage du chemin de l'image de profil
    FOREIGN KEY(departement_id) REFERENCES Department(id_departement)
);

CREATE TABLE Categorie (
    id_categorie INTEGER PRIMARY KEY AUTO_INCREMENT,
    nom_categorie VARCHAR(50) NOT NULL,
    description_categorie VARCHAR(255)
);

CREATE TABLE Idee (
    id_idee INTEGER PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(100) NOT NULL,
    contenu_idee TEXT,
    est_publique BOOLEAN NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME ON UPDATE CURRENT_TIMESTAMP,
    employe_id INTEGER,
    categorie_id INTEGER,
    statut ENUM('Soumis', 'Approuvé', 'Rejeté', 'Implémenté') NOT NULL,
    FOREIGN KEY (employe_id) REFERENCES Employe(id_employe),
    FOREIGN KEY (categorie_id) REFERENCES Categorie(id_categorie)
);

CREATE TABLE Fichier (
    id_fichier INTEGER PRIMARY KEY AUTO_INCREMENT,
    nom_fichier VARCHAR(255) NOT NULL,
    type VARCHAR(255),
    taille DOUBLE,
    contenu_fichier VARCHAR(255), -- Utilisation de varchar pour stocker le chemin du fichier
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

CREATE TABLE LikeIdee (
    id_like INTEGER PRIMARY KEY AUTO_INCREMENT,
    employe_id INTEGER,
    idee_id INTEGER,
    FOREIGN KEY (employe_id) REFERENCES Employe(id_employe),
    FOREIGN KEY (idee_id) REFERENCES Idee(id_idee),
    UNIQUE (employe_id, idee_id) -- Un employé ne peut liker une idée qu'une seule fois
);

CREATE TABLE LikeCommentaire (
    id_like INTEGER PRIMARY KEY AUTO_INCREMENT,
    employe_id INTEGER,
    commentaire_id INTEGER,
    FOREIGN KEY (employe_id) REFERENCES Employe(id_employe),
    FOREIGN KEY (commentaire_id) REFERENCES Commentaire(id_commentaire),
    UNIQUE (employe_id, commentaire_id) -- Un employé ne peut liker un commentaire qu'une seule fois
);
