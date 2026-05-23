<?php
/**
 * Couche d'accès à la base de données via PDO.
 *
 * Fournit une fonction unique getDB() qui retourne une instance PDO configurée
 * en mode singleton (une seule connexion par requête HTTP).
 *
 * @author  Laurent Boyer — Groupe 3 LPDWCA
 * @version 1.0
 */

require_once __DIR__ . '/../config.php';

/**
 * Retourne l'instance PDO partagée (singleton).
 *
 * La connexion est créée au premier appel puis réutilisée.
 * Les options PDO activent :
 *  - ERRMODE_EXCEPTION   : les erreurs SQL lèvent des exceptions PHP
 *  - FETCH_ASSOC         : les résultats sont retournés en tableaux associatifs
 *  - EMULATE_PREPARES=false : les requêtes préparées sont traitées côté MySQL
 *    (meilleure sécurité et typage des paramètres)
 *
 * @return PDO Instance de connexion à la base de données
 * @throws PDOException En cas d'échec de connexion
 */
function getDB(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}
