/**
 * Account Management JavaScript
 * 
 * This file handles user account functionality including:
 * - Profile editing (email, username, password)
 * - Favorites management
 * - AJAX requests for data updates
 * 
 * @package GreenGarnishLabs
 * @version 1.0.0
 */

document.addEventListener("DOMContentLoaded", () => {
  const editButtons = document.querySelectorAll(".edit-btn");

  editButtons.forEach(button => {
      button.addEventListener("click", function () {
          // Get field information and current value
          const field = this.getAttribute("data-field");
          const span = document.getElementById(field);
          const oldValue = span.textContent;

          // Replace text with input field
          const input = document.createElement("input");
          input.type = field === "password" ? "password" : "text";
          input.value = field === "password" ? "" : oldValue;
          span.replaceWith(input);

          // Create save and cancel buttons
          const saveButton = document.createElement("button");
          saveButton.textContent = "Speichern";
          saveButton.classList.add("save-btn");

          const cancelButton = document.createElement("button");
          cancelButton.textContent = "Abbrechen";
          cancelButton.classList.add("cancel-btn");

          this.replaceWith(saveButton);
          saveButton.after(cancelButton);

          // Handle save functionality
          saveButton.addEventListener("click", () => {
              // Send update request to server
              fetch("../src/user/update_user.php", {
                  method: "POST",
                  headers: { "Content-Type": "application/json" },
                  body: JSON.stringify({ field, value: input.value })
              })
              .then(response => response.json())
              .then(data => {
                  if (data.success) {
                      input.replaceWith(span);
                      span.textContent = field === "password" ? "********" : input.value;
                  } else {
                      alert("Fehler: " + data.message);
                  }
              });
              saveButton.remove();
              cancelButton.remove();
          });

          // Handle cancel functionality
          cancelButton.addEventListener("click", () => {
              input.replaceWith(span);
              span.textContent = oldValue;
              saveButton.remove();
              cancelButton.remove();
          });
      });
  });

  // Load favorites
  function loadFavorites() {
      fetch("../user/favorites.php")
          .then(response => response.json())
          .then(data => {
              const container = document.getElementById("favorites-content");
              container.innerHTML = "";

              // Display message if no favorites exist
              if (data.length === 0) {
                  container.innerHTML = "<p>Du hast noch keine Favoriten.</p>";
                  return;
              }

              // Create and append favorite items
              data.forEach(fav => {
                  const favItem = document.createElement("div");
                  favItem.classList.add("favorite-item");
                  favItem.innerHTML = `
                      <img src="../public/images/${fav.image}" alt="${fav.title}" class="fav-img">
                      <p>${fav.title}</p>
                      <button class="remove-fav" data-id="${fav.fav_id}">✖</button>
                  `;
                  container.appendChild(favItem);
              });

              // Initialize remove buttons for favorites
              document.querySelectorAll(".remove-fav").forEach(button => {
                  button.addEventListener("click", function () {
                      removeFavorite(this.getAttribute("data-id"), this);
                  });
              });
          })
          .catch(error => console.error("Fehler beim Laden der Favoriten", error));
  }

  // Delete Favorites
  function removeFavorite(favId, buttonElement) {
      fetch("../user/remove_favorite.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ fav_id: favId })
      })
      .then(response => response.json())
      .then(data => {
          if (data.success) {
              buttonElement.parentElement.remove();
              alert(data.message);
          } else {
              alert("Fehler: " + data.error);
          }
      })
      .catch(error => console.error("Fehler beim Entfernen", error));
  }
});
