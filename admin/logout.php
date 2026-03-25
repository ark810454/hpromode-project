<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

unset($_SESSION['admin']);
flash('success', 'Session administrateur fermée.');
redirect('admin/login.php');
