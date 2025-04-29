document.addEventListener("DOMContentLoaded", function () {
  const loginForm = document.getElementById("loginForm");
  const errorMessage = document.getElementById("errorMessage");

  loginForm.addEventListener("submit", function (event) {
    event.preventDefault();

    const usernameInput = document.getElementById("username");
    const passwordInput = document.getElementById("password");
    const username = usernameInput.value;
    const password = passwordInput.value;

    errorMessage.textContent = "";

    const formData = new FormData();
    formData.append("username", username);
    formData.append("password", password);

    fetch("verify_login.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`Erreur serveur: ${response.statusText}`);
        }

        return response.json();
      })
      .then((data) => {
        if (data.success) {
          sessionStorage.setItem("currentUser", username);
          window.location.href = "index.html";
        } else {
          errorMessage.textContent =
            data.message || "Nom d'utilisateur ou mot de passe invalide.";
        }
      })
      .catch((error) => {
        console.error("Erreur lors de la connexion:", error);
        errorMessage.textContent =
          "Une erreur s'est produite lors de la connexion. Veuillez réessayer.";
      });
  });
});
