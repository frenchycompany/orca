<?php
/**
 * Admin - Logout
 */
require_once '../includes/config.php';

// Détruire la session
session_destroy();

// Rediriger vers la page de login
redirect('index.php');
