<?php
/**
 * Admin - Gestion newsletter
 */
require_once '../includes/config.php';
require_once '../includes/newsletter.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Newsletter';
$message = '';

// Envoi de newsletter
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_newsletter'])) {
    $subject = $_POST['subject'] ?? '';
    $content = $_POST['content'] ?? '';
    
    if ($subject && $content) {
        $result = sendNewsletter($subject, $content);
        $message = '<div class="alert alert-success">Newsletter envoyée : ' . $result['sent'] . ' succès, ' . $result['failed'] . ' échecs</div>';
    } else {
        $message = '<div class="alert alert-error">Veuillez remplir tous les champs</div>';
    }
}

// Ajout manuel d'un inscrit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subscriber'])) {
    $email = $_POST['email'] ?? '';
    $nom = $_POST['nom'] ?? '';
    
    $result = subscribeNewsletter($email, $nom);
    $message = $result['success'] 
        ? '<div class="alert alert-success">' . $result['message'] . '</div>'
        : '<div class="alert alert-error">' . $result['message'] . '</div>';
}

// Stats
$subscribers = getNewsletterSubscribers();
$totalSubscribers = count($subscribers);

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <h1 class="admin-title">Newsletter</h1>
    
    <?php echo $message; ?>
    
    <!-- Stats -->
    <div class="admin-section">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; text-align: center;">
                <div style="font-size: 48px; font-weight: 700;"><?php echo $totalSubscribers; ?></div>
                <div>Inscrits</div>
            </div>
            <div style="background: var(--color-gray-lighter); padding: 30px; border-radius: 12px; text-align: center;">
                <div style="font-size: 48px; font-weight: 700; color: var(--color-primary);">0</div>
                <div>Envoyés ce mois</div>
            </div>
            <div style="background: var(--color-gray-lighter); padding: 30px; border-radius: 12px; text-align: center;">
                <div style="font-size: 48px; font-weight: 700; color: var(--color-primary);">0%</div>
                <div>Taux d'ouverture</div>
            </div>
        </div>
    </div>
    
    <!-- Envoi newsletter -->
    <div class="admin-section">
        <h2 style="margin-bottom: 20px;">✉️ Envoyer une newsletter</h2>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Sujet</label>
                <input type="text" name="subject" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Contenu HTML</label>
                <textarea name="content" class="form-control" rows="10" required placeholder="<h2>Bonjour !</h2><p>Votre message ici...</p>"></textarea>
                <small style="color: var(--color-gray);">HTML autorisé. Un lien de désinscription sera ajouté automatiquement.</small>
            </div>
            <button type="submit" name="send_newsletter" class="btn btn-primary btn-lg" 
                    onclick="return confirm('Envoyer cette newsletter à <?php echo $totalSubscribers; ?> inscrits ?')">
                🚀 Envoyer la newsletter
            </button>
        </form>
    </div>
    
    <!-- Ajouter un inscrit -->
    <div class="admin-section">
        <h2 style="margin-bottom: 20px;">➕ Ajouter un inscrit</h2>
        <form method="POST" style="display: flex; gap: 15px;">
            <input type="email" name="email" class="form-control" placeholder="Email" required style="flex: 2;">
            <input type="text" name="nom" class="form-control" placeholder="Nom (optionnel)" style="flex: 1;">
            <button type="submit" name="add_subscriber" class="btn btn-primary">Ajouter</button>
        </form>
    </div>
    
    <!-- Liste des inscrits -->
    <div class="admin-section">
        <h2 style="margin-bottom: 20px;">📋 Liste des inscrits</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Nom</th>
                    <th>Date d'inscription</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($subscribers, 0, 50) as $sub): ?>
                <tr>
                    <td><?php echo htmlspecialchars($sub['email']); ?></td>
                    <td><?php echo htmlspecialchars($sub['nom']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($sub['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (count($subscribers) > 50): ?>
        <p style="text-align: center; color: var(--color-gray); margin-top: 15px;">
            Affichage des 50 derniers inscrits sur <?php echo $totalSubscribers; ?>
        </p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
