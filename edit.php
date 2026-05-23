<?php
/**
 * Page d'édition d'une tâche existante.
 *
 * GET  : charge la tâche depuis la base et affiche le formulaire pré-rempli
 * POST : valide les modifications, met à jour en base et redirige vers le détail
 *
 * Le formulaire est le même que pour la création (includes/task_form.php),
 * avec $isEdit = true pour adapter les libellés.
 *
 * La date de modification est automatiquement mise à jour à chaque sauvegarde.
 * La date de complétion est gérée selon le statut (terminal ou non).
 *
 * @author  Laurent Boyer — Groupe 3 LPDWCA
 * @version 1.0
 */

require_once 'includes/init.php';

$pdo    = getDB();
// L'identifiant vient de l'URL (GET) ou du formulaire soumis (POST)
$id     = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$errors = [];

if (!$id) {
    header('Location: index.php');
    exit;
}

// ══════════════════════════════════════════════════════════════════════════
//  Traitement POST : validation et mise à jour
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
        // Vérifier si le nouveau statut est terminal pour gérer la date de complétion
        $stTerminal = $pdo->prepare('SELECT est_terminal FROM statuts_tache WHERE id_statut_tache = ?');
        $stTerminal->execute([$idStatut]);
        $isTerminal     = (bool) $stTerminal->fetchColumn();
        $now            = date('Y-m-d H:i:s');
        $dateCompletion = $isTerminal ? $now : null;

        // Mise à jour de la tâche (date_modification automatiquement actualisée)
        $sql = "UPDATE taches SET
                    libelle = ?, description = ?, date_modification = ?, date_completion = ?,
                    temps_passe_minutes = ?, id_collaborateur = ?, id_priorite = ?,
                    id_statut_tache = ?, id_matiere = ?,
                    id_niveau_competence_requis = ?, id_niveau_difficulte = ?
                WHERE id_tache = ?";

        $pdo->prepare($sql)->execute([
            $libelle, $description, $now, $dateCompletion,
            $tempsPasse, $idCollab, $idPriorite,
            $idStatut, $idMatiere,
            $idCompetence, $idDifficulte, $id,
        ]);

        flash('success', 'Tâche mise à jour.');
        header("Location: task.php?id=$id");
        exit;
    }
}

// ══════════════════════════════════════════════════════════════════════════
//  Chargement de la tâche existante
// ══════════════════════════════════════════════════════════════════════════
$stmt = $pdo->prepare('SELECT * FROM taches WHERE id_tache = ?');
$stmt->execute([$id]);
$task = $stmt->fetch();

if (!$task) {
    flash('danger', 'Tâche introuvable.');
    header('Location: index.php');
    exit;
}

// En cas d'erreur de validation, on fusionne les valeurs POST dans $task
// pour conserver les modifications de l'utilisateur dans le formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task = array_merge($task, $_POST);
}

$isEdit    = true;
$pageTitle = 'Modifier : ' . $task['libelle'];

// ── Chargement des données pour les menus déroulants ──────────────────────
$collaborateurs = $pdo->query('SELECT * FROM collaborateurs ORDER BY nom_affichage')->fetchAll();
$priorites      = $pdo->query('SELECT * FROM priorites ORDER BY ordre_affichage')->fetchAll();
$statuts        = $pdo->query('SELECT * FROM statuts_tache ORDER BY ordre_affichage')->fetchAll();
$matieres       = $pdo->query('SELECT * FROM matieres ORDER BY libelle')->fetchAll();
$difficultes    = $pdo->query('SELECT * FROM niveaux_difficulte ORDER BY ordre_niveau')->fetchAll();
$competences    = $pdo->query('SELECT * FROM niveaux_competence ORDER BY ordre_niveau')->fetchAll();
$associations   = $pdo->query('SELECT * FROM niveaux_difficulte_competence')->fetchAll();

require 'includes/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Tâches</a></li>
        <li class="breadcrumb-item"><a href="task.php?id=<?= $id ?>"><?= e($task['libelle']) ?></a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
</nav>

<h1 class="h3 mb-4">Modifier la tâche</h1>

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
