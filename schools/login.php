<?php
require_once "pdo.php"; 
require_once 'util.php';
if ( isset($_POST['cancel'] ) ) {
    header("Location: index.php");
    return;
}

$salt = 'XyZzy12*_';

session_start();

    if ( isset($_POST["email"]) && isset ($_POST["pass"]) ) {
        unset($_SESSION["email"]); 
        if ( strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1 ) {
        $_SESSION["error"] = "User name and password are required";
        header('Location: login.php');
        error_log("Login fail ".$_POST["email"]);
        return;
    }   else {
    	if (strpos($_POST['email'],'@') === false) {
    	$_SESSION["error"] = "email must have an @ sign";
        header('Location: login.php');
        error_log("Login fail ".$_POST["email"]);
        return;
    	} else {
            $check = hash('md5', $salt.$_POST['pass']);
            $stmt = $pdo->prepare('SELECT user_id, email FROM users
            WHERE email = :em AND password = :pw');
            $stmt->execute(array( ':em' => $_POST['email'], ':pw' => $check));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ( $row !== false ) {
            $_SESSION['email'] = $row['email'];
            $_SESSION['user_id'] = $row['user_id'];
            error_log("Login success ".$_POST["email"]);
            header("Location: index.php");
            return;
        	} else {
            	$_SESSION["error"] = "Incorrect password";
                header('Location: login.php');
                error_log("Login fail ".$_POST["email"]." pw: ".$_POST["pass"]);
                return;
        }
    }
}
}
?>

<!DOCTYPE html>
<html>

<head>
<?php require_once "head.php"; ?>
<link rel="stylesheet" type="text/css" href="mystyle.css">
<title>Ryan Jackson</title>
</head>

<body>
<div class="container">
<h1>Please Log In</h1>

<?php

flashMessages();

?>

<form method="POST">
<table id='table_login'>
<tr><td><label for="email">E-mail:</label></td>
<td><input type="text" name="email" id="email"></td></tr>
<tr><td><label for="id_1723">Password:</label></td>
<td><input text="current-password" type="password" name="pass" id="id_1723"></td></tr>
</table>
<input type="submit" onclick="return doValidate();" value="Log In">
<input type="submit" name="cancel" value="Cancel">
</form>

<p>
For a password hint, view source and find a password hint
in the HTML comments.
<!-- Hint: The password is the three character coding language
(all lower case) followed by 123. -->
</p>
<script>
function doValidate() {
console.log('Validating...');
try {
pw = document.getElementById('id_1723').value;
console.log("Validating pw="+pw);
if (pw == null || pw == "") {
alert("Both fields must be filled out");
return false;
}
return true;
} catch(e) {
return false;
}
return false;
}
</script>
</div>
</body>
