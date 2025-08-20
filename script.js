
document.getElementById('registrationForm').addEventListener('submit', function (event) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;

    if (password !== confirmPassword) {
        event.preventDefault();
        alert('Passwords do not match!');
    }
});

// JavaScript to handle form submission and validation __ login-page
document.getElementById('loginForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent form submission
    
    // Retrieve form input values
    let email = document.getElementById('email').value;
    let password = document.getElementById('password').value;

    // Simple validation checks
    if (email === "" || password === "") {
        alert("Please fill in all fields.");
        return; // Stop form submission
    }

    // Additional validation for email format
    let emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    if (!emailPattern.test(email)) {
        alert("Please enter a valid email address.");
        return;
    }

    // If everything is valid, you can submit the form
    alert("Form Submitted Successfully!");
    window.location.href = "home.html";  // Redirect to home page after successful login
});


//

