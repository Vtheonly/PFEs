// assets/js/script.js
document.addEventListener('DOMContentLoaded', function () {
    // Ajout d'animations supplémentaires si nécessaire
});
 // Traductions
 const translations = {
  fr: {
      university_name: "Université M'hamed Bougara-Boumerdès",
      library_name: "Faculté des Sciences-Departement d'Informatique",
      home: "Accueil",
      contact: "Contact",
      connect: "Se connecter",
      connect_user: "Se connecter comme utilisateur",
      logout: "Déconnexion",
      register: "Inscription",
      connect_librarian: "Se connecter comme bibliothécaire",
      welcome_title: "Bienvenue à la Bibliothèque du Département d'Informatique",
      text :" La bibliothèque du département d'Informatique de l'université Mohamed Bougara Boumerdès, située au premier étage du bloc 5, est bien plus qu'un simple dépôt de livres. Centre névralgique pour les étudiants et enseignants, elle offre :",
      welcome_message: "Découvrez nos espaces de travail modernes et nos services adaptés à vos besoins académiques.",
      feature_1: "Une collection riche en ouvrages spécialisés et mémoires",
        feature_2: "Un nouveau système de réservation en ligne pour gagner du temps",
        feature_3: "Des espaces de travail modernes et connectés",
        innovation_title: "Notre innovation",
        innovation_description: "Nous avons digitalisé nos services pour vous offrir une expérience simplifiée :",
        reservation_24h: "Réservation 24h/24",
        real_time_availability: "Disponibilité en temps réel",
        automatic_alerts: "Alertes automatiques",
        recommendations_title: "Nos recommandations du jour",
      opening_hours: "Horaires d'ouverture",
      open_now: "Ouvert maintenant",
      closing_soon: "Fermeture dans",
      sunday: "Dimanche",
      monday: "Lundi",
      tuesday: "Mardi",
      wednesday: "Mercredi",
      thursday: "Jeudi",
      friday: "Vendredi",
      saturday: "Samedi",
      closed: "Fermé",
      calendar: "Calendrier",
      sun: "Dim",
      mon: "Lun",
      tue: "Mar",
      wed: "Mer",
      thu: "Jeu",
      fri: "Ven",
      sat: "Sam",
      search_placeholder: "Rechercher des livres, articles, thèses...",
      all: "Tous",
      books: "Livres",
      articles: "Articles",
      about: "À propos",
      regulations: "Règlement",
      help: "Aide",
      copyright: "© 2025 Bibliothèque Universitaire UMB - Tous droits réservés",
      dashboard: "Tableau de bord",
      profile: "Mon Profil",
      reservations: "Réservations",
      suggestions: "Suggestions",
      messages: "Messages",
      catalogue: "Catalogue",
      welcome_librarian: "Bienvenue dans votre espace bibliothécaire. Accédez aux outils de gestion des prêts, réservations et collections.",
      librarian_dashboard: "Tableau de bord - Bibliothécaire",
      manage_books: "Gérer les ouvrages",
      manage_reservations: "Gérer les réservations",
      manage_accounts: "Gérer les comptes",
      manage_suggestions: "Gérer les suggestions",
      respond_messages: "Répondre aux messages",
      librarian_info: "Informations Bibliothécaire",
      librarian_portal: "Portail Bibliothécaires",
      librarian_email: "Adresse email",
      university_portal: "Portail Utilisateurs",
      university_email: "Adresse email universitaire",
      password: "Mot de passe",
      login: "Se connecter",
      forgot_password: "Mot de passe oublié",
      first_connection: "Première connexion",
      create_account: "Créer un compte",
      reset_password: "Réinitialiser le mot de passe",
      forgot_email: "Entrez votre adresse email",
      send: "Envoyer",
      email_sent: "Un e-mail avec un nouveau mot de passe a été envoyé.",
      email_error: "Erreur lors de l'envoi de l'e-mail.",
      no_account_found: "Aucun compte trouvé avec cet e-mail.",
      incorrect_credentials: "Email ou mot de passe incorrect.",
      registration_success: "Votre compte a été créé avec succès. Il doit être activé par un bibliothécaire avant que vous puissiez vous connecter.",
      registration_error: "Erreur lors de l'inscription. Veuillez réessayer.",
      fill_all_fields: "Veuillez remplir tous les champs !",
      invalid_role: "Rôle invalide !",
      passwords_not_matching: "Les mots de passe ne correspondent pas !",
      invalid_email: "Adresse e-mail invalide !",
      name: "Nom",
      first_name: "Prénom",
      matricule: "Matricule",
      user_info: "Informations {role}",
      teacher:"Enseignant",
      student:"Etudiant",
      welcome_message: "Bienvenue dans votre espace bibliothécaire. Accédez aux outils de gestion des prêts, réservations et collections",
      profile: "Mon Profil",
      manage_books: "Gérer les ouvrages",
      manage_reservations: "Gérer les réservations",
      manage_accounts: "Gérer les comptes",
      manage_suggestions: "Gérer les suggestions",
      respond_messages: "Répondre aux messages"

      
  },
  ar: {
      university_name: "جامعة محمد بوقرة - بومرداس",
      library_name: "كلية العلوم - قسم الإعلام الآلي",
      home: "الرئيسية",
      contact: "اتصل بنا",
      connect: "تسجيل الدخول",
      connect_user: "تسجيل الدخول كمستخدم",
      connect_librarian: "تسجيل الدخول كأمين مكتبة",
      logout: "تسجيل الخروج",
      register: "التسجيل",
      welcome_title: "مرحبا بكم في المكتبة الجامعية",
      welcome_message: "اكتشف مساحات العمل الحديثة وخدماتنا المصممة لاحتياجاتك الأكاديمية.",
      text:"مكتبة قسم الإعلام الآلي بجامعة محمد بوقرة بومرداس، الواقعة في الطابق الأول من القسم رقم 5، ليست مجرد مستودع للكتب بل هي مركز حيوي للطلبة والأساتذة، حيث توفر خدمات وموارد قيّمة.",
      feature_1: "مجموعة غنية من الكتب المتخصصة والرسائل",
      feature_2: "نظام حجز جديد عبر الإنترنت لتوفير الوقت",
      feature_3: "مساحات عمل حديثة ومتصلة",
      innovation_title: "ابتكارنا",
      innovation_description: "قمنا برقمنة خدماتنا لتقديم تجربة مبسطة لكم:",
      reservation_24h: "الحجز على مدار 24 ساعة",
      real_time_availability: "التوافر في الوقت الفعلي",
      automatic_alerts: "تنبيهات تلقائية",
      recommendations_title: "توصياتنا لهذا اليوم",
      opening_hours: "ساعات العمل",
      open_now: "مفتوح الآن",
      closing_soon: "يغلق بعد",
      sunday: "الأحد",
      monday: "الإثنين",
      tuesday: "الثلاثاء",
      wednesday: "الأربعاء",
      thursday: "الخميس",
      friday: "الجمعة",
      saturday: "السبت",
      closed: "مغلق",
      calendar: "التقويم",
      sun: "أحد",
      mon: "إثنين",
      tue: "ثلاثاء",
      wed: "أربعاء",
      thu: "خميس",
      fri: "جمعة",
      sat: "سبت",
      search_placeholder: "ابحث عن كتب، مقالات، أطروحات...",
      all: "الكل",
      books: "كتب",
      articles: "مقالات",
      about: "حول",
      regulations: "اللوائح",
      help: "مساعدة",
      copyright: "© 2025 المكتبة الجامعية UMB - جميع الحقوق محفوظة",
      dashboard: "لوحة القيادة",
      profile: "ملفي الشخصي",
      reservations: "الحجوزات",
      suggestions: "الاقتراحات",
      messages: "الرسائل",
      catalogue: "الفهرس",
      welcome_librarian: "مرحبًا بك في مساحة أمين المكتبة الخاصة بك. يمكنك الوصول إلى أدوات إدارة القروض والحجوزات والمجموعات.",
      librarian_dashboard: "لوحة القيادة - أمين المكتبة",
      manage_books: "إدارة الكتب",
      manage_reservations: "إدارة الحجوزات",
      manage_accounts: "إدارة الحسابات",
      manage_suggestions: "إدارة الاقتراحات",
      respond_messages: "الرد على الرسائل",
      librarian_info: "معلومات أمين المكتبة",
      librarian_portal: "بوابة أمناء المكتبة",
      librarian_email: "البريد الإلكتروني",
      university_portal: "بوابة المستخدمين",
      university_email: "البريد الإلكتروني الجامعي",
      password: "كلمة المرور",
      login: "تسجيل الدخول",
      forgot_password: "نسيت كلمة المرور",
      create_account: "إنشاء حساب",
      first_connection: "الاتصال الأول",
      reset_password: "إعادة تعيين كلمة المرور",
      forgot_email: "أدخل بريدك الإلكتروني",
      send: "إرسال",
      email_sent: "تم إرسال بريد إلكتروني بكلمة مرور جديدة.",
      email_error: "حدث خطأ أثناء إرسال البريد الإلكتروني.",
      no_account_found: "لم يتم العثور على حساب بهذا البريد الإلكتروني.",
      incorrect_credentials: "البريد الإلكتروني أو كلمة المرور غير صحيحة.",
      registration_success: "تم إنشاء حسابك بنجاح. يجب تفعيله من قبل أمين المكتبة قبل أن تتمكن من تسجيل الدخول.",
      registration_error: "حدث خطأ أثناء التسجيل. يرجى المحاولة مرة أخرى.",
      fill_all_fields: "يرجى ملء جميع الحقول!",
      invalid_role: "دور غير صالح!",
      passwords_not_matching: "كلمات المرور غير متطابقة!",
      invalid_email: "البريد الإلكتروني غير صالح!",
      name: "الاسم",
      first_name: "الاسم الأول",
      matricule: "الرقم الجامعي",
      user_info: "معلومات {role}",
      teacher: "أستاذ",
      student: "طالب",
      welcome_message: "مرحبًا بك في مساحة أمين المكتبة الخاصة بك. يمكنك الوصول إلى أدوات إدارة القروض والحجوزات والمجموعات",
      profile: "ملفي الشخصي",
      manage_books: "إدارة الكتب",
      manage_reservations: "إدارة الحجوزات",
      manage_accounts: "إدارة الحسابات",
      manage_suggestions: "إدارة الاقتراحات",
      respond_messages: "الرد على الرسائل"
  },
  en: {
      university_name: "M'hamed Bougara-Boumerdès University",
      library_name: "Faculty of Sciences-Department of Informatics",
      home: "Home",
      contact: "Contact",
      connect: "Login",
      connect_user: "Login as user",
      connect_librarian: "Login as librarian",
      logout: "Logout",
      register: "Register",
      welcome_title: "Welcome to the University Library",
      welcome_message: "Discover our modern workspaces and services tailored to your academic needs.",
      text:"The Library of the Computer Science Department at the University of Mohamed Bougara Boumerdès, located on the first floor of Block 5, is much more than just a book repository; it is a central hub for students and teachers, offering valuable resources and services.",
      feature_1: "A rich collection of specialized books and theses",
      feature_2: "A new online reservation system to save time",
      feature_3: "Modern and connected workspaces",
      innovation_title: "Our Innovation",
      innovation_description: "We have digitized our services to provide you with a simplified experience:",
      reservation_24h: "24/7 Reservation",
      real_time_availability: "Real-time availability",
      automatic_alerts: "Automatic alerts",
      recommendations_title: "Our Recommendations of the Day",
      opening_hours: "Opening Hours",
      open_now: "Open now",
      closing_soon: "Closing in",
      sunday: "Sunday",
      monday: "Monday",
      tuesday: "Tuesday",
      wednesday: "Wednesday",
      thursday: "Thursday",
      friday: "Friday",
      saturday: "Saturday",
      closed: "Closed",
      calendar: "Calendar",
      sun: "Sun",
      mon: "Mon",
      tue: "Tue",
      wed: "Wed",
      thu: "Thu",
      fri: "Fri",
      sat: "Sat",
      search_placeholder: "Search for books, articles, theses...",
      all: "All",
      books: "Books",
      articles: "Articles",
      about: "About",
      regulations: "Regulations",
      help: "Help",
      copyright: "© 2025 UMB University Library - All rights reserved",
      dashboard: "Dashboard",
      profile: "My Profile",
      reservations: "Reservations",
      suggestions: "Suggestions",
      messages: "Messages",
      catalogue: "Catalogue",
      welcome_librarian: "Welcome to your librarian space. Access tools for managing loans, reservations, and collections.",
      librarian_dashboard: "Dashboard - Librarian",
      manage_books: "Manage Books",
      manage_reservations: "Manage Reservations",
      manage_accounts: "Manage Accounts",
      manage_suggestions: "Manage Suggestions",
      respond_messages: "Respond to Messages",
      librarian_info: "Librarian Information",
      librarian_portal: "Librarian Portal",
      librarian_email: "Email address",
      university_portal: "User Portal",
      university_email: "University email address",
      password: "Password",
      login: "Login",
      forgot_password: "Forgot password",
      create_account: "Create an account",
      first_connection: "First connection",
      reset_password: "Reset Password",
      forgot_email: "Enter your email address",
      send: "Send",
      email_sent: "An email with a new password has been sent.",
      email_error: "Error while sending the email.",
      no_account_found: "No account found with this email.",
      incorrect_credentials: "Incorrect email or password.",
      registration_success: "Your account has been successfully created. It must be activated by a librarian before you can log in.",
      registration_error: "Error during registration. Please try again.",
      fill_all_fields: "Please fill in all fields!",
      invalid_role: "Invalid role!",
      passwords_not_matching: "Passwords do not match!",
      invalid_email: "Invalid email address!",
      name: "Name",
      first_name: "First Name",
      matricule: "Matricule",
      user_info: "{role} Information",
      teacher:"Teacher",
      student:"Student",
      welcome_message: "Welcome to your librarian space. Access tools for managing loans, reservations, and collections",
      profile: "My Profile",
      manage_books: "Manage Books",
      manage_reservations: "Manage Reservations",
      manage_accounts: "Manage Accounts",
      manage_suggestions: "Manage Suggestions",
      respond_messages: "Respond to Messages"
  }
};
  // Fonction de changement de langue
  function changeLanguage(lang) {
    if (lang === 'ar') {
      document.body.setAttribute('dir', 'rtl');
    } else {
      document.body.removeAttribute('dir');
    }
    
    document.querySelectorAll('[data-i18n]').forEach(element => {
      const key = element.getAttribute('data-i18n');
      if (translations[lang][key]) {
        element.textContent = translations[lang][key];
      }
    });
    
    document.querySelectorAll('[data-i18n-placeholder]').forEach(element => {
      const key = element.getAttribute('data-i18n-placeholder');
      element.setAttribute('placeholder', translations[lang][key]);
    });
    
    // Mettre à jour le sélecteur de langue
    const flagImg = document.getElementById('current-flag');
    const langText = document.getElementById('current-language');
    
    if (lang === 'fr') {
      flagImg.src = 'https://flagcdn.com/w20/fr.png';
      flagImg.alt = 'FR';
      langText.textContent = 'FR';
    } else if (lang === 'ar') {
      flagImg.src = 'https://flagcdn.com/w20/dz.png';
      flagImg.alt = 'AR';
      langText.textContent = 'AR';
    } else if (lang === 'en') {
      flagImg.src = 'https://flagcdn.com/w20/gb.png';
      flagImg.alt = 'EN';
      langText.textContent = 'EN';
    }
    
    // Mettre à jour l'option active
    document.querySelectorAll('.language-option').forEach(option => {
      option.classList.remove('active');
      if (option.getAttribute('data-lang') === lang) {
        option.classList.add('active');
      }
    });
    
    // Fermer le menu déroulant
    document.querySelector('.language-switcher').classList.remove('active');
    
    localStorage.setItem('preferredLanguage', lang);
  }

  // Gestion du calendrier
  let currentDate = new Date();
  let currentMonth = currentDate.getMonth();
  let currentYear = currentDate.getFullYear();

  function initCalendar() {
    renderCalendar(currentMonth, currentYear);
    
    document.getElementById('prev-month').addEventListener('click', () => {
      currentMonth--;
      if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
      }
      renderCalendar(currentMonth, currentYear);
    });
    
    document.getElementById('next-month').addEventListener('click', () => {
      currentMonth++;
      if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
      }
      renderCalendar(currentMonth, currentYear);
    });
  }

  function renderCalendar(month, year) {
    const calendarGrid = document.getElementById('calendar-grid');
    
    // Effacer les jours existants (garder les en-têtes)
    while (calendarGrid.children.length > 7) {
      calendarGrid.removeChild(calendarGrid.lastChild);
    }
    
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDay = firstDay.getDay();
    
    // Ajouter les jours vides du mois précédent
    for (let i = 0; i < startingDay; i++) {
      const dayElement = document.createElement('div');
      dayElement.className = 'calendar-day other-month';
      calendarGrid.appendChild(dayElement);
    }
    
    // Ajouter les jours du mois
    const today = new Date();
    for (let i = 1; i <= daysInMonth; i++) {
      const dayElement = document.createElement('div');
      dayElement.className = 'calendar-day';
      dayElement.textContent = i;
      
      // Marquer le jour actuel
      if (i === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
        dayElement.classList.add('today');
      }
      
      // Ajouter des marqueurs d'événements (exemple)
      if (Math.random() > 0.7) { // 30% de chance d'avoir un événement
        dayElement.classList.add('event');
      }
      
      calendarGrid.appendChild(dayElement);
    }
  }

  // Gestion des horaires d'ouverture
  function updateLibraryStatus() {
    const now = new Date();
    const day = now.getDay(); // 0 (dimanche) à 6 (samedi)
    const hours = now.getHours();
    const minutes = now.getMinutes();
    
    const statusElement = document.getElementById('library-status');
    const timerElement = document.getElementById('countdown-timer');
    
    // Horaires d'ouverture : dimanche à jeudi, 9h-16h
    const isOpen = (day >= 0 && day <= 4) && // Dimanche (0) à Jeudi (4)
    (hours > 9 || (hours === 9 && minutes >= 0)) && 
      (hours < 16);
    
    if (isOpen) {
      // Calculer le temps restant avant la fermeture (16h)
      const closingTime = new Date(now);
      closingTime.setHours(16, 0, 0, 0);
      
      const diff = closingTime - now;
      const hoursLeft = Math.floor(diff / (1000 * 60 * 60));
      const minutesLeft = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
      
      // Mettre à jour l'affichage
      statusElement.className = 'time-indicator open';
      statusElement.querySelector('i').className = 'fas fa-door-open';
      statusElement.querySelector('h3').setAttribute('data-i18n', 'open_now');
      timerElement.className = 'countdown-timer open';
      timerElement.textContent = `${hoursLeft}h ${minutesLeft}m`;
    } else {
      statusElement.className = 'time-indicator closed';
      statusElement.querySelector('i').className = 'fas fa-door-closed';
      statusElement.querySelector('h3').setAttribute('data-i18n', 'closed_now');
      timerElement.className = 'countdown-timer closed';
      
      // Calculer le temps jusqu'à la prochaine ouverture
      let nextOpen = new Date(now);
      
      if (day === 5 || day === 6 || (day === 4 && hours >= 16)) { // Vendredi, Samedi ou Jeudi après 16h
        // Prochaine ouverture: Dimanche 9h
        nextOpen.setDate(now.getDate() + (7 - day) % 7);
        nextOpen.setHours(9, 0, 0, 0);
      } else if (hours < 9) { // Avant 9h
        nextOpen.setHours(9, 0, 0, 0);
      }
      
      const diff = nextOpen - now;
      const daysLeft = Math.floor(diff / (1000 * 60 * 60 * 24));
      const hoursLeft = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      
      if (daysLeft > 0) {
        timerElement.textContent = `${daysLeft}j ${hoursLeft}h`;
      } else {
        timerElement.textContent = `${hoursLeft}h`;
      }
    }
    
    // Mettre à jour les traductions
    changeLanguage(localStorage.getItem('preferredLanguage') || 'fr');
    
    // Mettre en évidence le jour actuel dans le tableau des horaires
    document.querySelectorAll('.hours-table td').forEach((td, index) => {
      td.classList.remove('today');
      if (index === day) {
        td.classList.add('today');
      }
    });
  }

  // Initialisation au chargement
  document.addEventListener('DOMContentLoaded', () => {
    // Initialiser la langue
    const preferredLanguage = localStorage.getItem('preferredLanguage') || 'fr';
    changeLanguage(preferredLanguage);
    
    // Gestion du sélecteur de langue
    const languageToggle = document.getElementById('language-toggle');
    languageToggle.addEventListener('click', function(e) {
      e.stopPropagation();
      document.querySelector('.language-switcher').classList.toggle('active');
    });
    
    // Gestion du clic sur les options de langue
    document.querySelectorAll('.language-option').forEach(option => {
      option.addEventListener('click', function(e) {
        e.stopPropagation();
        const lang = this.getAttribute('data-lang');
        changeLanguage(lang);
      });
    });
    
    // Fermer le menu déroulant quand on clique ailleurs
    document.addEventListener('click', function() {
      document.querySelector('.language-switcher').classList.remove('active');
    });
    
    // Animation du bouton de langue
    languageToggle.addEventListener('mouseenter', () => {
      languageToggle.classList.add('animate__animated', 'animate__rubberBand');
    });
    languageToggle.addEventListener('mouseleave', () => {
      languageToggle.classList.remove('animate__animated', 'animate__rubberBand');
    });
    
    // Initialiser le calendrier
    initCalendar();
    
    // Mettre à jour le statut d'ouverture
    updateLibraryStatus();
    
    // Actualiser le compte à rebours toutes les minutes
    setInterval(updateLibraryStatus, 60000);
  });
  // Gestion du mode nuit/jour
document.addEventListener('DOMContentLoaded', function () {
const darkModeToggle = document.getElementById('dark-mode-toggle');
const isDarkMode = localStorage.getItem('darkMode') === 'true';

// Appliquer le mode nuit si activé
if (isDarkMode) {
  document.body.classList.add('dark-mode');
  darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
}

// Basculer entre les modes
darkModeToggle.addEventListener('click', function () {
  document.body.classList.toggle('dark-mode');
  const isDark = document.body.classList.contains('dark-mode');
  localStorage.setItem('darkMode', isDark);
  darkModeToggle.innerHTML = isDark
    ? '<i class="fas fa-sun"></i>'
    : '<i class="fas fa-moon"></i>';
});
});
// Animation des inputs
const inputs = document.querySelectorAll('input');
inputs.forEach(input => {
  input.addEventListener('focus', () => {
    input.style.borderColor = 'var(--primary-color)';
    input.classList.add('animate__animated', 'animate__pulse');
  });
  input.addEventListener('blur', () => {
    input.style.borderColor = '#e0e0e0';
    input.classList.remove('animate__animated', 'animate__pulse');
  });
});
// Mettre à jour le texte
document.querySelectorAll('[data-i18n]').forEach(element => {
  const key = element.getAttribute('data-i18n');
  element.textContent = translations[lang][key];
  
});
i18next.init({
  lng: 'fr', // Langue par défaut
  resources: {
      fr: {
          translation: {
              "first_name": "Prénom",
              "last_name": "Nom",
              "email": "Adresse email",
              "password": "Mot de passe",
              "confirm_password": "Confirmer le mot de passe",
              "register": "S'inscrire",
              "already_account": "Déjà un compte? Se connecter"
          }
      }
  }
}, function(err, t) {
  // Appliquer les traductions aux placeholders
  document.querySelectorAll('[data-i18n-placeholder]').forEach(function(el) {
      el.setAttribute('placeholder', t(el.getAttribute('data-i18n-placeholder')));
  });
});
 // Afficher le formulaire de récupération de mot de passe
 document.getElementById('forgot-password-link').addEventListener('click', function (e) {
  e.preventDefault();
  document.querySelector('.login-container').style.display = 'none';
  document.getElementById('forgot-password-container').style.display = 'block';
});
i18next.init({
  lng: 'fr', // Langue par défaut
  resources: {
      fr: {
          translation: {
              "first_name": "Prénom",
              "last_name": "Nom",
              "email": "Adresse email",
              "password": "Mot de passe",
              "confirm_password": "Confirmer le mot de passe",
              "register": "S'inscrire",
              "already_account": "Déjà un compte? Se connecter"
          }
      }
  }
}, function(err, t) {
  // Appliquer les traductions aux placeholders
  document.querySelectorAll('[data-i18n-placeholder]').forEach(function(el) {
      el.setAttribute('placeholder', t(el.getAttribute('data-i18n-placeholder')));
  });
});
document.addEventListener('DOMContentLoaded', () => {
    const welcomeMessage = document.getElementById('welcomeMessage');
    const text = "Bienvenue dans l'espace bibliothécaire !";
    let index = 0;

    function typeWriter() {
        if (index < text.length) {
            welcomeMessage.textContent += text.charAt(index);
            index++;
            setTimeout(typeWriter, 100); // Ajustez la vitesse ici (100ms par caractère)
        } else {
            setTimeout(() => {
                welcomeMessage.textContent = ""; // Efface le texte
                index = 0;
                typeWriter(); // Redémarre l'animation
            }, 2000); // Pause avant de recommencer
        }
    }

    typeWriter();
});
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.querySelector('.search-button');

    // Vérifiez si l'utilisateur est connecté (remplacez par votre logique PHP)
    const isUserLoggedIn = typeof isUserLoggedIn !== 'undefined' ? isUserLoggedIn : false;

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
