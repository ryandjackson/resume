<?php // line 1 added to enable color highlight
session_start();
unset($_SESSION['email']);
unset($_SESSION['pass']);
header('Location: login.php');
?>

<!DOCTYPE html>

<title>Ryan Jackson</title>

<head><link rel="stylesheet" type="text/css" href="mystyle.css"></head>

<body>