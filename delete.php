<?php
/**
 * Suppression d'une tâche.
 *
 * Accepte uniquement les requêtes POST (via le formulaire de confirmation
 * présent dans index.php et task.php) pour éviter les suppressions
 * accidentelles par simple navigation (lien GET).
 *
 * La suppression cascade automatiquement vers les sous-tâches et les
 * associations tâche↔ressource grâce aux contraintes ON DELETE CASCADE
 * définies dans le schéma SQL.
 *
 * @author  Laurent Boyer — Groupe 3 LPDWCA
 * @version 1.0
 */

require_once 'includes/init.php';

// Seules les requêtes POST sont autorisées (sécurité anti-CSRF basique)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id) {
    $pdo = getDB();
    $pdo->prepare('DELETE FROM taches WHERE id_tache = ?')->execute([$id]);
    flash('success', 'Tâche supprimée.');
}

header('Location: index.php');
exit;
