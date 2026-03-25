<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

unset($_SESSION['user']);
flash('success', 'Vous êtes désormais déconnecté.');
redirect('index.php');
