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
    const link = document.getElementById("link-display");
  
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
  
    // Open Modal on Save
  saveButtons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      popup.style.display = "block";
      // Add dynamic info to the modal if needed
      const popupInfo = document.getElementById("popup-info");
      popupInfo.textContent = "[event informations go here]";
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
  
  // Function to show the modal
function showModal() {
    modalOverlay.classList.add("active");
  }
  
  // Function to hide the modal
  function hideModal() {
    modalOverlay.classList.remove("active");
  }

  // Redirect to Home
  returnHomeButton.addEventListener("click", () => {
    popup.style.display = "none";
    // Logic to redirect to home or other functionality
    window.location.href = "/home";
  });
  
    document.getElementById("copy-link").addEventListener("click", () => {
      alert("Link copied to clipboard!");
    });
  });