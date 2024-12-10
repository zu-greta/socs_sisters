document.addEventListener("DOMContentLoaded", function () {
    const emailField = document.getElementById("email");
    const firstNameField = document.getElementById("firstname");
    const lastNameField = document.getElementById("lastname");
    const passwordField = document.getElementById("password");
    const confirmPasswordField = document.getElementById("confirmpassword");
    const registerButton = document.getElementById("register-button");

    function validateEmail() {
        const email = emailField.value;
        return /@(mcgill\.ca|mail\.mcgill\.ca)$/.test(email);
    }

    function validatePassword() {
        const password = passwordField.value;
        const lengthValid = password.length >= 10;
        const numberValid = /\d/.test(password);
        const alphaValid = /[a-zA-Z]/.test(password);

        return lengthValid && numberValid && alphaValid;
    }

    function validateFields() {
        const errors = [];

        // Check email
        if (!emailField.value) {
            errors.push("Email field cannot be empty.");
        } else if (!validateEmail()) {
            errors.push("Email must end with @mcgill.ca or @mail.mcgill.ca.");
        }

        // Check first name
        if (!firstNameField.value) {
            errors.push("First name field cannot be empty.");
        }

        // Check last name
        if (!lastNameField.value) {
            errors.push("Last name field cannot be empty.");
        }

        // Check password
        if (!passwordField.value) {
            errors.push("Password field cannot be empty.");
        } else if (!validatePassword()) {
            errors.push(
                "Password must have at least 10 characters, one number, and one alphabetical letter."
            );
        }

        // Check confirm password
        if (!confirmPasswordField.value) {
            errors.push("Confirm password field cannot be empty.");
        } else if (passwordField.value !== confirmPasswordField.value) {
            errors.push("Passwords do not match.");
        }

        return errors;
    }

    // Handle form submission
    document.querySelector("form").addEventListener("submit", function (event) {
        const errors = validateFields();

        if (errors.length > 0) {
            event.preventDefault(); // Prevent form submission
            alert("The following errors were found:\n\n" + errors.join("\n"));
        }
    });
});
