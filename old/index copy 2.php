<!DOCTYPE html>
<!-- Coding By CodingNepal - www.codingnepalweb.com -->
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sidebar Menu HTML and CSS | CodingNepal</title>
  <!-- Linking Google Font Link For Icons -->
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <aside class="sidebar">
    <div class="sidebar-header">
      <img src="logo.jpg" alt="logo" />
      <h2>CodingLab</h2>
    </div>
    <ul class="sidebar-links">
      <h4>
        <span>Main Menu</span>
        <div class="menu-separator"></div>
      </h4>
      <li>
        <a href="#">
          <span class="material-symbols-outlined"> dashboard </span>Dashboard</a>
      </li>
      <li>
        <a href="#"><span class="material-symbols-outlined"> overview </span>Overview</a>
      </li>
      <li>
        <a href="#"><span class="material-symbols-outlined"> monitoring </span>Analytic</a>
      </li>
      <h4>
        <span>General</span>
        <div class="menu-separator"></div>
      </h4>
      <li>
        <a href="#"><span class="material-symbols-outlined"> folder </span>Projects</a>
      </li>
      <li>
        <a href="#"><span class="material-symbols-outlined"> groups </span>Groups</a>
      </li>
      <li>
        <a href="#"><span class="material-symbols-outlined"> move_up </span>Transfer</a>
      </li>
      <li>
        <a href="#"><span class="material-symbols-outlined"> flag </span>All Reports</a>
      </li>
      <li>
        <a href="#"><span class="material-symbols-outlined">
            notifications_active </span>Notifications</a>
      </li>
      <h4>
        <span>Account</span>
        <div class="menu-separator"></div>
      </h4>
      <li>
        <a href="#"><span class="material-symbols-outlined"> account_circle </span>Profile</a>
      </li>
      <li>
        <a href="#"><span class="material-symbols-outlined"> settings </span>Settings</a>
      </li>
      <li>
        <a href="#"><span class="material-symbols-outlined"> logout </span>Logout</a>
      </li>
    </ul>
    <div class="user-account">
      <div class="user-profile">
        <img src="portrait.jpg" alt="Profile Image" />
        <div class="user-detail">
          <h3>Eva Murphy</h3>
          <span>Web Developer</span>
        </div>
      </div>
    </div>
  </aside>
</body>
</html>