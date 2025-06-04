# Recipe Network

A simple social network for sharing and discovering recipes.  
Built with PHP (MySQLi), MySQL, JavaScript (jQuery), and Bootstrap 5.

---

## Overview

This application is a “Social Network of Recipes,” where users can:

- Register an account and create a profile (including avatar upload)  
- Log in and log out securely  
- Upload recipes (title, rich‐text description, optional image)  
- View all recipes in a feed, “like” recipes, and leave comments  
- Browse recipes as a guest (read‐only functionality)  

All dynamic content is powered by PHP/MySQLi on the server side, with AJAX interactivity via jQuery. The front end is styled responsively using Bootstrap 5.

---

## Features

- **User Registration & Authentication**  
  - Secure password hashing with `password_hash()`  
  - Unique username & email validation  

- **Profiles & Avatars**  
  - Each user can upload an avatar (JPEG/PNG/GIF)  
  - Avatar stored in `/uploads/avatars/` and displayed next to comments  

- **Recipe Upload**  
  - Title (text), Description (rich HTML via TinyMCE), Optional Image  
  - Uploaded images stored in `/uploads/recipes/`  

- **Recipe Feed**  
  - Displays all recipes with thumbnail, author, date, like & comment counts  
  - AJAX “Like/Unlike” buttons update counts in real time  

- **Single Recipe View**  
  - Full recipe details with rendered HTML description  
  - Comment list (with commenter avatars) and AJAX‐powered comment form  

- **Responsive Design**  
  - Uses Bootstrap 5 for layout, forms, and components  
  - Custom CSS overrides in `css/styles.css`  

---

## Technologies Used

- **Backend**  
  - PHP 7+ (MySQLi extension)  
  - MySQL 5.7+ (or MariaDB)  

- **Frontend**  
  - HTML5, CSS3 (Bootstrap 5)  
  - JavaScript (jQuery 3.x)  
  - TinyMCE (rich‐text editor)  

- **Utilities & Libraries**  
  - [HTML Purifier](http://htmlpurifier.org/) (to sanitize rich HTML)  
  - Composer (optional, for installing HTML Purifier)  

---

## Prerequisites

Ensure you have the following installed on your system:

1. **Web Server**  
   - Apache, Nginx, or IIS with PHP support  

2. **PHP**  
   - Version 7.2 or higher  
   - Extensions: `mysqli`, `gd` or `fileinfo` (for MIME detection), `mbstring`, `json`  

3. **MySQL**  
   - Version 5.7+ (or MariaDB equivalent)  

4. **Composer (optional)**  
   - For installing HTML Purifier:  
     ```bash
     composer require ezyang/htmlpurifier
     ```  

---

## Installation & Setup

### 1. Clone & Directory Structure

Clone the repository into your web server’s document root:
