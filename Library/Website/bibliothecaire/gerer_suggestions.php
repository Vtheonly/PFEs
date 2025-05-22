<?php

require_once('../config.php');
require_once('../classes/Suggestion.php');

// Définir les statuts valides
$statuts_valides = ['En attente', 'Traitée'];
$message = ""; // Pour les messages de succès/erreur

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = Suggestion::handleStatusUpdate($_POST, $statuts_valides);
    if (strpos($message, 'succès') !== false) {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

$suggestions = Suggestion::getAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer les suggestions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
          integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn6+CnrXvc9jSjlkV0fLl9mVk9c+Nwvo6n35DSjgeH2IUHQN3GhXcjw=="
          crossorigin="anonymous" referrerpolicy="no-referrer" />
          <link rel="stylesheet" href="../assets/css/cs.css">
</head>
<body>
    <div class="container">
        <h2>Gestion des suggestions</h2>

        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, 'succès') !== false ? 'alert-success' : 'alert-error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Titre suggéré</th>
                    <th>Auteur</th>
                    <th>Date</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($suggestions)): ?>
                    <?php foreach ($suggestions as $s): ?>
                    <tr>
                        <td data-label="Utilisateur"><?= htmlspecialchars(trim(($s['Prenom_u'] ?? '') . ' ' . ($s['Nom_u'] ?? ''))) ?></td>
                        <td data-label="Titre"><?= htmlspecialchars($s['Titre']) ?></td>
                        <td data-label="Auteur"><?= htmlspecialchars($s['Auteur']) ?></td>
                        <td data-label="Date"><?= htmlspecialchars($s['Date_Suggestion']) ?></td>
                        <td data-label="Statut">
                            <form method="post" action="">
                                <input type="hidden" name="id_suggestion" value="<?= (int)$s['ID_Suggestion'] ?>">
                                <select name="nouveau_statut" onchange="this.form.submit()">
                                    <?php foreach ($statuts_valides as $statut): ?>
                                        <option value="<?= htmlspecialchars($statut) ?>" <?= $statut === $s['Statut_Suggestion'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($statut) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <noscript><button type="submit"><i class="fas fa-sync-alt"></i> Modifier</button></noscript>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; font-style: italic; color: #666;">Aucune suggestion trouvée.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
