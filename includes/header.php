<?php
/**
 * Template d'en-tête HTML partagé par toutes les pages.
 *
 * Inclut :
 *  - Les balises <head> avec Bootstrap 5 (CDN) et Bootstrap Icons
 *  - La barre de navigation principale
 *  - L'affichage du message flash s'il existe en session (pattern PRG)
 *  - L'ouverture du conteneur <div class="container">
 *
 * Variable attendue : $pageTitle (string) — titre de la page courante.
 *
 * @author  Laurent Boyer — Groupe 3 LPDWCA
 * @version 1.0
 *
 * @var string $pageTitle
 */
?>
<!-- noinspection HtmlUnknownTarget -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'To Do List') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-check2-square"></i> To Do List
        </a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="create.php">
                <i class="bi bi-plus-circle"></i> Nouvelle tâche
            </a>
        </div>
    </div>
</nav>
<div class="container">
<?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show">
        <?= e($_SESSION['flash']['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
