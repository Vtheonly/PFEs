<?php
include('../templates/header.php');
require_once('../config.php');
require_once('../classes/Livre.php');

// --- Récupérer 3 livres différents chaque jour ---
$livre = new Livre();
$allLivres = $livre->search([]);
$seed = intval(date('Ymd'));
srand($seed);
shuffle($allLivres);
$livresDuJour = array_slice($allLivres, 0, 3);
?>

<link rel="stylesheet" href="../assets/css/page1.css">

<div class="image-slider">
    <div class="slider-container">
      <img src="../assets/img/clock.jpg" alt="Bibliothèque UMB" class="slider-image">
      <img src="../assets/img/book.jpg" alt="Espace lecture" class="slider-image">
      <img src="../assets/img/book2.jpg" alt="Salle de recherche" class="slider-image">
    </div>
</div>

<!-- Barre de recherche -->
<section class="search-section">
    <div class="search-container">
    <input type="text" id="searchInput" class="search-input" data-i18n-placeholder="search_placeholder" placeholder="Rechercher des livres, articles...">
      <button class="search-button"> 🔍
        <i class="fas fa-search"></i>
      </button>
    </div>
    <div class="search-options">
      <div class="search-option">
        <input type="radio" id="search-all" name="search-type" checked>
        <label for="search-all" data-i18n="all">Tous</label>
      </div>
      <div class="search-option">
        <input type="radio" id="search-books" name="search-type">
        <label for="search-books" data-i18n="books">Livres</label>
      </div>
      <div class="search-option">
        <input type="radio" id="search-articles" name="search-type">
        <label for="search-articles" data-i18n="articles">Articles</label>
      </div>
    </div>
</section>

<!-- Contenu principal -->
<main class="main-container">
    <section class="hero-section">
      <!-- Carte de bienvenue avec les livres du jour -->
      <div class="welcome-card">
    <h1 data-i18n="welcome_title">Bienvenue à la Bibliothèque du Département d'Informatique</h1>
    
    <div class="welcome-text">
        <p data-i18n="text">La bibliothèque du département d'Informatique de l'université Mohamed Bougara Boumerdès, 
        située au premier étage du bloc 5, est bien plus qu'un simple dépôt de livres. 
        Centre névralgique pour les étudiants et enseignants, elle offre :</p>
        
        <ul class="features-list">
            <li data-i18n="feature_1"><i class="fas fa-check-circle" ></i> Une collection riche en ouvrages spécialisés et mémoires</li>
            <li data-i18n="feature_2"><i class="fas fa-check-circle"  ></i> Un nouveau système de <strong>réservation en ligne</strong> pour gagner du temps</li>
            <li data-i18n="feature_3"><i class="fas fa-check-circle" ></i> Des espaces de travail modernes et connectés</li>
        </ul>
        
        <div class="innovation-notice">
            <h3 data-i18n="innovation_title"><i class="fas fa-bolt"  ></i> Notre innovation</h3>
            <p  data-i18n="innovation_description">Nous avons digitalisé nos services pour vous offrir une expérience simplifiée:</p>
            
            <div class="improvements">
                <div class="improvement-item">
                    <i class="fas fa-clock"></i>
                    <span  data-i18n="reservation_24h">Réservation 24h/24</span>
                </div>
                <div class="improvement-item">
                    <i class="fas fa-search"></i>
                    <span  data-i18n="real_time_availability">Disponibilité en temps réel</span>
                </div>
                <div class="improvement-item">
                    <i class="fas fa-bell"></i>
                    <span  data-i18n="automatic_alerts">Alertes automatiques</span>
                </div>
            </div>
        </div>
       </div>
       <h3 data-i18n="recommendations_title">Nos recommandations du jour</h3>
      <div class="books-of-the-day">
  
            <?php foreach ($livresDuJour as $l): ?>
                <div class="book-of-day">
                    <div class="book-of-day-img">
                        <?php if (!empty($l['Image'])): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($l['Image']) ?>" alt="Couverture du livre" />
                        <?php else: ?>
                            <img src="../assets/img/introduction_a_algo.jpg" alt="Pas d'image" />
                        <?php endif; ?>
                    </div>
                    <div class="book-of-day-title"><?= htmlspecialchars($l['Titre']) ?></div>
                    <div class="book-of-day-author"><?= htmlspecialchars($l['Auteur']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
      </div>
      <!-- Sidebar avec horaires et calendrier -->
      <div class="sidebar">
        <!-- Horaires d'ouverture -->
        <div class="opening-hours">
          <h2><i class="fas fa-clock"></i> <span data-i18n="opening_hours">Horaires d'ouverture</span></h2>
          <div class="time-indicator" id="library-status">
            <i class="fas fa-door-open"></i>
            <div class="time-text">
              <h3 data-i18n="open_now">Ouvert maintenant</h3>
              <p data-i18n="closing_soon">Fermeture dans <span class="countdown-timer" id="countdown-timer">3h 45m</span></p>
            </div>
          </div>
          <table class="hours-table">
            <tr>
              <th data-i18n="sunday">Dimanche</th>
              <td class="today">09:00 - 16:00</td>
            </tr>
            <tr>
              <th data-i18n="monday">Lundi</th>
              <td>09:00 - 16:00</td>
            </tr>
            <tr>
              <th data-i18n="tuesday">Mardi</th>
              <td>09:00 - 16:00</td>
            </tr>
            <tr>
              <th data-i18n="wednesday">Mercredi</th>
              <td>09:00 - 16:00</td>
            </tr>
            <tr>
              <th data-i18n="thursday">Jeudi</th>
              <td>09:00 - 16:00</td>
            </tr>
            <tr>
              <th data-i18n="friday">Vendredi</th>
              <td data-i18n="closed">Fermé</td>
            </tr>
            <tr>
              <th data-i18n="saturday">Samedi</th>
              <td data-i18n="closed">Fermé</td>
            </tr>
          </table>
        </div>
        <!-- Calendrier -->
        <div class="calendar-widget">
          <div class="calendar-header">
            <h2><i class="fas fa-calendar-alt"></i> <span data-i18n="calendar">Calendrier</span></h2>
            <div class="calendar-nav">
              <button id="prev-month"><i class="fas fa-chevron-left"></i></button>
              <button id="next-month"><i class="fas fa-chevron-right"></i></button>
            </div>
          </div>
          <div class="calendar-grid" id="calendar-grid">
            <div class="calendar-day-header" data-i18n="sun">Dim</div>
            <div class="calendar-day-header" data-i18n="mon">Lun</div>
            <div class="calendar-day-header" data-i18n="tue">Mar</div>
            <div class="calendar-day-header" data-i18n="wed">Mer</div>
            <div class="calendar-day-header" data-i18n="thu">Jeu</div>
            <div class="calendar-day-header" data-i18n="fri">Ven</div>
            <div class="calendar-day-header" data-i18n="sat">Sam</div>
            <!-- Les jours du mois seront ajoutés dynamiquement -->
          </div>
        </div>
      </div>
    </section>
    <button id="dark-mode-toggle"> 🌙
        <i class="fas fa-moon"></i>
    </button>
</main>
<script>
    // Définir la variable isUserLoggedIn pour le script externe
    const isUserLoggedIn = <?php echo isset($_SESSION['ID_Utilisateur']) || isset($_SESSION['ID_Bibliothecaire']) ? 'true' : 'false'; ?>;
</script>
<script src="../assets/js/scripts2.js"></script>
<?php include('../templates/footer.php'); ?>
<script src="../assets/js/script.js"></script>
