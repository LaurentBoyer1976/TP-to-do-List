-- =============================================================
--  SP To Do List — Export SQL MySQL (version finale corrigée)
--  Base : todo_list_php
--  Auteur équipe : Groupe 3 — LPDWCA
--  Date : 2026-05-11
-- =============================================================

DROP DATABASE IF EXISTS todo_list_php;
CREATE DATABASE todo_list_php
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE todo_list_php;
SET NAMES utf8mb4;

-- ─────────────────────────────────────────────────────────────
--  TABLES DE RÉFÉRENCE / LOOKUP
-- ─────────────────────────────────────────────────────────────

CREATE TABLE collaborateurs (
                              id_collaborateur INT UNSIGNED    NOT NULL AUTO_INCREMENT,
                              nom_affichage   VARCHAR(100)    NOT NULL,
                              email           VARCHAR(190)    NULL,
                              date_creation   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                              PRIMARY KEY (id_collaborateur),
                              UNIQUE KEY uq_collaborateur_nom   (nom_affichage),
                              UNIQUE KEY uq_collaborateur_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE priorites (
                           id_priorite      TINYINT UNSIGNED NOT NULL,
                           libelle          VARCHAR(50)      NOT NULL,
                           ordre_affichage  TINYINT UNSIGNED NOT NULL,
                           PRIMARY KEY (id_priorite),
                           UNIQUE KEY uq_priorites_libelle (libelle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE statuts_tache (
                               id_statut_tache  TINYINT UNSIGNED NOT NULL,
                               libelle          VARCHAR(80)      NOT NULL,
                               ordre_affichage  TINYINT UNSIGNED NOT NULL,
                               est_terminal     TINYINT(1)       NOT NULL DEFAULT 0,
                               PRIMARY KEY (id_statut_tache),
                               UNIQUE KEY uq_statuts_tache_libelle (libelle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE matieres (
                          id_matiere  SMALLINT UNSIGNED NOT NULL,
                          libelle     VARCHAR(150)      NOT NULL,
                          PRIMARY KEY (id_matiere),
                          UNIQUE KEY uq_matieres_libelle (libelle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE niveaux_competence (
                                    id_niveau_competence  TINYINT UNSIGNED NOT NULL,
                                    libelle               VARCHAR(50)      NOT NULL,
                                    description           VARCHAR(255)     NOT NULL,
                                    experience_requise    VARCHAR(50)      NOT NULL,
                                    ordre_niveau          TINYINT UNSIGNED NOT NULL,
                                    PRIMARY KEY (id_niveau_competence),
                                    UNIQUE KEY uq_niveaux_competence_libelle (libelle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE niveaux_difficulte (
                                    id_niveau_difficulte  TINYINT UNSIGNED NOT NULL,
                                    libelle               VARCHAR(50)      NOT NULL,
                                    note                  VARCHAR(255)     NOT NULL,
                                    ordre_niveau          TINYINT UNSIGNED NOT NULL,
                                    PRIMARY KEY (id_niveau_difficulte),
                                    UNIQUE KEY uq_niveaux_difficulte_libelle (libelle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
--  RELATION N:M #1  (native du JSON — skillsLevelIds[])
--  niveaux_difficulté ↔ niveaux_competence
-- ─────────────────────────────────────────────────────────────
CREATE TABLE niveaux_difficulte_competence (
                                               id_niveau_difficulte  TINYINT UNSIGNED NOT NULL,
                                               id_niveau_competence  TINYINT UNSIGNED NOT NULL,
                                               PRIMARY KEY (id_niveau_difficulte, id_niveau_competence),
                                               CONSTRAINT fk_ndc_difficulte
                                                   FOREIGN KEY (id_niveau_difficulte)
                                                       REFERENCES niveaux_difficulte (id_niveau_difficulte)
                                                       ON UPDATE CASCADE ON DELETE RESTRICT,
                                               CONSTRAINT fk_ndc_competence
                                                   FOREIGN KEY (id_niveau_competence)
                                                       REFERENCES niveaux_competence (id_niveau_competence)
                                                       ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
--  TABLE CENTRALE : taches
--  Contrainte composite fk_taches_compatibilite :
--  garantit que le couple (difficulté, competence) est valide
--  dans niveaux_difficulte_competence.
-- ─────────────────────────────────────────────────────────────
CREATE TABLE taches (
                        id_tache                   INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                        libelle                    VARCHAR(255)     NOT NULL,
                        description                TEXT             NOT NULL,
                        date_creation              DATETIME         NOT NULL,
                        date_modification          DATETIME         NOT NULL,
                        date_completion            DATETIME         NULL,
                        temps_passe_minutes        SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                        id_collaborateur           INT UNSIGNED     NOT NULL,
                        id_priorite                TINYINT UNSIGNED NOT NULL,
                        id_statut_tache            TINYINT UNSIGNED NOT NULL,
                        id_matiere                 SMALLINT UNSIGNED NOT NULL,
                        id_niveau_competence_requis TINYINT UNSIGNED NOT NULL,
                        id_niveau_difficulte       TINYINT UNSIGNED NOT NULL,
                        PRIMARY KEY (id_tache),
                        KEY idx_taches_collaborateur  (id_collaborateur),
                        KEY idx_taches_priorite     (id_priorite),
                        KEY idx_taches_statut       (id_statut_tache),
                        KEY idx_taches_matiere      (id_matiere),
                        KEY idx_taches_competence   (id_niveau_competence_requis),
                        KEY idx_taches_difficulte   (id_niveau_difficulte),
                        KEY idx_taches_diff_comp    (id_niveau_difficulte, id_niveau_competence_requis),
                        CONSTRAINT fk_taches_collaborateurs
                            FOREIGN KEY (id_collaborateur)
                                REFERENCES collaborateurs (id_collaborateur)
                                ON UPDATE CASCADE ON DELETE RESTRICT,
                        CONSTRAINT fk_taches_priorite
                            FOREIGN KEY (id_priorite)
                                REFERENCES priorites (id_priorite)
                                ON UPDATE CASCADE ON DELETE RESTRICT,
                        CONSTRAINT fk_taches_statut
                            FOREIGN KEY (id_statut_tache)
                                REFERENCES statuts_tache (id_statut_tache)
                                ON UPDATE CASCADE ON DELETE RESTRICT,
                        CONSTRAINT fk_taches_matiere
                            FOREIGN KEY (id_matiere)
                                REFERENCES matieres (id_matiere)
                                ON UPDATE CASCADE ON DELETE RESTRICT,
                        CONSTRAINT fk_taches_competence
                            FOREIGN KEY (id_niveau_competence_requis)
                                REFERENCES niveaux_competence (id_niveau_competence)
                                ON UPDATE CASCADE ON DELETE RESTRICT,
                        CONSTRAINT fk_taches_difficulte
                            FOREIGN KEY (id_niveau_difficulte)
                                REFERENCES niveaux_difficulte (id_niveau_difficulte)
                                ON UPDATE CASCADE ON DELETE RESTRICT,
    -- Contrainte composite : vérifie la cohérence difficulte/competence
                        CONSTRAINT fk_taches_compatibilite
                            FOREIGN KEY (id_niveau_difficulte, id_niveau_competence_requis)
                                REFERENCES niveaux_difficulte_competence (id_niveau_difficulte, id_niveau_competence)
                                ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
--  PATRON D'HÉRITAGE (Spécialisation) — Ressources documentaires
--  Table mère : ressources
--  Spécialisations : ressources_liens | ressources_fichiers
-- ─────────────────────────────────────────────────────────────

-- Table mère
CREATE TABLE ressources (
                            id_ressource   INT UNSIGNED                NOT NULL AUTO_INCREMENT,
                            titre          VARCHAR(150)                NOT NULL,
                            type_ressource ENUM('lien', 'fichier')     NOT NULL,
                            date_creation  DATETIME                    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            PRIMARY KEY (id_ressource)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Spécialisation 1 : liens externes (URLs)
CREATE TABLE ressources_liens (
                                  id_ressource  INT UNSIGNED  NOT NULL,
                                  url           VARCHAR(255)  NOT NULL,
                                  PRIMARY KEY (id_ressource),
                                  CONSTRAINT fk_lien_parent
                                      FOREIGN KEY (id_ressource)
                                          REFERENCES ressources (id_ressource) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Spécialisation 2 : fichiers locaux (pièces jointes)
CREATE TABLE ressources_fichiers (
                                     id_ressource    INT UNSIGNED  NOT NULL,
                                     nom_original    VARCHAR(190)  NOT NULL,
                                     chemin_stockage VARCHAR(255)  NOT NULL DEFAULT '',
                                     type_mime       VARCHAR(100)  NULL,
                                     PRIMARY KEY (id_ressource),
                                     CONSTRAINT fk_fichier_parent
                                         FOREIGN KEY (id_ressource)
                                             REFERENCES ressources (id_ressource) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
--  RELATION N:M #2  (table de jonction unique pour toute ressource)
--  taches ↔ ressources  (liens ET fichiers)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE taches_ressources (
                                   id_tache         INT UNSIGNED     NOT NULL,
                                   id_ressource     INT UNSIGNED     NOT NULL,
                                   ordre_affichage  TINYINT UNSIGNED NOT NULL DEFAULT 1,
                                   PRIMARY KEY (id_tache, id_ressource),
                                   CONSTRAINT fk_tr_tache
                                       FOREIGN KEY (id_tache)
                                           REFERENCES taches (id_tache) ON DELETE CASCADE,
                                   CONSTRAINT fk_tr_ressource
                                       FOREIGN KEY (id_ressource)
                                           REFERENCES ressources (id_ressource) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
--  SOUS-TÂCHES (ToDoA / ToDoB via ENUM)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE sous_taches (
                             id_sous_tache   INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                             id_tache        INT UNSIGNED     NOT NULL,
                             code_source     VARCHAR(20)      NOT NULL,
                             bloc_todo       ENUM('ToDoA','ToDoB') NOT NULL,
                             libelle         VARCHAR(150)     NOT NULL,
                             commentaire     TEXT             NULL,
                             ordre_affichage TINYINT UNSIGNED NOT NULL DEFAULT 1,
                             est_terminee    TINYINT(1)       NOT NULL DEFAULT 0,
                             PRIMARY KEY (id_sous_tache),
                             UNIQUE KEY uq_sous_taches_code (id_tache, code_source, bloc_todo),
                             CONSTRAINT fk_sous_taches_tache
                                 FOREIGN KEY (id_tache)
                                     REFERENCES taches (id_tache)
                                     ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================
--  DONNÉES Initiales
-- =============================================================

INSERT INTO collaborateurs (id_collaborateur, nom_affichage, email) VALUES
    (1, 'Justine', NULL);

INSERT INTO priorites (id_priorite, libelle, ordre_affichage) VALUES
                                                                  (1, 'Important',     1),
                                                                  (2, 'Haut',          2),
                                                                  (3, 'Moyen',         3),
                                                                  (4, 'Bas',           4),
                                                                  (5, 'Non important', 5);

INSERT INTO statuts_tache (id_statut_tache, libelle, ordre_affichage, est_terminal) VALUES
                                                                                        (1, 'Nouvelle tache',          1, 0),
                                                                                        (2, 'Conception',              2, 0),
                                                                                        (3, 'En cours',                3, 0),
                                                                                        (4, 'Bloque',                  4, 0),
                                                                                        (5, 'En attente de validation',5, 0),
                                                                                        (6, 'Termine',                 6, 1),
                                                                                        (7, 'Archive',                 7, 1);

INSERT INTO matieres (id_matiere, libelle) VALUES
                                               (1,  'HTML/CSS'),
                                               (2,  'JavaScript'),
                                               (3,  'Frameworks JavaScript (React, Vue.js)'),
                                               (4,  'Développement Backend (Node.js, PHP)'),
                                               (5,  'Bases de donnees (SQL, NoSQL)'),
                                               (6,  'UX/UI Design'),
                                               (7,  'Responsive Design et Mobile First'),
                                               (8,  'Gestion de projet web (Agile, Scrum)'),
                                               (9,  'Versioning (Git, GitHub)'),
                                               (10, 'Accessibilité web (WCAG)'),
                                               (11, 'SEO et Référencement'),
                                               (12, 'APIs et Services Web (REST, GraphQL)'),
                                               (13, 'Tests unitaires et integration continue'),
                                               (14, 'Securite web'),
                                               (15, 'CMS et e-commerce (WordPress, Shopify)'),
                                               (16, 'Hebergement et deploiement'),
                                               (17, 'Veille technologique'),
                                               (18, 'Droit du numerique et RGPD'),
                                               (19, 'Communication professionnelle'),
                                               (20, 'Anglais technique'),
                                               (21, 'Projet tutore'),
                                               (22, 'Stage en entreprise'),
                                               (23, 'Memoire professionnel'),
                                               (24, 'Design graphique et logiciels (Photoshop, Illustrator)'),
                                               (25, 'Identite visuelle et branding'),
                                               (26, 'Theorie des couleurs et typographie'),
                                               (27, 'Creation d''icones et illustrations'),
                                               (28, 'Animation et motion design (After Effects)'),
                                               (29, 'Prototypage et maquettage (Figma, Sketch)'),
                                               (30, 'Photographie web et retouche d''images'),
                                               (31, 'Marketing digital et strategie web'),
                                               (32, 'Reseaux sociaux et community management'),
                                               (33, 'Content marketing et redaction web'),
                                               (34, 'E-mail marketing et automation'),
                                               (35, 'Publicite en ligne (Google Ads, Facebook Ads)'),
                                               (36, 'Analytics et mesure de performance (Google Analytics)'),
                                               (37, 'Conversion et optimisation UX'),
                                               (38, 'Influence marketing et partenariats'),
                                               (39, 'Growth hacking et acquisition client'),
                                               (40, 'Webmarketing et e-reputation');

INSERT INTO niveaux_competence (id_niveau_competence, libelle, description, experience_requise, ordre_niveau) VALUES
                                                                                                                  (1, 'Debutant',        'Decouverte des concepts de base',     '0-6 mois',      1),
                                                                                                                  (2, 'Initie',          'Comprehension des fondamentaux',      '6 mois - 1 an', 2),
                                                                                                                  (3, 'Intermediaire',   'Application pratique autonome',       '1-2 ans',       3),
                                                                                                                  (4, 'Confirme',        'Maitrise des techniques avancees',    '2-3 ans',       4),
                                                                                                                  (5, 'Avance',          'Expertise technique approfondie',     '3-5 ans',       5),
                                                                                                                  (6, 'Expert',          'Maitrise complete et innovation',     '5+ ans',        6),
                                                                                                                  (7, 'Expert confirme', 'Leadership technique et mentorat',    '7+ ans',        7),
                                                                                                                  (8, 'Maitre',          'Reference dans le domaine',           '10+ ans',       8);

INSERT INTO niveaux_difficulte (id_niveau_difficulte, libelle, note, ordre_niveau) VALUES
                                                                                       (1, 'Facile',        'Niveau debutant - concepts de base',         1),
                                                                                       (2, 'Moyen',         'Niveau intermediaire - pratique reguliere',  2),
                                                                                       (3, 'Difficile',     'Niveau avance - expertise technique',        3),
                                                                                       (4, 'Tres difficile','Niveau expert - maitrise complete',          4);

-- N:M #1 : difficulte ↔ competence (natif JSON : skillsLevelIds[])
INSERT INTO niveaux_difficulte_competence (id_niveau_difficulte, id_niveau_competence) VALUES
                                                                                           (1, 1), (1, 2),   -- Facile        → Debutant, Initie
                                                                                           (2, 3), (2, 4),   -- Moyen         → Intermédiaire, Confirme
                                                                                           (3, 5), (3, 6),   -- Difficile → Avance, Expert
                                                                                           (4, 7), (4, 8);   -- Tres difficile → Expert confirme, Maitre

-- Tâches (contrainte composite auto-validée via niveaux_difficulte_competence)
INSERT INTO taches
(id_tache, libelle, description,
 date_creation, date_modification, date_completion,
 temps_passe_minutes,
 id_collaborateur, id_priorite, id_statut_tache, id_matiere,
 id_niveau_competence_requis, id_niveau_difficulte)
VALUES
    (1,
     'TP HTML/CSS: Creation d''une page web responsive',
     'Creer une page web responsive en utilisant HTML5 et CSS3 avec les techniques de Flexbox et Grid.',
     '2025-10-01 10:00:00', '2025-10-01 10:00:00', NULL,
     120,
     1, 2, 3, 1, 2, 1),   -- Haut / En cours / HTML-CSS / Initie / Facile

    (2,
     'TP JavaScript: Gestion d''evenements et DOM',
     'Developper une application interactive en JavaScript avec manipulation du DOM et gestion des evenements collaborateur.',
     '2025-10-05 13:30:00', '2025-10-08 14:45:00', NULL,
     180,
     1, 1, 2, 2, 3, 2),   -- Important / Conception / JS / Intermédiaire / Moyen

    (3,
     'Documenter un preprocesseur CSS: Sass',
     'Documenter le preprocesseur Sass, ses avantages, ses inconvenients et les elements de syntaxe essentiels.',
     '2025-10-05 14:30:00', '2025-10-08 16:45:00', NULL,
     110,
     1, 3, 3, 1, 3, 2);   -- Moyen / En cours / HTML-CSS / Intermédiaire / Moyen

-- Ressources : TABLE MÈRE (liens + fichiers mélangés)
INSERT INTO ressources (id_ressource, titre, type_ressource) VALUES
                                                                 -- Liens documentaires (tache 1)
                                                                 (1,  'MDN - HTML',             'lien'),
                                                                 (2,  'CSS-Tricks - Flexbox',   'lien'),
                                                                 -- Liens documentaires (tache 2)
                                                                 (3,  'MDN - JavaScript',       'lien'),
                                                                 (4,  'javascript.info',        'lien'),
                                                                 -- Liens documentaires (tache 3)
                                                                 (5,  'Sass - Syntax',          'lien'),
                                                                 (6,  'Sass - Playground',      'lien'),
                                                                 -- Fichiers joints (tache 1)
                                                                 (7,  'maquette-desktop.png',   'fichier'),
                                                                 (8,  'specifications.pdf',     'fichier'),
                                                                 -- Fichiers joints (tache 2)
                                                                 (9,  'starter-code.js',        'fichier'),
                                                                 (10, 'mockup-interface.jpg',   'fichier'),
                                                                 -- Fichiers joints (tache 3)
                                                                 (11, 'style.sass',             'fichier'),
                                                                 (12, 'style.scss',             'fichier');

-- Spécialisation liens
INSERT INTO ressources_liens (id_ressource, url) VALUES
                                                     (1, 'https://developer.mozilla.org/fr/docs/Web/HTML'),
                                                     (2, 'https://css-tricks.com/snippets/css/a-guide-to-flexbox/'),
                                                     (3, 'https://developer.mozilla.org/fr/docs/Web/JavaScript'),
                                                     (4, 'https://javascript.info/'),
                                                     (5, 'https://sass-lang.com/documentation/syntax/'),
                                                     (6, 'https://sass-lang.com/playground/');

-- Spécialisation fichiers
INSERT INTO ressources_fichiers (id_ressource, nom_original, chemin_stockage, type_mime) VALUES
                                                                                             (7,  'maquette-desktop.png',  '', 'image/png'),
                                                                                             (8,  'specifications.pdf',    '', 'application/pdf'),
                                                                                             (9,  'starter-code.js',       '', 'text/javascript'),
                                                                                             (10, 'mockup-interface.jpg',  '', 'image/jpeg'),
                                                                                             (11, 'style.sass',            '', 'text/x-sass'),
                                                                                             (12, 'style.scss',            '', 'text/x-scss');

-- N : M #2 : taches ↔ ressources (liens + fichiers via table pivot unique)
INSERT INTO taches_ressources (id_tache, id_ressource, ordre_affichage) VALUES
                                                                            -- Tache 1 : liens
                                                                            (1, 1, 1), (1, 2, 2),
                                                                            -- Tache 1 : fichiers
                                                                            (1, 7, 3), (1, 8, 4),
                                                                            -- Tache 2 : liens
                                                                            (2, 3, 1), (2, 4, 2),
                                                                            -- Tache 2 : fichiers
                                                                            (2, 9, 3), (2, 10, 4),
                                                                            -- Tache 3 : liens
                                                                            (3, 5, 1), (3, 6, 2),
                                                                            -- Tache 3 : fichiers
                                                                            (3, 11, 3), (3, 12, 4);

-- Sous-tâches (ENUM ToDoA/ToDoB — plus de table type séparée)
INSERT INTO sous_taches (id_tache, code_source, bloc_todo, libelle, commentaire, ordre_affichage) VALUES
                                                                                                      (1, 'A1', 'ToDoA', 'Structure HTML',   'Creer la structure semantique',             1),
                                                                                                      (1, 'B1', 'ToDoB', 'Styles CSS',       'Appliquer les styles responsive',           2),
                                                                                                      (2, 'A2', 'ToDoA', 'Event Listeners',  'Ajouter les gestionnaires d''evenements',   1),
                                                                                                      (2, 'B2', 'ToDoB', 'Manipulation DOM', 'Modifier dynamiquement les elements',       2),
                                                                                                      (3, 'A2', 'ToDoA', 'Variables Sass',   'Lister et comprendre la syntaxe',           1),
                                                                                                      (3, 'B2', 'ToDoB', 'Mixins',           'Documenter la syntaxe et l''objectif',      2);


-- =============================================================
--  REQUÊTES DE VÉRIFICATION
-- =============================================================

-- Vue complète d'une tâche
SELECT
    t.id_tache,
    t.libelle,
    u.nom_affichage   AS collaborateur,
    p.libelle         AS priorite,
    st.libelle        AS statut,
    m.libelle         AS matiere,
    nc.libelle        AS competence,
    nd.libelle        AS difficulte,
    t.temps_passe_minutes
FROM taches t
         JOIN collaborateurs      u  ON u.id_collaborateur         = t.id_collaborateur
         JOIN priorites          p  ON p.id_priorite             = t.id_priorite
         JOIN statuts_tache      st ON st.id_statut_tache        = t.id_statut_tache
         JOIN matieres           m  ON m.id_matiere              = t.id_matiere
         JOIN niveaux_competence nc ON nc.id_niveau_competence   = t.id_niveau_competence_requis
         JOIN niveaux_difficulte nd ON nd.id_niveau_difficulte   = t.id_niveau_difficulte
ORDER BY t.id_tache;

-- Ressources d'une tâche (liens + fichiers)
SELECT
    t.libelle  AS tache,
    r.type_ressource,
    r.titre,
    rl.url,
    rf.nom_original,
    tr.ordre_affichage
FROM taches_ressources tr
         JOIN taches    t  ON t.id_tache    = tr.id_tache
         JOIN ressources r  ON r.id_ressource = tr.id_ressource
         LEFT JOIN ressources_liens    rl ON rl.id_ressource = r.id_ressource
         LEFT JOIN ressources_fichiers rf ON rf.id_ressource = r.id_ressource
ORDER BY t.id_tache, tr.ordre_affichage;
