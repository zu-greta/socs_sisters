const hamburger = document.getElementById("hamburger");
const hamburgerMenu = document.getElementById("hamburger-menu");

hamburger.addEventListener("click", () => {
  hamburgerMenu.classList.toggle("open");
});

document.addEventListener("click", (event) => {
  if (!hamburger.contains(event.target) && !hamburgerMenu.contains(event.target)) {
    hamburgerMenu.classList.remove("open");
  }
});

function showpopalert(message) {
  document.getElementById('pop-alert-message').innerText = message;
  document.getElementById('pop-alert').style.display = 'block';
}

function closepopalert() {
  document.getElementById('pop-alert').style.display = 'none';
}