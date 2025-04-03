/**
 * Main JavaScript File
 * 
 * This file contains the core JavaScript functionality for the website, including:
 * - Profile menu interactions
 * - UI element behaviors
 * - Event listeners
 * 
 * @package GreenGarnishLabs
 * @version 1.0.0
 */

// DOM Elements for profile menu functionality
const profileImg = document.querySelector('.profile-img');
const profileMenu = document.querySelector('.profile-menu');
const profileDiv = document.querySelector('.profile');

// Show profile menu on hover over profile image
profileImg.addEventListener('mouseenter', () => {
    profileMenu.style.display = "flex";
});

// Hide profile menu when mouse leaves the profile area
profileDiv.addEventListener('mouseleave', () => {
    profileMenu.style.display = "none";
});

