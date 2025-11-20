<?php
require_once 'config.php';

session_destroy();
flashMessage('Has cerrado sesión correctamente.', 'success');
redirect('index.php');
?>

