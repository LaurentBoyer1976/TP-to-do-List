<?php
/**
 * Fonctions utilitaires partagées par toutes les pages de l'application.
 *
 * Regroupe les helpers de présentation (couleurs Bootstrap),
 * le système de messages flash (via $_SESSION) et l'échappement HTML.
 *
 * @author  Laurent Boyer — Groupe 3 LPDWCA
 * @version 1.0
 */

/**
 * Retourne la classe de couleur Bootstrap associée à une priorité.
 *
 * Correspondance :
 *   1 (Important)     → danger  (rouge)
 *   2 (Haut)          → warning (jaune)
 *   3 (Moyen)         → info    (bleu clair)
 *   4 (Bas)           → primary (bleu)
 *   5 (Non important) → secondary (gris)
 *
 * @param  int    $id  Identifiant de la priorité (table `priorites`)
 * @return string Classe(s) CSS Bootstrap pour le badge
 */
function prioriteColor(int $id): string
{
    $colors = [
        1 => 'danger',
        2 => 'warning text-dark',
        3 => 'info text-dark',
        4 => 'primary',
        5 => 'secondary',
    ];
    return $colors[$id] ?? 'secondary';
}

/**
 * Retourne la classe de couleur Bootstrap selon qu'un statut est terminal ou non.
 *
 * Un statut terminal (Terminé, Archivé) est affiché en vert (success),
 * les autres en bleu clair (info).
 *
 * @param  int    $estTerminal  0 ou 1 (champ `est_terminal` de `statuts_tache`)
 * @return string Classe CSS Bootstrap
 */
function statutColor(int $estTerminal): string
{
    return $estTerminal ? 'success' : 'info text-dark';
}

/**
 * Stocke un message flash en session pour affichage après redirection.
 *
 * Le message est affiché une seule fois par le template header.php,
 * puis supprimé de la session (pattern Post/Redirect/Get).
 *
 * @param string $type    Type d'alerte Bootstrap : 'success', 'danger', 'warning', 'info'
 * @param string $message Texte du message à afficher
 * @return void
 */
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Échappe une chaîne pour un affichage HTML sécurisé (protection XSS).
 *
 * Convertit les caractères spéciaux (<, >, ", ', &) en entités HTML.
 *
 * @param  string $str Chaîne brute
 * @return string Chaîne échappée, sûre pour l'insertion dans le HTML
 */
function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
