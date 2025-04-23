
document.addEventListener("DOMContentLoaded", function() {
    
    var currentUser = sessionStorage.getItem("currentUser");
    if (!currentUser) {
        
        window.location.href = "login.html";
        return;
    }
    
    
    var submitButton = document.getElementById("submitExam");
    var scoreDisplay = document.getElementById("score-display");
    var examForm = document.getElementById("examForm");
    var countdown = document.getElementById("countdown");
    
    
    var currentPage = window.location.pathname.split("/").pop();
    
    
    var timeLeft = 0;
    if (currentPage === "exam1.html") {
        timeLeft = 60 * 60; 
    } else if (currentPage === "exam2.html") {
        timeLeft = 45 * 60; 
    } else if (currentPage === "exam3.html") {
        timeLeft = 50 * 60; 
    } else {
        timeLeft = 60 * 60; 
    }
    
    
    function updateTimer() {
        
        var minutes = Math.floor(timeLeft / 60);
        var seconds = timeLeft % 60;
        
        
        var formattedMinutes = minutes < 10 ? "0" + minutes : minutes;
        var formattedSeconds = seconds < 10 ? "0" + seconds : seconds;
        
        
        countdown.textContent = formattedMinutes + ":" + formattedSeconds;
        
        
        if (timeLeft <= 0) {
            
            clearInterval(timerInterval);
            
            
            disableFormInputs();
            
            
            calculateScore();
            
            
            alert("Time's up! Your answers have been submitted automatically.");
        } else {
            
            timeLeft--;
        }
    }
    
    
    var timerInterval = setInterval(updateTimer, 1000);
    updateTimer(); 
    
    
    function disableFormInputs() {
        var inputs = examForm.querySelectorAll("input");
        for (var i = 0; i < inputs.length; i++) {
            inputs[i].disabled = true;
        }
        
        
        submitButton.disabled = true;
    }
    
    
    submitButton.addEventListener("click", function() {
        
        clearInterval(timerInterval);
        
        
        disableFormInputs();
        
        
        calculateScore();
    });
    
    
    function calculateScore() {
        
        var correctAnswers = {};
        
        if (currentPage === "exam1.html") {
            
            correctAnswers = {
                "q1": ["a", "b", "d"], 
                "q2": ["b"], 
                "q3": ["b"], 
                "q4": ["c"], 
                "q5": ["b"], 
                "q6": ["a", "b", "c"], 
                "q7": ["a"], 
                "q8": ["a"], 
                "q9": ["c"], 
                "q10": ["c"] 
            };
        } else if (currentPage === "exam2.html") {
            
            correctAnswers = {
                "q1": ["a", "b", "d"], 
                "q2": ["a", "c"], 
                "q3": ["a"], 
                "q4": ["a", "b", "d"], 
                "q5": ["b"], 
                "q6": ["false"], 
                "q7": ["true"], 
                "q8": ["false"], 
                "q9": ["false"], 
                "q10": ["false"] 
            };
        } else if (currentPage === "exam3.html") {
            
            correctAnswers = {
                "q1": ["c"], 
                "q2": ["a", "b", "d"], 
                "q3": ["b"], 
                "q4": ["b"], 
                "q5": ["b", "c"], 
                "q6": ["b"], 
                "q7": ["a", "b", "c"], 
                "q8": ["b"], 
                "q9": ["true"], 
                "q10": ["false"], 
                "q11": ["false"] 
            };
        }
        
        var score = 0;
        var totalQuestions = Object.keys(correctAnswers).length;
        var pointsPerQuestion = 2;
        
        
        for (var question in correctAnswers) {
            if (correctAnswers.hasOwnProperty(question)) {
                
                var inputType = document.querySelector("input[name='" + question + "']").type;
                
                if (inputType === "radio") {
                    
                    var selectedOption = document.querySelector("input[name='" + question + "']:checked");
                    if (selectedOption && selectedOption.value === correctAnswers[question][0]) {
                        score += pointsPerQuestion;
                    }
                } else {
                    
                    var checkboxes = document.querySelectorAll("input[name='" + question + "']:checked");
                    var selectedValues = [];
                    
                    
                    for (var i = 0; i < checkboxes.length; i++) {
                        selectedValues.push(checkboxes[i].value);
                    }
                    
                    
                    var sortedSelected = selectedValues.sort();
                    var sortedCorrect = correctAnswers[question].sort();
                    
                    
                    var isCorrect = true;
                    
                    
                    if (sortedSelected.length !== sortedCorrect.length) {
                        isCorrect = false;
                    } else {
                        
                        for (var j = 0; j < sortedSelected.length; j++) {
                            if (sortedSelected[j] !== sortedCorrect[j]) {
                                isCorrect = false;
                                break;
                            }
                        }
                    }
                    
                    
                    if (isCorrect) {
                        score += pointsPerQuestion;
                    }
                }
            }
        }
        
        
        var percentage = (score / (totalQuestions * pointsPerQuestion)) * 100;
        scoreDisplay.innerHTML = "Your Score: " + score + " out of " + (totalQuestions * pointsPerQuestion) + 
                                " (" + percentage.toFixed(2) + "%)";
        
        
        
        saveScore(score, percentage);
    }
    
    
    function saveScore(score, percentage) {
        
        var examType = "";
        if (currentPage === "exam1.html") {
            examType = "Web Application Development";
        } else if (currentPage === "exam2.html") {
            examType = "Object-Oriented Programming";
        } else if (currentPage === "exam3.html") {
            examType = "Language Theory";
        }
        
        console.log("Score for user " + currentUser + " on " + examType + " exam saved: " + score + " points (" + percentage + "%)");
        
        
        
       
    }
    
    
    var guidelinesContent = document.querySelector(".scrolling-content");
    if (guidelinesContent) {
        setInterval(function() {
            if (guidelinesContent.scrollTop + guidelinesContent.clientHeight >= guidelinesContent.scrollHeight) {
                
                guidelinesContent.scrollTop = 0;
            } else {
                
                guidelinesContent.scrollTop += 1;
            }
        }, 100);
    }
});