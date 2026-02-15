<?php
/**
 * Gestion des utilisateurs - Interface admin
 */

require_once __DIR__ . '/../config/database.php';

$page_title = 'Gestion des Utilisateurs';

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Accès non autorisé.";
    redirect('/ecommerce/index.php');
}

// Récupérer tous les utilisateurs
$pdo = getDBConnection();
$users = [];

if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT * FROM users 
            ORDER BY date_inscription DESC
        ");
        $users = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des utilisateurs: " . $e->getMessage());
    }
}

include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="/ecommerce/assets/css/admin-users.css">

<div class="admin-container">
    <div class="admin-header">
        <h1> Gestion des Utilisateurs</h1>
        <a href="/ecommerce/admin/index.php" class="btn-back">
            ⬅️ Retour au tableau de bord
        </a>
    </div>

    <?php if (count($users) > 0): ?>

        <!-- Table des utilisateurs -->
        <div class="users-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Date d'inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr class="<?php echo $user['role'] === 'admin' ? 'admin-row' : ''; ?>">
                            <td class="user-id">#<?php echo $user['id']; ?></td>
                            <td>
                                <div class="user-info">
                                    <span class="user-name"><?php echo escape($user['prenom'] . ' ' . $user['nom']); ?></span>
                                    <?php if ($user['id'] === $_SESSION['user_id']): ?>
                                        <span class="you-badge">Vous</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="user-email"><?php echo escape($user['email']); ?></td>
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="role-badge role-admin"> Administrateur</span>
                                <?php else: ?>
                                    <span class="role-badge role-user"> Utilisateur</span>
                                <?php endif; ?>
                            </td>
                            <td class="user-date"><?php echo date('d/m/Y à H:i', strtotime($user['date_inscription'])); ?></td>
                            <td class="action-buttons">
                                <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                    <!-- Bouton Rôle -->
                                    <a href="user-role.php?id=<?php echo $user['id']; ?>" 
                                       class="btn-role">
                                        Rôle
                                    </a>
                                    
                                    <!-- Bouton Supprimer -->
                                    <a href="user-delete.php?id=<?php echo $user['id']; ?>" 
                                       class="btn-supprimer"
                                       onclick="return confirm('Supprimer cet utilisateur ?');">
                                        Supprimer
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Info -->

        <div class="info-box">
            <strong>Information :</strong> Les lignes en bleu indiquent les comptes administrateurs. Vous ne pouvez pas modifier votre propre compte.
        </div>

    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon"></div>
            <h3>Aucun utilisateur</h3>
            <p>Aucun utilisateur trouvé dans la base de données.</p>
        </div>
    <?php endif; ?>
</div>

<?php include ROOT_PATH . '/includes/footer.php'; ?>