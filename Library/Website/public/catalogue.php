<?php
session_start();

require_once('../config.php');
require_once('../classes/Livre.php');

$message = "";
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

$livres = [];

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $result = Livre::handleSearchRequest($_GET);
    $livres = $result['livres'];
    if (!empty($result['message'])) {
        $message = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Catalogue des Livres</title>
    <head>
    <link rel="stylesheet" href="../assets/css/cs2.css">
</head>
<body class="catalogue-page">
<div class="catalog-container">
    <h2>Catalogue des Livres</h2>
    <?php if ($message): ?>
        <div class="alert">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    <form method="GET" class="catalog-form">
        <div class="form-row">
            <label for="Titre">Titre:</label>
            <input type="text" id="Titre" name="Titre" placeholder="Rechercher par titre"/>
        </div>
        <div class="form-row">
            <label for="Auteur">Auteur:</label>
            <input type="text" id="Auteur" name="Auteur" placeholder="Rechercher par auteur"/>
        </div>
        <div class="form-row">
            <label for="Categorie">Catégorie:</label>
            <input type="text" id="Categorie" name="Categorie" placeholder="Rechercher par catégorie"/>
        </div>
        <button type="submit" class="btn-submit">Rechercher</button>
    </form>

    <?php if (!empty($livres)): ?>
    <table class="catalog-table">
        <thead>
        <tr>
            <th>Image</th> <!-- Nouvelle colonne pour l'image -->
            <th>Titre</th>
            <th>Auteur</th>
            <th>Catégorie</th>
            <th>Statut</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($livres as $livre): ?>
            <tr>
                <td>
                    <?php if (!empty($livre['Image'])): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($livre['Image']) ?>" alt="Image du livre" style="width: 50px; height: auto; border-radius: 5px;" />
                    <?php else: ?>
                        <span style="color:#888;">Aucune image</span>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($livre['Titre']); ?></td>
                <td><?php echo htmlspecialchars($livre['Auteur']); ?></td>
                <td><?php echo htmlspecialchars($livre['Categorie']); ?></td>
                <td><span class="status <?php echo htmlspecialchars($livre['Statut_Livre']); ?>">
                        <?php echo htmlspecialchars($livre['Statut_Livre']); ?>
                    </span></td>
                <td>
                    <?php if ($livre['Statut_Livre'] === 'Disponible'): ?>
                        <form method="post" action="reservation.php">
    <input type="hidden" name="ID_Livre" value="<?php echo htmlspecialchars($livre['ID_Livre']); ?>">
    <button type="submit" class="btn-submit">Reserver</button>
</form>

                    <?php else: ?>
                        Indisponible
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="no-results"><?php echo $message; ?></p>
<?php endif; ?>
</div>
</body>
</html>
