<?php
/**
 * Point d'entrée commun à toutes les pages.
 *
 * Chaque page inclut ce fichier en premier afin de :
 *  1. Démarrer la session PHP (nécessaire pour les messages flash)
 *  2. Charger la connexion à la base de données (PDO)
 *  3. Charger les fonctions utilitaires (helpers)
 *
 * @author  Laurent Boyer — Groupe 3 LPDWCA
 * @version 1.0
 */

session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
