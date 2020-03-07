<?php
require_once 'pdo.php';
require_once 'util.php';

session_start();

if ( isset($_POST['logout']) ) {
    session_destroy();
    header('Location: index.php');  
}

if ( isset($_POST['Add New']) ) {
    header('Location: add.php');
}

if ( isset($_POST['delete']) && isset($_POST['profile_id']) ) {
    $sql = "DELETE FROM profile WHERE profile_id = :zip";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':zip' => $_POST['profile_id']));
}

?>

<!DOCTYPE html>
<html>
<title>Ryan Jackson</title>

<head><link rel="stylesheet" type="text/css" href="mystyle.css"></head>

<body>

<h1>Welcome to the Ryan Jackson's Profiles</h1>

<?php 

flashMessages();

$stmt = $pdo->query("SELECT profile_id,first_name,last_name,email,headline,summary FROM profile ORDER BY last_name ASC");

$nRows = $pdo->query("SELECT count(profile_id) FROM profile")->fetchColumn();


if ( ! isset($_SESSION['email']) ) {
    echo("<body><div class='container'><p><a href='login.php'>Please log in</a></p></div></body>");
    echo ("<table id='table2'><th id='table_header'>First & Last Name</th><th id='table_header'>Headline</th>");
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	echo("<tr><td id='table_data'>");
    echo( '<a href="view.php?profile_id='.$row['profile_id'].'">'.htmlentities($row['first_name']).' '.htmlentities($row['last_name']).'</a>');
    echo("</td><td id='table_data'>");
    echo(htmlentities($row['headline']));
    echo("</td></tr>\n");
}
} else {  
	echo("<body><div class='container'>
        <p>
        <a href='add.php'>Add New Entry</a>
        </p>
        </div>
        </body>");
	echo ("<table id='table2'><th id='table_header'>First & Last Name</th><th id='table_header'>Headline</th><th id='table_header'></th>");
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo("<tr><td id='table_data'>");
    echo( '<a href="view.php?profile_id='.$row['profile_id'].'">'.htmlentities($row['first_name']).' '.htmlentities($row['last_name']).'</a>');
    echo( "</td><td id='table_data'>");
    echo( htmlentities($row['headline']));
    echo( "</td><td id='table_data_id'>");
    echo( '<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ');
    echo( '<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');
    echo( "</td></tr>\n");
}
if ($nRows < 1) {
    echo "<tr><td id='table_data_id' colspan='3'>No Rows Found</td></tr></table>";
}
}
?>
</table>
<hr>
<?php
if (isset($_SESSION['email']) ) {
    echo("<a href='logout.php'>Logout</a>");
}
?>
</body>
