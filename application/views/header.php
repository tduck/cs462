<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>tduck - Lab 3 - CS 462</title>

    
    <link href="http://getbootstrap.com/2.3.2/assets/css/bootstrap.css" rel="stylesheet">
    <link href="http://getbootstrap.com/2.3.2/assets/css/bootstrap-responsive.css" rel="stylesheet">

    <style>

      body {
        padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
      }
    
    </style>

</head>
<body>
  <?php session_start(); ?>
  <div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
      <div class="container">
        <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <div class="nav-collapse collapse">
          <ul class="nav">
            <li><a href="<?php echo site_url(); ?>">Home</a></li>
            <li><a href="<?php echo site_url('lab3'); ?>">Lab 3</a></li>
            <li><a href="<?php echo site_url('users/create'); ?>">Register</a></li>
            <li>
              <?php if (isset($_SESSION['username'])): ?>
                <a href="<?php echo site_url('users/logout'); ?>">Logout</a>
              <?php else: ?>
                <a href="<?php echo site_url('users/login'); ?>">Login</a>
              <?php endif; ?>
            </li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>
  </div>