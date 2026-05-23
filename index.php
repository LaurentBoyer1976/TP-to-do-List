<?php
/**
 * Page d'accueil — Liste des tâches avec filtres.
 *
 * Affiche toutes les tâches sous forme de tableau avec possibilité de filtrer
 * par statut, priorité et recherche textuelle (titre ou description).
 * Les filtres sont transmis en GET pour permettre le partage d'URL filtrée.
 *
 * Chaque ligne propose des liens vers le détail, l'édition et la suppression.
 * Les tâches dont le statut est terminal (Terminé, Archivé) sont grisées.
 *
 * @author  Laurent Boyer — Groupe 3 LPDWCA
 * @version 1.0
 */

require_once 'includes/init.php';

$pdo = getDB();
$pageTitle = 'Liste des tâches';

// ── Récupération des filtres GET ──────────────────────────────────────────
$filterStatut   = $_GET['statut'] ?? '';
$filterPriorite = $_GET['priorite'] ?? '';
$search         = trim($_GET['q'] ?? '');

// ── Construction dynamique de la clause WHERE ─────────────────────────────
// On utilise des requêtes préparées (?) pour éviter les injections SQL.
$where  = [];
$params = [];

if ($filterStatut !== '') {
    $where[]  = 't.id_statut_tache = ?';
    $params[] = (int) $filterStatut;
}
if ($filterPriorite !== '') {
    $where[]  = 't.id_priorite = ?';
    $params[] = (int) $filterPriorite;
}
if ($search !== '') {
    $where[]  = '(t.libelle LIKE ? OR t.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ── Requête principale : jointures vers les tables de référence ───────────
$sql = "SELECT t.id_tache, t.libelle, t.date_modification,
               c.nom_affichage  AS collaborateur,
               p.libelle AS priorite, p.id_priorite,
               st.libelle AS statut, st.est_terminal,
               m.libelle  AS matiere
        FROM taches t
        JOIN collaborateurs c  ON c.id_collaborateur  = t.id_collaborateur
        JOIN priorites p       ON p.id_priorite       = t.id_priorite
        JOIN statuts_tache st  ON st.id_statut_tache  = t.id_statut_tache
        JOIN matieres m        ON m.id_matiere        = t.id_matiere
        $whereSQL
        ORDER BY t.date_modification DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$taches = $stmt->fetchAll();

// Chargement des listes pour les menus déroulants de filtrage
$statuts   = $pdo->query('SELECT * FROM statuts_tache ORDER BY ordre_affichage')->fetchAll();
$priorites = $pdo->query('SELECT * FROM priorites ORDER BY ordre_affichage')->fetchAll();

require 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Tâches</h1>
    <a href="create.php" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nouvelle tâche
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="filter-statut" class="form-label">Statut</label>
                <select id="filter-statut" name="statut" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <?php foreach ($statuts as $s): ?>
                        <option value="<?= $s['id_statut_tache'] ?>"
                            <?= $filterStatut == $s['id_statut_tache'] ? 'selected' : '' ?>>
                            <?= e($s['libelle']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter-priorite" class="form-label">Priorité</label>
                <select id="filter-priorite" name="priorite" class="form-select form-select-sm">
                    <option value="">Toutes</option>
                    <?php foreach ($priorites as $p): ?>
                        <option value="<?= $p['id_priorite'] ?>"
                            <?= $filterPriorite == $p['id_priorite'] ? 'selected' : '' ?>>
                            <?= e($p['libelle']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="filter-search" class="form-label">Recherche</label>
                <input type="text" id="filter-search" name="q" class="form-control form-control-sm"
                       value="<?= e($search) ?>" placeholder="Titre ou description...">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filtrer
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (empty($taches)): ?>
    <div class="alert alert-info">Aucune tâche trouvée.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Titre</th>
                    <th>Priorité</th>
                    <th>Statut</th>
                    <th>Matière</th>
                    <th>Collaborateur</th>
                    <th>Modifié le</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($taches as $t): ?>
                <tr class="<?= $t['est_terminal'] ? 'table-secondary' : '' ?>">
                    <td><?= $t['id_tache'] ?></td>
                    <td>
                        <a href="task.php?id=<?= $t['id_tache'] ?>">
                            <?= e($t['libelle']) ?>
                        </a>
                    </td>
                    <td>
                        <span class="badge bg-<?= prioriteColor($t['id_priorite']) ?>">
                            <?= e($t['priorite']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-<?= statutColor($t['est_terminal']) ?>">
                            <?= e($t['statut']) ?>
                        </span>
                    </td>
                    <td><?= e($t['matiere']) ?></td>
                    <td><?= e($t['collaborateur']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($t['date_modification'])) ?></td>
                    <td class="text-end text-nowrap">
                        <a href="edit.php?id=<?= $t['id_tache'] ?>"
                           class="btn btn-sm btn-outline-primary" title="Modifier">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="post" action="delete.php" class="d-inline"
                              onsubmit="return confirm('Supprimer cette tâche ?')">
                            <input type="hidden" name="id" value="<?= $t['id_tache'] ?>">
                            <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="text-muted small"><?= count($taches) ?> tâche(s)</p>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
