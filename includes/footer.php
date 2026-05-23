<?php
/**
 * Template de pied de page HTML.
 *
 * Ferme le conteneur principal, affiche le footer,
 * charge Bootstrap JS (CDN) et les scripts spécifiques à la page
 * (variable optionnelle $pageScripts définie par les pages qui en ont besoin).
 *
 * @author  Laurent Boyer — Groupe 3 LPDWCA
 * @version 1.0
 *
 * @var string $pageScripts
 */
?>
<?php
// PhpStorm signale des "closing tag matches nothing" sur </div>, </body>, </html>
// car ce template partiel est inclus par les pages principales qui ouvrent ces balises
// dans header.php. C'est un comportement normal du pattern header/footer en PHP.
?>
</div>
<footer class="text-center text-muted py-4 mt-5 border-top">
    <small>To Do List &mdash; LPDWCA Laurent Boyer &mdash; <?= date('Y') ?></small>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($pageScripts)): ?>
    <?= $pageScripts ?>
<?php endif; ?>
</body>
</html>
