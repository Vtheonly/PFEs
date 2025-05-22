<?php
require_once('../config.php');
require_once('../classes/Bibliothecaire.php');
require_once('../classes/Livre.php');

$message = "";
$form_ID_Livre = "";
$form_Titre = "";
$form_Auteur = "";
$form_Categorie = "";
$form_Statut_Livre = "";

$bibliothecaire = new Bibliothecaire();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $result = $bibliothecaire->handleBookForm($_POST, $_FILES);
    $message = $result["message"] ?? "";
    $form_ID_Livre = $result["form_ID_Livre"] ?? "";
    $form_Titre = $result["form_Titre"] ?? "";
    $form_Auteur = $result["form_Auteur"] ?? "";
    $form_Categorie = $result["form_Categorie"] ?? "";
    $form_Statut_Livre = $result["form_Statut_Livre"] ?? "";
}

$livre = new Livre();
$livres = $livre->search([]);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Gérer les ouvrages</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/cs.css">
    <script src="../assets/js/scripts.js" defer></script>
    <script src="../assets/js/scripts.js"></script>
</head>
<body>
<div class="container">
    <h2>Gestion des ouvrages</h2>
    <?php if ($message): ?>
        <div class="alert <?= strpos($message, 'succès') !== false ? 'alert-success' : 'alert-error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    <div class="form-section">
        <h3>Ajouter / Modifier un ouvrage</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="ID_Livre" id="ID_Livre" value="<?= htmlspecialchars($form_ID_Livre) ?>" />
            <label for="Titre">Titre :</label>
            <input type="text" name="Titre" id="Titre" required value="<?= htmlspecialchars($form_Titre) ?>" />

            <label for="Auteur">Auteur :</label>
            <input type="text" name="Auteur" id="Auteur" required value="<?= htmlspecialchars($form_Auteur) ?>" />

            <label for="Categorie">Catégorie :</label>
            <input type="text" name="Categorie" id="Categorie" required value="<?= htmlspecialchars($form_Categorie) ?>" />

            <label for="Statut_Livre">Statut :</label>
            <select name="Statut_Livre" id="Statut_Livre" required>
                <option value="Disponible" <?= $form_Statut_Livre=="Disponible"?"selected":"" ?>>Disponible</option>
                <option value="Réservé" <?= $form_Statut_Livre=="Réservé"?"selected":"" ?>>Réservé</option>
                <option value="Emprunté" <?= $form_Statut_Livre=="Emprunté"?"selected":"" ?>>Emprunté</option>
                <option value="Indisponible" <?= $form_Statut_Livre=="Indisponible"?"selected":"" ?>>Indisponible</option>
            </select>

            <label for="image">Image du livre (optionnelle) :</label>
            <input type="file" name="image" id="image" accept="image/*" />

            <div style="margin-top:20px;">
                <button type="submit" name="ajouter">Ajouter</button>
                <button type="submit" name="modifier">Modifier</button>
                <button type="submit" name="supprimer">Supprimer</button>
            </div>
        </form>
    </div>

    <h3>Liste des ouvrages</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Auteur</th>
                <th>Catégorie</th>
                <th>Statut</th>
                <th>Image</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($livres as $l): ?>
            <tr>
                <td><?= htmlspecialchars($l['ID_Livre']) ?></td>
                <td><?= htmlspecialchars($l['Titre']) ?></td>
                <td><?= htmlspecialchars($l['Auteur']) ?></td>
                <td><?= htmlspecialchars($l['Categorie']) ?></td>
                <td><?= htmlspecialchars($l['Statut_Livre']) ?></td>
                <td>
                    <?php if (!empty($l['Image'])): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($l['Image']) ?>" alt="Image du livre" />
                    <?php else: ?>
                        <span style="color:#888;">Aucune image</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="action-btns">
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="ID_Livre" value="<?= htmlspecialchars($l['ID_Livre']) ?>" />
                            <input type="hidden" name="Titre" value="<?= htmlspecialchars($l['Titre']) ?>" />
                            <input type="hidden" name="Auteur" value="<?= htmlspecialchars($l['Auteur']) ?>" />
                            <input type="hidden" name="Categorie" value="<?= htmlspecialchars($l['Categorie']) ?>" />
                            <input type="hidden" name="Statut_Livre" value="<?= htmlspecialchars($l['Statut_Livre']) ?>" />
                            <button type="submit" name="remplir" title="Modifier ce livre"><i class="fa fa-edit"></i></button>
                        </form>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="ID_Livre" value="<?= htmlspecialchars($l['ID_Livre']) ?>" />
                            <button type="submit" name="supprimer" title="Supprimer ce livre"><i class="fa fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
