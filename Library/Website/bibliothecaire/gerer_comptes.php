<?php
require_once('../config.php');
require_once('../classes/User.php');

// Modification d'un utilisateur
if (isset($_POST['update_user'])) {
    $result = User::updateUser($_POST);
    User::handleResult($result, 'gerer_comptes.php');
}

// Suppression d'un utilisateur
if (isset($_POST['delete_id'])) {
    User::deleteById($_POST['delete_id']);
    header("Location: gerer_comptes.php?success=suppr");
    exit();
}

// Récupération des utilisateurs
$users = User::getAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Gérer les comptes utilisateurs</title>
    <link rel="stylesheet" href="../assets/css/cs.css">
</head>
<body>
    <div class="container">
        <h2>Gestion des comptes utilisateurs</h2>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                <?php
                if ($_GET['success'] === 'modif') echo "Utilisateur modifié avec succès.";
                elseif ($_GET['success'] === 'suppr') echo "Utilisateur supprimé avec succès.";
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <?php
                if ($_GET['error'] === 'invalid_data') echo "Données invalides fournies. Veuillez vérifier vos entrées.";
                else echo "Une erreur est survenue.";
                ?>
            </div>
        <?php endif; ?>

        <?php
        // Formulaire de modification
        if (isset($_POST['edit_id'])):
            $editUser = User::getById($_POST['edit_id']);
            if ($editUser):
        ?>
            <form method="post" class="edit-form" autocomplete="off" novalidate>
                <input type="hidden" name="update_id" value="<?= htmlspecialchars($editUser['ID_Utilisateur']) ?>" />
                <label for="nom">Nom:
                    <input id="nom" type="text" name="Nom_u" placeholder="Entrez le nom" value="<?= htmlspecialchars($editUser['Nom_u']) ?>" required />
                </label>
                <label for="prenom">Prénom:
                    <input id="prenom" type="text" name="Prenom_u" placeholder="Entrez le prénom" value="<?= htmlspecialchars($editUser['Prenom_u']) ?>" required />
                </label>
                <label for="email">Email:
                    <input id="email" type="email" name="Email_u" placeholder="exemple@domaine.com" value="<?= htmlspecialchars($editUser['Email_u']) ?>" required />
                </label>
                <label for="role">Rôle:
                    <select id="role" name="Role" required>
                        <option value="" disabled>Choisissez un rôle</option>
                        <option value="Etudiant" <?= $editUser['Role'] === 'Etudiant' ? 'selected' : '' ?>>Etudiant</option>
                        <option value="Enseignant" <?= $editUser['Role'] === 'Enseignant' ? 'selected' : '' ?>>Enseignant</option>
                    </select>
                </label>
                <label for="statut">Statut:
                    <select id="statut" name="Statut_Compte" required>
                        <option value="" disabled>Choisissez un statut</option>
                        <option value="Actif" <?= $editUser['Statut_Compte'] === 'Actif' ? 'selected' : '' ?>>Actif</option>
                        <option value="Désactivé" <?= $editUser['Statut_Compte'] === 'Désactivé' ? 'selected' : '' ?>>Désactivé</option>
                    </select>
                </label>
                <button type="submit" name="update_user" class="btn-action">Enregistrer</button>
            </form>
        <?php
            endif;
        endif;
        ?>

        <table>
        <thead>
    <tr>
        <th>Nom</th>
        <th>Prénom</th>
        <th>Email</th>
        <th>Matricule</th> 
        <th>Rôle</th>
        <th>Statut</th>
        <th>Actions</th>
    </tr>
</thead>
<tbody>
    <?php if (!empty($users)): ?>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= htmlspecialchars($u['Nom_u']) ?></td>
            <td><?= htmlspecialchars($u['Prenom_u']) ?></td>
            <td><?= htmlspecialchars($u['Email_u']) ?></td>
            <td><?= htmlspecialchars($u['Matricule']) ?></td> <!-- Nouvelle cellule -->
            <td><?= htmlspecialchars($u['Role']) ?></td>
            <td><?= htmlspecialchars($u['Statut_Compte']) ?></td>
            <td>
                <form method="post" class="inline">
                    <input type="hidden" name="edit_id" value="<?= htmlspecialchars($u['ID_Utilisateur']) ?>" />
                    <button class="btn-action" type="submit" title="Modifier cet utilisateur">Modifier</button>
                </form>
                <form method="post" class="inline">
                    <input type="hidden" name="delete_id" value="<?= htmlspecialchars($u['ID_Utilisateur']) ?>" />
                    <button class="btn-action delete" type="submit" onclick="return confirm('Supprimer ce compte ?')" title="Supprimer cet utilisateur">Supprimer</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" style="text-align:center; font-style: italic; color: #666;">
                Aucun utilisateur trouvé.
            </td>
        </tr>
    <?php endif; ?>
</tbody>
        </table>
    </div>
</body>
</html>
