<?php
/**
 * Formulaire partagé pour la création et l'édition d'une tâche.
 *
 * Ce template est inclus par create.php et edit.php. Il affiche un formulaire
 * identique dans les deux cas ; seule la variable $isEdit détermine le libellé
 * du bouton de soumission et le lien d'annulation.
 *
 * Variables attendues (définies par la page appelante) :
 *  - $isEdit          (bool)   true en mode édition, false en création
 *  - $task            (array)  données de la tâche (vide [] en création)
 *  - $collaborateurs  (array)  liste des collaborateurs (table `collaborateurs`)
 *  - $priorites       (array)  liste des priorités (table `priorites`)
 *  - $statuts         (array)  liste des statuts (table `statuts_tache`)
 *  - $matieres        (array)  liste des matières (table `matieres`)
 *  - $difficultes     (array)  liste des niveaux de difficulté
 *  - $competences     (array)  liste des niveaux de compétence
 *  - $associations    (array)  couples valides difficulté↔compétence
 *                              (table `niveaux_difficulte_competence`)
 *
 * Le script JavaScript en bas de ce fichier filtre dynamiquement le menu
 * déroulant « Compétence requise » en fonction de la difficulté sélectionnée,
 * afin de respecter la contrainte de clé étrangère composite de la table `taches`.
 *
 * @author  Laurent Boyer — Groupe 3 LPDWCA
 * @version 1.0
 *
 * @var bool   $isEdit
 * @var array  $task
 * @var array  $collaborateurs
 * @var array  $priorites
 * @var array  $statuts
 * @var array  $matieres
 * @var array  $difficultes
 * @var array  $competences
 * @var array  $associations
 */
?>

<form method="post" class="needs-validation" novalidate>
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label for="libelle" class="form-label">Titre</label>
                    <input type="text" id="libelle" name="libelle" class="form-control" required
                           value="<?= e($task['libelle'] ?? '') ?>">
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4" required><?= e($task['description'] ?? '') ?></textarea>
                </div>

                <div class="col-md-6">
                    <label for="id_collaborateur" class="form-label">Collaborateur</label>
                    <select id="id_collaborateur" name="id_collaborateur" class="form-select" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach ($collaborateurs as $c): ?>
                            <option value="<?= $c['id_collaborateur'] ?>"
                                <?= ($task['id_collaborateur'] ?? '') == $c['id_collaborateur'] ? 'selected' : '' ?>>
                                <?= e($c['nom_affichage']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="id_matiere" class="form-label">Matière</label>
                    <select id="id_matiere" name="id_matiere" class="form-select" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach ($matieres as $m): ?>
                            <option value="<?= $m['id_matiere'] ?>"
                                <?= ($task['id_matiere'] ?? '') == $m['id_matiere'] ? 'selected' : '' ?>>
                                <?= e($m['libelle']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="id_priorite" class="form-label">Priorité</label>
                    <select id="id_priorite" name="id_priorite" class="form-select" required>
                        <?php foreach ($priorites as $p): ?>
                            <option value="<?= $p['id_priorite'] ?>"
                                <?= ($task['id_priorite'] ?? '') == $p['id_priorite'] ? 'selected' : '' ?>>
                                <?= e($p['libelle']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="id_statut_tache" class="form-label">Statut</label>
                    <select id="id_statut_tache" name="id_statut_tache" class="form-select" required>
                        <?php foreach ($statuts as $s): ?>
                            <option value="<?= $s['id_statut_tache'] ?>"
                                <?= ($task['id_statut_tache'] ?? '') == $s['id_statut_tache'] ? 'selected' : '' ?>>
                                <?= e($s['libelle']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="temps_passe_minutes" class="form-label">Temps passé (min)</label>
                    <input type="number" id="temps_passe_minutes" name="temps_passe_minutes"
                           class="form-control" min="0"
                           value="<?= (int) ($task['temps_passe_minutes'] ?? 0) ?>">
                </div>

                <div class="col-md-6">
                    <label for="id_niveau_difficulte" class="form-label">Difficulté</label>
                    <select id="id_niveau_difficulte" name="id_niveau_difficulte" class="form-select" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach ($difficultes as $d): ?>
                            <option value="<?= $d['id_niveau_difficulte'] ?>"
                                <?= ($task['id_niveau_difficulte'] ?? '') == $d['id_niveau_difficulte'] ? 'selected' : '' ?>>
                                <?= e($d['libelle']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="id_niveau_competence_requis" class="form-label">Compétence requise</label>
                    <select id="id_niveau_competence_requis" name="id_niveau_competence_requis"
                            class="form-select" required>
                        <option value="">-- Choisir une difficulté d'abord --</option>
                        <?php if ($isEdit): ?>
                            <?php foreach ($competences as $nc): ?>
                                <option value="<?= $nc['id_niveau_competence'] ?>"
                                    <?= $task['id_niveau_competence_requis'] == $nc['id_niveau_competence'] ? 'selected' : '' ?>>
                                    <?= e($nc['libelle']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="<?= $isEdit ? "task.php?id={$task['id_tache']}" : 'index.php' ?>"
               class="btn btn-secondary me-2">Annuler</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i>
                <?= $isEdit ? 'Enregistrer' : 'Créer la tâche' ?>
            </button>
        </div>
    </div>
</form>

<?php
/*
 * Script JS : filtrage dynamique du dropdown « Compétence requise ».
 *
 * La base de données impose une contrainte de clé étrangère composite
 * (id_niveau_difficulte, id_niveau_competence_requis) qui doit exister
 * dans la table niveaux_difficulte_competence.
 *
 * Ce script empêche côté client de choisir une combinaison invalide :
 *  1. Les associations valides sont injectées en JSON depuis PHP
 *  2. À chaque changement de difficulté, on reconstruit les <option>
 *     du select "Compétence" avec uniquement les valeurs autorisées
 *  3. En mode édition, la compétence actuelle est pré-sélectionnée
 */
?>
<?php ob_start(); ?>
<script>
    // Données injectées par PHP : couples (difficulté, compétence) autorisés
    var associations = <?= json_encode($associations) ?>;
    // Liste complète des niveaux de compétence
    var allCompetences = <?= json_encode($competences) ?>;
    // Compétence actuelle (null en mode création)
    var currentCompetence = <?= json_encode($task['id_niveau_competence_requis'] ?? null) ?>;

    var diffSelect = document.getElementById("id_niveau_difficulte");
    var compSelect = document.getElementById("id_niveau_competence_requis");

    /**
     * Reconstruit les options du select Compétence
     * en ne gardant que celles associées à la difficulté choisie.
     */
    function updateCompetences() {
        var diffId = parseInt(diffSelect.value);
        compSelect.innerHTML = "";

        if (!diffId) {
            compSelect.innerHTML = '<option value="">-- Choisir une difficulté d\'abord --</option>';
            return;
        }

        // Filtrer les compétences valides pour cette difficulté
        var valid = associations
            .filter(function(a) { return a.id_niveau_difficulte === diffId; })
            .map(function(a) { return a.id_niveau_competence; });

        compSelect.innerHTML = '<option value="">-- Choisir --</option>';
        allCompetences.forEach(function(c) {
            if (valid.indexOf(c.id_niveau_competence) !== -1) {
                var opt = document.createElement("option");
                opt.value = c.id_niveau_competence;
                opt.textContent = c.libelle;
                if (c.id_niveau_competence === currentCompetence) opt.selected = true;
                compSelect.appendChild(opt);
            }
        });
    }

    // Écouter les changements de difficulté
    diffSelect.addEventListener("change", function() { updateCompetences(); });
    // Initialiser au chargement si une difficulté est déjà sélectionnée (mode édition)
    if (diffSelect.value) updateCompetences();
</script>
<?php $pageScripts = ob_get_clean(); ?>
