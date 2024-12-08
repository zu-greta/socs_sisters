
function validateForm() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmpassword').value;

    // Ensure password meets criteria
    if (!checkPassword()) {
        alert('Password does not meet the criteria.');
        return false;
    }

    // Ensure passwords match
    if (password !== confirmPassword) {
        alert('Passwords do not match.');
        return false;
    }

    return true; // Allow form submission
}
