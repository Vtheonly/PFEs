<?php
session_start();

require_once('../config.php');
require_once('../classes/Suggestion.php');

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION["ID_Utilisateur"])) {
    header("Location: login.php");
    exit();
}

// Gérer la page des suggestions
$result = Suggestion::handleSuggestionPage($_SESSION["ID_Utilisateur"], $_SERVER["REQUEST_METHOD"] === "POST" ? $_POST : null);

$message = $result["message"];
$messageType = $result["messageType"];
$titre = $result["titre"];
$auteur = $result["auteur"];
$categorie = $result["categorie"];
$suggestions = $result["suggestions"];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Proposer un livre</title>
    <link rel="stylesheet" href="../assets/css/cs2.css">
</head>
<body class="suggestion-page">
    <div class="container-wrapper">
        <div class="container" role="main" aria-labelledby="pageTitle">
            <h2 id="pageTitle">Proposer un livre</h2>

            <?php if (!empty($message)): ?>
                <div class="alert <?php echo $messageType === "success" ? "success" : "error"; ?>" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <label for="Titre">Titre du livre :</label>
                <input type="text" id="Titre" name="Titre" placeholder="Entrez le titre du livre" required
                    value="<?php echo htmlspecialchars($titre); ?>" aria-required="true" />

                <label for="Auteur">Auteur :</label>
                <input type="text" id="Auteur" name="Auteur" placeholder="Entrez le nom de l'auteur" required
                    value="<?php echo htmlspecialchars($auteur); ?>" aria-required="true" />

                <label for="Categorie">Catégorie :</label>
                <input type="text" id="Categorie" name="Categorie" placeholder="Entrez la catégorie du livre" required
                    value="<?php echo htmlspecialchars($categorie); ?>" aria-required="true" />

                <button type="submit">Proposer</button>
            </form>
        </div>

        <div class="container" aria-label="Liste des suggestions">
            <h2>Vos Suggestions</h2>
            <?php if (!empty($suggestions)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Auteur</th>
                            <th>Catégorie</th>
                            <th>Date</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suggestions as $suggestion): 
                            $statut = strtolower($suggestion['Statut_Suggestion']);
                            $class = 'status-badge ';
                            if ($statut === 'validée' || $statut === 'valide') $class .= 'valide';
                            elseif ($statut === 'refusée' || $statut === 'refuse') $class .= 'refuse';
                            else $class .= 'en_attente';
                        ?>
                            <tr>
                                <td data-label="Titre"><?php echo htmlspecialchars($suggestion['Titre']); ?></td>
                                <td data-label="Auteur"><?php echo htmlspecialchars($suggestion['Auteur']); ?></td>
                                <td data-label="Catégorie"><?php echo htmlspecialchars($suggestion['Categorie']); ?></td>
                                <td data-label="Date"><?php echo htmlspecialchars($suggestion['Date_Suggestion']); ?></td>
                                <td data-label="Statut">
                                    <span class="<?php echo $class; ?>">
                                        <?php echo ucfirst(htmlspecialchars($suggestion['Statut_Suggestion'])); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Aucune suggestion trouvée.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>