<?php
/**
 * Page de détail d'une tâche.
 *
 * Affiche toutes les informations d'une tâche (propriétés, sous-tâches, ressources)
 * et permet de gérer directement depuis cette page :
 *  - Sous-tâches : ajouter, supprimer, cocher/décocher (toggle)
 *  - Ressources : ajouter un lien, supprimer une ressource
 *
 * Les actions POST sont traitées en haut du fichier puis redirigées
 * vers la même page (pattern Post/Redirect/Get) pour éviter la resoumission.
 *
 * @author  Laurent Boyer — Groupe 3 LPDWCA
 * @version 1.0
 */

require_once 'includes/init.php';

$pdo = getDB();
$id  = (int) ($_GET['id'] ?? 0);

if (!$id) {
    header('Location: index.php');
    exit;
}

// ══════════════════════════════════════════════════════════════════════════
//  Traitement des actions POST (sous-tâches et ressources)
//  Chaque formulaire envoie un champ caché 'action' pour identifier l'opération.
// ══════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Toggle sous-tâche : inverse la valeur de est_terminee (0↔1) ───────
    if ($action === 'toggle_subtask') {
        $idSub = (int) $_POST['id_sous_tache'];
        $pdo->prepare('UPDATE sous_taches SET est_terminee = NOT est_terminee WHERE id_sous_tache = ? AND id_tache = ?')
            ->execute([$idSub, $id]);
    }

    // ── Ajout d'une sous-tâche ────────────────────────────────────────────
    if ($action === 'add_subtask') {
        $libelle = trim($_POST['libelle'] ?? '');
        $bloc    = $_POST['bloc_todo'] ?? 'ToDoA';
        if ($libelle !== '') {
            // code_source unique généré automatiquement via uniqid()
            $code = uniqid('S', true);
            $pdo->prepare('INSERT INTO sous_taches (id_tache, code_source, bloc_todo, libelle) VALUES (?, ?, ?, ?)')
                ->execute([$id, $code, $bloc, $libelle]);
            flash('success', 'Sous-tâche ajoutée.');
        }
    }

    // ── Suppression d'une sous-tâche ──────────────────────────────────────
    if ($action === 'delete_subtask') {
        $idSub = (int) $_POST['id_sous_tache'];
        $pdo->prepare('DELETE FROM sous_taches WHERE id_sous_tache = ? AND id_tache = ?')
            ->execute([$idSub, $id]);
        flash('success', 'Sous-tâche supprimée.');
    }

    // ── Ajout d'un lien (ressource de type 'lien') ───────────────────────
    // Transaction : on insère dans 3 tables (ressources, ressources_liens,
    // taches_ressources) de manière atomique.
    if ($action === 'add_link') {
        $titre = trim($_POST['titre'] ?? '');
        $url   = trim($_POST['url'] ?? '');
        if ($titre !== '' && $url !== '') {
            $pdo->beginTransaction();
            $pdo->prepare("INSERT INTO ressources (titre, type_ressource) VALUES (?, 'lien')")
                ->execute([$titre]);
            $resId = (int) $pdo->lastInsertId();
            $pdo->prepare('INSERT INTO ressources_liens (id_ressource, url) VALUES (?, ?)')
                ->execute([$resId, $url]);
            $pdo->prepare('INSERT INTO taches_ressources (id_tache, id_ressource) VALUES (?, ?)')
                ->execute([$id, $resId]);
            $pdo->commit();
            flash('success', 'Lien ajouté.');
        }
    }

    // ── Suppression d'une ressource ───────────────────────────────────────
    // La suppression cascade vers ressources_liens/fichiers et taches_ressources
    if ($action === 'delete_resource') {
        $resId = (int) $_POST['id_ressource'];
        $pdo->prepare('DELETE FROM ressources WHERE id_ressource = ?')
            ->execute([$resId]);
        flash('success', 'Ressource supprimée.');
    }

    // Redirection PRG (Post/Redirect/Get) pour éviter la resoumission du formulaire
    header("Location: task.php?id=$id");
    exit;
}

// ══════════════════════════════════════════════════════════════════════════
//  Chargement des données de la tâche (GET)
// ══════════════════════════════════════════════════════════════════════════

// Requête avec jointures vers toutes les tables de référence
$sql = "SELECT t.*,
               c.nom_affichage AS collaborateur,
               p.libelle AS priorite, p.id_priorite,
               st.libelle AS statut, st.est_terminal,
               m.libelle AS matiere,
               nc.libelle AS competence,
               nd.libelle AS difficulte
        FROM taches t
        JOIN collaborateurs c  ON c.id_collaborateur       = t.id_collaborateur
        JOIN priorites p       ON p.id_priorite            = t.id_priorite
        JOIN statuts_tache st  ON st.id_statut_tache       = t.id_statut_tache
        JOIN matieres m        ON m.id_matiere             = t.id_matiere
        JOIN niveaux_competence nc ON nc.id_niveau_competence = t.id_niveau_competence_requis
        JOIN niveaux_difficulte nd ON nd.id_niveau_difficulte = t.id_niveau_difficulte
        WHERE t.id_tache = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$task = $stmt->fetch();

if (!$task) {
    flash('danger', 'Tâche introuvable.');
    header('Location: index.php');
    exit;
}

$pageTitle = $task['libelle'];

// Chargement des sous-tâches triées par bloc (ToDoA/ToDoB) puis par ordre d'affichage
$subtasks = $pdo->prepare('SELECT * FROM sous_taches WHERE id_tache = ? ORDER BY bloc_todo, ordre_affichage');
$subtasks->execute([$id]);
$subtasks = $subtasks->fetchAll();

// Chargement des ressources (liens et fichiers) via la table de jonction.
// LEFT JOIN car une ressource est soit un lien, soit un fichier (héritage par spécialisation).
$resSql = "SELECT r.*, rl.url, rf.nom_original
           FROM taches_ressources tr
           JOIN ressources r ON r.id_ressource = tr.id_ressource
           LEFT JOIN ressources_liens rl    ON rl.id_ressource = r.id_ressource
           LEFT JOIN ressources_fichiers rf ON rf.id_ressource = r.id_ressource
           WHERE tr.id_tache = ?
           ORDER BY tr.ordre_affichage";
$resStmt = $pdo->prepare($resSql);
$resStmt->execute([$id]);
$resources = $resStmt->fetchAll();

require 'includes/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Tâches</a></li>
        <li class="breadcrumb-item active"><?= e($task['libelle']) ?></li>
    </ol>
</nav>

<div class="row">
    <!-- Main info -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><?= e($task['libelle']) ?></h4>
                <div>
                    <a href="edit.php?id=<?= $id ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i> Modifier
                    </a>
                    <form method="post" action="delete.php" class="d-inline"
                          onsubmit="return confirm('Supprimer cette tâche ?')">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <button class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i> Supprimer
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <p><?= nl2br(e($task['description'])) ?></p>

                <div class="row g-3 mt-2">
                    <div class="col-sm-4">
                        <span class="task-detail-label d-block">Priorité</span>
                        <span class="badge bg-<?= prioriteColor($task['id_priorite']) ?>">
                            <?= e($task['priorite']) ?>
                        </span>
                    </div>
                    <div class="col-sm-4">
                        <span class="task-detail-label d-block">Statut</span>
                        <span class="badge bg-<?= statutColor($task['est_terminal']) ?>">
                            <?= e($task['statut']) ?>
                        </span>
                    </div>
                    <div class="col-sm-4">
                        <span class="task-detail-label d-block">Matière</span>
                        <?= e($task['matiere']) ?>
                    </div>
                    <div class="col-sm-4">
                        <span class="task-detail-label d-block">Collaborateur</span>
                        <?= e($task['collaborateur']) ?>
                    </div>
                    <div class="col-sm-4">
                        <span class="task-detail-label d-block">Difficulté</span>
                        <?= e($task['difficulte']) ?>
                    </div>
                    <div class="col-sm-4">
                        <span class="task-detail-label d-block">Compétence requise</span>
                        <?= e($task['competence']) ?>
                    </div>
                    <div class="col-sm-4">
                        <span class="task-detail-label d-block">Temps passé</span>
                        <?= $task['temps_passe_minutes'] ?> min
                    </div>
                    <div class="col-sm-4">
                        <span class="task-detail-label d-block">Créé le</span>
                        <?= date('d/m/Y H:i', strtotime($task['date_creation'])) ?>
                    </div>
                    <div class="col-sm-4">
                        <span class="task-detail-label d-block">Modifié le</span>
                        <?= date('d/m/Y H:i', strtotime($task['date_modification'])) ?>
                    </div>
                    <?php if ($task['date_completion']): ?>
                    <div class="col-sm-4">
                        <span class="task-detail-label d-block">Complété le</span>
                        <?= date('d/m/Y H:i', strtotime($task['date_completion'])) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar: subtasks + resources -->
    <div class="col-lg-4">
        <!-- Subtasks -->
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Sous-tâches</h5></div>
            <ul class="list-group list-group-flush">
                <?php if (empty($subtasks)): ?>
                    <li class="list-group-item text-muted">Aucune sous-tâche</li>
                <?php endif; ?>
                <?php foreach ($subtasks as $st): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <form method="post" class="d-flex align-items-center gap-2 flex-grow-1">
                            <input type="hidden" name="action" value="toggle_subtask">
                            <input type="hidden" name="id_sous_tache" value="<?= $st['id_sous_tache'] ?>">
                            <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent">
                                <i class="bi <?= $st['est_terminee'] ? 'bi-check-circle-fill text-success' : 'bi-circle' ?>"></i>
                            </button>
                            <span class="<?= $st['est_terminee'] ? 'subtask-done' : '' ?>">
                                <?= e($st['libelle']) ?>
                                <small class="text-muted">(<?= e($st['bloc_todo']) ?>)</small>
                            </span>
                        </form>
                        <form method="post" onsubmit="return confirm('Supprimer ?')">
                            <input type="hidden" name="action" value="delete_subtask">
                            <input type="hidden" name="id_sous_tache" value="<?= $st['id_sous_tache'] ?>">
                            <button class="btn btn-sm text-danger p-0 border-0 bg-transparent">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="card-body border-top">
                <form method="post" class="row g-2">
                    <input type="hidden" name="action" value="add_subtask">
                    <div class="col-12">
                        <label for="subtask-libelle" class="visually-hidden">Libellé</label>
                        <input type="text" id="subtask-libelle" name="libelle" class="form-control form-control-sm"
                               placeholder="Nouvelle sous-tâche..." required>
                    </div>
                    <div class="col-8">
                        <label for="subtask-bloc" class="visually-hidden">Bloc</label>
                        <select id="subtask-bloc" name="bloc_todo" class="form-select form-select-sm">
                            <option value="ToDoA">ToDoA</option>
                            <option value="ToDoB">ToDoB</option>
                        </select>
                    </div>
                    <div class="col-4">
                        <button class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resources -->
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Ressources</h5></div>
            <ul class="list-group list-group-flush">
                <?php if (empty($resources)): ?>
                    <li class="list-group-item text-muted">Aucune ressource</li>
                <?php endif; ?>
                <?php foreach ($resources as $res): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <?php if ($res['type_ressource'] === 'lien'): ?>
                                <i class="bi bi-link-45deg"></i>
                                <a href="<?= e($res['url']) ?>" target="_blank">
                                    <?= e($res['titre']) ?>
                                </a>
                            <?php else: ?>
                                <i class="bi bi-file-earmark"></i>
                                <?= e($res['titre']) ?>
                                <small class="text-muted">(<?= e($res['nom_original']) ?>)</small>
                            <?php endif; ?>
                        </div>
                        <form method="post" onsubmit="return confirm('Supprimer ?')">
                            <input type="hidden" name="action" value="delete_resource">
                            <input type="hidden" name="id_ressource" value="<?= $res['id_ressource'] ?>">
                            <button class="btn btn-sm text-danger p-0 border-0 bg-transparent">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="card-body border-top">
                <form method="post" class="row g-2">
                    <input type="hidden" name="action" value="add_link">
                    <div class="col-12">
                        <label for="res-titre" class="visually-hidden">Titre</label>
                        <input type="text" id="res-titre" name="titre" class="form-control form-control-sm"
                               placeholder="Titre du lien" required>
                    </div>
                    <div class="col-8">
                        <label for="res-url" class="visually-hidden">URL</label>
                        <input type="url" id="res-url" name="url" class="form-control form-control-sm"
                               placeholder="https://..." required>
                    </div>
                    <div class="col-4">
                        <button class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
