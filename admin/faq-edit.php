<?php
/**
 * Admin - Édition FAQ
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Ajouter une question';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$faq = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM faq WHERE id = ?");
    $stmt->execute([$id]);
    $faq = $stmt->fetch();
    if (!$faq) {
        header('Location: faq.php');
        exit;
    }
    $page_title = 'Modifier une question';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'question' => $_POST['question'] ?? '',
        'reponse' => $_POST['reponse'] ?? '',
        'categorie' => $_POST['categorie'] ?? 'general',
        'ordre_affichage' => intval($_POST['ordre_affichage'] ?? 0),
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];

    if ($id) {
        $sql = "UPDATE faq SET question=?, reponse=?, categorie=?, ordre_affichage=?, is_active=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$data['question'], $data['reponse'], $data['categorie'], $data['ordre_affichage'], $data['is_active'], $id]);
    } else {
        $sql = "INSERT INTO faq (question, reponse, categorie, ordre_affichage, is_active) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$data['question'], $data['reponse'], $data['categorie'], $data['ordre_affichage'], $data['is_active']]);
    }

    header('Location: faq.php');
    exit;
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <h1 class="admin-title"><?php echo $faq ? 'Modifier' : 'Ajouter'; ?> une question</h1>

    <div class="admin-section">
        <div class="section-header">
            <h2><?php echo $faq ? 'Modifier la question #' . $faq['id'] : 'Nouvelle question FAQ'; ?></h2>
            <a href="faq.php" class="btn btn-sm btn-outline">Retour à la liste</a>
        </div>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Question *</label>
                <textarea name="question" class="form-control" rows="3" required><?php echo htmlspecialchars($faq['question'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Réponse</label>
                <textarea name="reponse" class="form-control" rows="6"><?php echo htmlspecialchars($faq['reponse'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Catégorie</label>
                <select name="categorie" class="form-control">
                    <option value="general" <?php echo ($faq['categorie'] ?? '') == 'general' ? 'selected' : ''; ?>>Général</option>
                    <option value="prix" <?php echo ($faq['categorie'] ?? '') == 'prix' ? 'selected' : ''; ?>>Prix</option>
                    <option value="modeles" <?php echo ($faq['categorie'] ?? '') == 'modeles' ? 'selected' : ''; ?>>Modèles</option>
                    <option value="delai" <?php echo ($faq['categorie'] ?? '') == 'delai' ? 'selected' : ''; ?>>Délai</option>
                    <option value="garanties" <?php echo ($faq['categorie'] ?? '') == 'garanties' ? 'selected' : ''; ?>>Garanties</option>
                    <option value="normes" <?php echo ($faq['categorie'] ?? '') == 'normes' ? 'selected' : ''; ?>>Normes</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Ordre d'affichage</label>
                <input type="number" name="ordre_affichage" class="form-control" value="<?php echo $faq['ordre_affichage'] ?? '0'; ?>">
            </div>

            <div class="form-check" style="margin: 20px 0;">
                <input type="checkbox" id="is_active" name="is_active" <?php echo ($faq['is_active'] ?? 1) ? 'checked' : ''; ?>>
                <label for="is_active">Question active (visible sur le site)</label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Enregistrer</button>
        </form>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
