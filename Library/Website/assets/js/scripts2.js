
// Gestionnaire pour le formulaire de récupération de mot de passe
document.getElementById('forgot-password-link')?.addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('forgot-password-container').style.display = 'block';
});

// Gestionnaire pour le bouton de recherche
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.querySelector('.search-button');

    // Vérifiez si l'utilisateur est connecté
    const isUserLoggedIn = typeof isUserLoggedIn !== 'undefined' ? isUserLoggedIn : false;

    searchButton?.addEventListener('click', (e) => {
        if (!isUserLoggedIn) {
            e.preventDefault();
            alert('Veuillez vous connecter pour effectuer une recherche.');
            window.location.href = '../public/login.php';
        }
    });

    searchInput?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !isUserLoggedIn) {
            e.preventDefault();
            alert('Veuillez vous connecter pour effectuer une recherche.');
            window.location.href = '../public/login.php';
        }
    });
});

// Gestionnaire pour le tableau de bord (sidebar et sections)
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    const sections = document.querySelectorAll('.content-section');

    toggleBtn?.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
        toggleBtn.classList.toggle('active');
    });

    sidebarLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            sidebarLinks.forEach(link => link.classList.remove('active'));
            link.classList.add('active');
            sections.forEach(section => section.classList.remove('active'));
            const sectionId = link.getAttribute('data-section');
            document.getElementById(sectionId)?.classList.add('active');
        });
    });

    // Initialisation de la première section
    document.getElementById('profile')?.classList.add('active');
});

// Effet de machine à écrire pour le message de bienvenue
document.addEventListener('DOMContentLoaded', () => {
    const welcomeMessage = document.getElementById('welcomeMessage');
    const text = "Bienvenue dans votre espace. Accédez à toutes les fonctionnalités de la bibliothèque.";
    let index = 0;
    let isDeleting = false;
    let speed = 100;

    function typeWriter() {
        if (index < text.length && !isDeleting) {
            welcomeMessage.textContent += text.charAt(index);
            index++;
            setTimeout(typeWriter, speed);
        } else if (index > 0 && isDeleting) {
            welcomeMessage.textContent = text.substring(0, index - 1);
            index--;
            setTimeout(typeWriter, speed / 2);
        } else {
            isDeleting = !isDeleting;
            setTimeout(typeWriter, isDeleting ? 2000 : speed);
        }
    }

    typeWriter();
});
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.querySelector('.search-button');
  
    // Vérifiez si l'utilisateur est connecté (cette variable doit être définie dans le fichier PHP)
    if (typeof isUserLoggedIn === 'undefined') {
        console.error('La variable isUserLoggedIn n\'est pas définie.');
        return;
    }
  
    // Gestionnaire d'événement pour le bouton de recherche
    searchButton?.addEventListener('click', (e) => {
        if (!isUserLoggedIn) {
            e.preventDefault(); // Empêche l'action par défaut
            alert('Veuillez vous connecter pour effectuer une recherche.');
            window.location.href = '../public/login.php'; // Redirige vers la page de connexion
        }
    });
  
    // Gestionnaire d'événement pour la saisie dans le champ de recherche
    searchInput?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !isUserLoggedIn) {
            e.preventDefault(); // Empêche l'action par défaut
            alert('Veuillez vous connecter pour effectuer une recherche.');
            window.location.href = '../public/login.php'; // Redirige vers la page de connexion
        }
    });
  });
  
  