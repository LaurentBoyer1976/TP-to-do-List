<?php
/**
 * Page de création d'une nouvelle tâche.
 *
 * GET  : affiche le formulaire vide (inclut includes/task_form.php)
 * POST : valide les données soumises, insère en base et redirige vers le détail.
 *
 * La date de complétion est automatiquement renseignée si le statut choisi
 * est un statut terminal (Terminé ou Archivé).
 *
 * En cas d'erreur de validation, le formulaire est ré-affiché avec les valeurs
 * saisies conservées (via $task = $_POST).
 *
 * @author  Laurent Boyer — Groupe 3 LPDWCA
 * @version 1.0
 */

require_once 'includes/init.php';

$pdo       = getDB();
$pageTitle = 'Nouvelle tâche';
$errors    = [];

// ══════════════════════════════════════════════════════════════════════════
//  Traitement POST : validation et insertion
// ══════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage et cast des données soumises
    $libelle       = trim($_POST['libelle'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $idCollab      = (int) ($_POST['id_collaborateur'] ?? 0);
    $idPriorite    = (int) ($_POST['id_priorite'] ?? 0);
    $idStatut      = (int) ($_POST['id_statut_tache'] ?? 0);
    $idMatiere     = (int) ($_POST['id_matiere'] ?? 0);
    $idDifficulte  = (int) ($_POST['id_niveau_difficulte'] ?? 0);
    $idCompetence  = (int) ($_POST['id_niveau_competence_requis'] ?? 0);
    $tempsPasse    = max(0, (int) ($_POST['temps_passe_minutes'] ?? 0));

    // Validation des champs obligatoires
    if ($libelle === '')     $errors[] = 'Le titre est requis.';
    if ($description === '') $errors[] = 'La description est requise.';
    if (!$idCollab)          $errors[] = 'Collaborateur requis.';
    if (!$idDifficulte)      $errors[] = 'Difficulté requise.';
    if (!$idCompetence)      $errors[] = 'Compétence requise.';

    if (empty($errors)) {
        // Vérifier si le statut choisi est terminal pour auto-remplir la date de complétion
        $stTerminal = $pdo->prepare('SELECT est_terminal FROM statuts_tache WHERE id_statut_tache = ?');
        $stTerminal->execute([$idStatut]);
        $isTerminal     = (bool) $stTerminal->fetchColumn();
        $now            = date('Y-m-d H:i:s');
        $dateCompletion = $isTerminal ? $now : null;

        // Insertion dans la table taches (requête préparée = protection injection SQL)
        $sql = "INSERT INTO taches
                (libelle, description, date_creation, date_modification, date_completion,
                 temps_passe_minutes, id_collaborateur, id_priorite, id_statut_tache,
                 id_matiere, id_niveau_competence_requis, id_niveau_difficulte)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $pdo->prepare($sql)->execute([
            $libelle, $description, $now, $now, $dateCompletion,
            $tempsPasse, $idCollab, $idPriorite, $idStatut,
            $idMatiere, $idCompetence, $idDifficulte,
        ]);

        $newId = (int) $pdo->lastInsertId();
        flash('success', 'Tâche créée avec succès.');
        header("Location: task.php?id=$newId");
        exit;
    }
}

// ══════════════════════════════════════════════════════════════════════════
//  Chargement des données pour les menus déroulants du formulaire
// ══════════════════════════════════════════════════════════════════════════
$collaborateurs = $pdo->query('SELECT * FROM collaborateurs ORDER BY nom_affichage')->fetchAll();
$priorites      = $pdo->query('SELECT * FROM priorites ORDER BY ordre_affichage')->fetchAll();
$statuts        = $pdo->query('SELECT * FROM statuts_tache ORDER BY ordre_affichage')->fetchAll();
$matieres       = $pdo->query('SELECT * FROM matieres ORDER BY libelle')->fetchAll();
$difficultes    = $pdo->query('SELECT * FROM niveaux_difficulte ORDER BY ordre_niveau')->fetchAll();
$competences    = $pdo->query('SELECT * FROM niveaux_competence ORDER BY ordre_niveau')->fetchAll();
$associations   = $pdo->query('SELECT * FROM niveaux_difficulte_competence')->fetchAll();

// En mode création, $isEdit = false ; $task contient les valeurs POST en cas d'erreur
$isEdit = false;
$task   = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : [];

require 'includes/header.php';
?>

<h1 class="h3 mb-4">Nouvelle tâche</h1>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php require 'includes/task_form.php'; ?>
<?php require 'includes/footer.php'; ?>
