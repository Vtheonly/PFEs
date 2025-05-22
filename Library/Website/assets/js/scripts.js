// Script pour afficher le formulaire de récupération de mot de passe
document.getElementById('forgot-password-link')?.addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('forgot-password-container').style.display = 'block';
});

// Script pour faire défiler automatiquement la fenêtre de chat vers le bas
window.onload = function () {
    var box = document.getElementById('messenger-box');
    if (box) box.scrollTop = box.scrollHeight;
};

// Script pour remplir le formulaire avec les données d'un livre à modifier
function remplirForm(id, titre, auteur, categorie, statut) {
    document.getElementById('ID_Livre').value = id;
    document.getElementById('Titre').value = titre;
    document.getElementById('Auteur').value = auteur;
    document.getElementById('Categorie').value = categorie;
    document.getElementById('Statut_Livre').value = statut;
    window.scrollTo({top: 0, behavior: 'smooth'});
}

// Script pour gérer l'affichage des sections dans le tableau de bord
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
        document.getElementById(sectionId).classList.add('active');
    });
});

// Script pour afficher un message de bienvenue avec effet de machine à écrire
document.addEventListener('DOMContentLoaded', () => {
    const welcomeMessage = document.getElementById('welcomeMessage');
    const text = "Bienvenue dans votre espace bibliothécaire. Accédez aux outils de gestion des prêts, réservations et collections";
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