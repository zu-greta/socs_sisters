
function checkPassword() {
    const passwordField = document.getElementById('password');
    const passwordCheckLength = document.getElementById('password-check-length');
    const passwordCheckNumber = document.getElementById('password-check-number');
    const passwordCheckAlpha = document.getElementById('password-check-alpha');

    const value = passwordField.value;

    let lengthValid = false;
    let numberValid = false;
    let alphaValid = false;

    // Check for length of 10 or more
    if (value.length >= 10) {
        passwordCheckLength.style.visibility = 'visible';
        lengthValid = true;
    } else {
        passwordCheckLength.style.visibility = 'hidden';
    }

    // Check for at least one number
    if (/\d/.test(value)) {
        passwordCheckNumber.style.visibility = 'visible';
        numberValid = true;
    } else {
        passwordCheckNumber.style.visibility = 'hidden';
    }

    // Check for at least one alphabetical character
    if (/[a-zA-Z]/.test(value)) {
        passwordCheckAlpha.style.visibility = 'visible';
        alphaValid = true;
    } else {
        passwordCheckAlpha.style.visibility = 'hidden';
    }

    return lengthValid && numberValid && alphaValid;
}

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
