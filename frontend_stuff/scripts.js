document.addEventListener("DOMContentLoaded", () => {
    const weeklyBtn = document.getElementById("weekly-btn");
    const oneTimeBtn = document.getElementById("one-time-btn");
    const recurringForm = document.getElementById("recurring-form");
    const oneTimeForm = document.getElementById("one-time-form");
    const popup = document.getElementById("confirmation-popup");
    const closePopup = document.getElementById("close-popup");
    const saveButtons = document.querySelectorAll(".save-btn");
    const copyLinkButton = document.getElementById("copy-link");
    const returnHomeButton = document.getElementById("return-home");
  
    // Toggle between Weekly and One-Time forms
    weeklyBtn.addEventListener("click", () => {
      weeklyBtn.classList.add("active");
      oneTimeBtn.classList.remove("active");
      recurringForm.classList.add("active");
      oneTimeForm.classList.remove("active");
    });
  
    oneTimeBtn.addEventListener("click", () => {
      oneTimeBtn.classList.add("active");
      weeklyBtn.classList.remove("active");
      oneTimeForm.classList.add("active");
      recurringForm.classList.remove("active");
    });

  saveButtons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault(); // Prevent default submission
      if (!validationForm()) {
        return; // Stop if the form is invalid
      }

      // Retrieve form inputs
      const eventName = document.getElementById('name').value;
      const location = document.getElementById('location').value;
      const startTime = document.getElementById('start_time').value;
      const endTime = document.getElementById('end_time').value;
      const participants = document.getElementById('participants').value;
      const slot = document.getElementById('slot').value;
      const calendar = document.getElementById('calendar').value;
      const notes = document.getElementById('notes').value;

      popup.style.display = "block";
      // const popupInfo = document.getElementById("popup-info");
      // popupInfo.textContent = "[event informations go here]";
      // Populate the popup-info div
      const popupInfo = document.getElementById('popup-info');
      popupInfo.innerHTML = `
        <p><strong>Event Name:</strong> ${eventName}</p>
        <p><strong>Location:</strong> ${location}</p>
        <p><strong>Start Time:</strong> ${startTime}</p>
        <p><strong>End Time:</strong> ${endTime}</p>
        <p><strong>Participants:</strong> ${participants}</p>
        <p><strong>Slot:</strong> ${slot}</p>
        <p><strong>Calendar:</strong> ${calendar}</p>
        <p><strong>Notes:</strong> ${notes}</p>
      `;
      const popup = document.querySelector('.confirmation-popup');
      popup.classList.add('visible');
    });
  });

  // Close Modal
  closePopup.addEventListener("click", () => {
    popup.style.display = "none";
  });

  // Close Modal by Clicking Outside of Modal Content
  window.addEventListener("click", (e) => {
    if (e.target === popup) {
      popup.style.display = "none";
    }
  });

  // Copy Link Functionality
  copyLinkButton.addEventListener("click", () => {
    navigator.clipboard.writeText("https://example.com/scheduled-link").then(() => {
      alert("Link copied to clipboard!");
    });
  });

  function validationForm() {
    // Select the active form (Recurring or One-Time)
    const activeForm = document.querySelector('.schedule-form.active');
  
    if (!activeForm) {
      alert("No active form found. Please try again.");
      return false;
    }
  
    const eventName = activeForm["event-name"].value.trim();
    const location = activeForm["location"].value.trim();
    const startTime = activeForm["start-time"].value.trim();
    const endTime = activeForm["end-time"].value.trim();
    const participants = activeForm["participants"].value.trim();
    const calendar = activeForm["calendar"].value;
  
    let errorMessages = [];
  
    // Validate event name
    if (eventName === "") {
      errorMessages.push("Event name is required.");
    }
  
    // Validate location
    if (location === "") {
      errorMessages.push("Location is required.");
    }
  
    // Validate start and end times
    if (startTime === "") {
      errorMessages.push("Start time is required.");
    }
    if (endTime === "") {
      errorMessages.push("End time is required.");
    }
    if (startTime && endTime) {
      const start = new Date(startTime);
      const end = new Date(endTime);
      if (end <= start) {
        errorMessages.push("End time must be after start time.");
      }
    }
  
    // Validate participants
    if (participants === "" || parseInt(participants) <= 0) {
      errorMessages.push("Number of participants must be a positive number.");
    }
  
    // Validate calendar
    if (!calendar) {
      errorMessages.push("Calendar selection is required.");
    }
  
    // Display errors or allow form submission
    if (errorMessages.length > 0) {
      alert("Form submission failed due to the following errors:\n\n" + errorMessages.join("\n"));
      return false;
    }
  
    return true; // Allow form submission
  }
  

  // Redirect to Home
  returnHomeButton.addEventListener("click", () => {
    popup.style.display = "none";
    // Logic to redirect to home or other functionality
    window.location.href = "/home";
  });
  
    // document.getElementById("copy-link").addEventListener("click", () => {
    //   alert("Link copied to clipboard!");
    // });
  });