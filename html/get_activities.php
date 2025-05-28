<?php
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['enseignants'])) {
    die("Accès non autorisé");
}

$stmt = $pdo->prepare("SELECT * FROM activites WHERE enseignant_id = ? ORDER BY date_creation DESC LIMIT 5");
$stmt->execute([$_SESSION['enseignants']['id']]);
$activites = $stmt->fetchAll();

foreach ($activites as $activite): ?>
<div class="activity-item">
    <div class="activity-marker <?= $activite['type'] === 'video' ? 'video' : 'upload' ?>">
        <i class="fas fa-<?= $activite['type'] === 'video' ? 'video' : 'file-upload' ?>"></i>
    </div>
    <div class="activity-card">
        <div class="activity-header">
            <span class="activity-type <?= $activite['type'] === 'video' ? 'video' : 'upload' ?>">
                <?= $activite['type'] === 'video' ? 'Visioconférence' : 'Publication' ?>
            </span>
            <span class="activity-time">
                <i class="far fa-clock"></i>
                <?= date('d/m/Y H:i', strtotime($activite['date_creation'])) ?>
            </span>
        </div>
        <div class="activity-content">
            <p><?= htmlspecialchars($activite['description']) ?></p>
            <?php if ($activite['type'] === 'file' && !empty($activite['fichier_nom'])): ?>
            <div class="activity-file">
                <i class="fas fa-paperclip"></i>
                <span><?= htmlspecialchars($activite['fichier_nom']) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>