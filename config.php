<?php
/**
 * Configuration de la connexion à la base de données.
 *
 * Les constantes peuvent être surchargées par des variables d'environnement,
 * ce qui permet d'utiliser des identifiants différents selon le contexte
 * (développement local, Docker, hébergement Alwaysdata…) sans modifier ce fichier.
 *
 * @author  Laurent Boyer — Groupe 3 LPDWCA
 * @version 1.0
 */

// Hôte du serveur MySQL (ex: 'localhost', 'mysql' pour Docker, 'mysql-xxx.alwaysdata.net')
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');

// Nom de la base de données (doit correspondre au CREATE DATABASE du script SQL)
define('DB_NAME', getenv('DB_NAME') ?: 'todo_list_php');

// Identifiants de connexion MySQL
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Jeu de caractères utilisé par PDO pour la connexion
const DB_CHARSET = 'utf8mb4';
