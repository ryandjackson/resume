<?php
require_once "pdo.php";
require_once "util.php";
session_start();

?>

<!DOCTYPE html>
<html>

<title>Ryan Jackson</title>

<head><link rel="stylesheet" type="text/css" href="mystyle.css"></head>

<body>

<h1>Profile Information</h1>
<hr>
<?php

$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}

?>
<table>
<tr><td>First Name:</td><td><?= $row['first_name'] ?></td></tr>
<tr><td>Last Name:</td><td><?= $row['last_name'] ?></td></tr>
<tr><td>E-mail Address:</td><td><?= $row['email'] ?></td></tr>
<tr><td colspan="2"><p>Headline:</p><p><?= $row['headline'] ?></p></td></tr>
<tr><td colspan="2"><p>Summary:</p><p><?= $row['summary'] ?></p></td></tr>
</table>

<p>Educations: 
<ul>
<?php
	$stmt = $pdo->prepare("SELECT * FROM Education INNER JOIN Institution ON Education.institution_id = institution.institution_id WHERE profile_id = :xyz ORDER BY rank");
	$stmt -> execute(array(":xyz" => $_GET['profile_id']));
	$rows = $stmt ->fetchAll(PDO::FETCH_ASSOC);
	foreach( $rows as $row ) {
  	echo ('<li>'.htmlentities($row['year']).' : '.htmlentities($row['name']).'</li>'); 
}

?>
</ul></p>
<hr>

<p>Positions: 
<ul>
<?php
	$stmt = $pdo->prepare("SELECT * FROM Position WHERE profile_id = :xyz ORDER BY rank");
	$stmt -> execute(array(":xyz" => $_GET['profile_id']));
	$rows = $stmt ->fetchAll(PDO::FETCH_ASSOC);
	foreach( $rows as $row ) {
  	echo ('<li>'.htmlentities($row['year']).' : '.htmlentities($row['description']).'</li>'); 
}

?>
</ul></p>
<hr>
<a href="index.php">Done</a>
</body>
