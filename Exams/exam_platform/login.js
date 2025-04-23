
document.addEventListener("DOMContentLoaded", function() {
    
    var loginForm = document.getElementById("loginForm");
    var errorMessage = document.getElementById("errorMessage");
    
    
    loginForm.addEventListener("submit", function(event) {
        
        event.preventDefault();
        
        
        var username = document.getElementById("username").value;
        var password = document.getElementById("password").value;
        
        
        
        
        
        var validCredentials = [
            { username: "student1", password: "pass123" },
            { username: "student2", password: "pass456" },
            { username: "student3", password: "pass789" }
        ];
        
        
        var isValid = false;
        for (var i = 0; i < validCredentials.length; i++) {
            if (validCredentials[i].username === username && 
                validCredentials[i].password === password) {
                isValid = true;
                break;
            }
        }
        
        if (isValid) {
            
            sessionStorage.setItem("currentUser", username);
            
            
            window.location.href = "index.html";
        } else {
            
            errorMessage.textContent = "Invalid username or password. Please try again.";
        }
    });
});