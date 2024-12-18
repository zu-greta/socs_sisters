// Coder: Quinn Xiao

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