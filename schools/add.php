<?php
session_start();
require_once "pdo.php";
require_once "util.php";


if ( ! isset($_SESSION['email']) ) {
  die('ACCESS DENIED');
}

if ( isset($_POST['logout']) ) {
    header('Location: logout.php');
    return;
}

if ( isset($_POST['cancel']) ) {
    header('Location: index.php');
    return;
}

if ( isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) 
     && isset($_POST['headline']) && isset($_POST['summary'])) {

    $msg = validateProfile ();
    if ( is_string($msg) ) {
      $_SESSION['error'] = $msg;
      header("Location: add.php");
      return;
}
    $msg = validatePos ();
    if (is_string($msg) ) {
      $_SESSION['error'] = $msg;
      header('Location: add.php');
      return;
}   
    $msg = validateEdu ();
    if (is_string($msg) ) {
      $_SESSION['error'] = $msg;
      header('Location: add.php');
      return;
   
}

    $stmt = $pdo->prepare('INSERT INTO Profile 
        (user_id,first_name, last_name, email, headline,summary) 
        VALUES ( :uid, :fn, :ln, :em, :he, :su)');
    $stmt->execute(array(
  				':uid' => $_SESSION['user_id'],
  				':fn' => $_POST['first_name'],
  				':ln' => $_POST['last_name'],
  				':em' => $_POST['email'],
  				':he' => $_POST['headline'],
  				':su' => $_POST['summary']),
        );

$profile_id = $pdo->lastInsertId();

      $rank = 1;
      for($i=1; $i<=9; $i++) {
          if ( ! isset($_POST['year'.$i]) ) continue;
          if ( ! isset($_POST['desc'.$i]) ) continue;
          $year = $_POST['year'.$i];
          $desc = $_POST['desc'.$i];

          $stmt = $pdo->prepare('INSERT INTO Position (profile_id,rank,year,description) VALUES (:pid, :rank, :year, :desc)');
          $stmt->execute(array(
              ':pid' => $profile_id,
              ':rank' => $rank,
              ':year' => $year,
              ':desc' => $desc)
          );
          $rank++;
      }
      $rank = 1;
    for($i=1; $i<=9; $i++) {
    if ( ! isset($_POST['edu_year'.$i]) ) continue;
    if ( ! isset($_POST['edu_school'.$i]) ) continue;
    $year = $_POST['edu_year'.$i];
    $school = $_POST['edu_school'.$i];

    $institution_id = false;
    $stmt = $pdo->prepare('SELECT institution_id FROM
      Institution WHERE name = :name');
    $stmt->execute(array(':name' => $school));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ( $row !== false ) $institution_id = $row['institution_id'];

    If ( $institution_id === false ) {
      $stmt = $pdo->prepare('INSERT INTO Institution (name) VALUES (:name)');
      $stmt->execute(array(':name' => $school));
      $institution_id = $pdo->lastInsertId();
    }

    $sql = "INSERT INTO Education (profile_id,institution_id,rank,year) 
                            VALUES (:pid, :iid, :rank, :year)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
      ':pid' => $profile_id,
      ':iid' => $institution_id,
      ':rank' => $rank,
      ':year' => $year)
      );
      $rank++;
      }
      $_SESSION['success'] = "Profile added";
  header("Location: index.php");
  return;
  }
  

?>


<html>
<title>Ryan Jackson</title>
<?php require_once 'head.php'; ?>
<head><link rel="stylesheet" type="text/css" href="mystyle.css"></head>
<body>

<p><u>Add A New Profile</u></p>
<?php flashMessages(); ?>
<form method="post">
<table id='table1'>
    <tr><td id='form_fields'>First Name:</td><td><input type="text" id='formentry' name="first_name"></td></tr>
    <tr><td id='form_fields'>Last Name:</td><td><input type="text" id='formentry' name="last_name"></td></tr>
    <tr><td id='form_fields'>Email:</td><td><input type="text" id='formentry' name="email"></td></tr>
    <tr><td id='form_fields'>Headline:</td><td><input type="text" id='formentry' name="headline"></td></tr>
    <tr><td id='form_fields'>Summary:</td><td><textarea rows="10" cols="70" type="text" id='formentry' name="summary"></textarea></td></tr>
</table>

<p>Education: <input type="submit" id="addEdu" value="+">
  <div id="edu_fields"></div>
</p>
<p>Position: <input type="submit" id="addPos" value="+">
  <div id="position_fields"></div>
</p>

<p><input type="submit" value="Add New"/> <input type="submit" name="cancel" value="Cancel"/></p>

</form>
</table>
<hr>
<form method="post">
    <input type="submit" name="logout" value="Logout">
</form>

<script type="text/javascript">

countPos = 0;
countEdu = 0;

$(document).ready(function() {
  window.console && console.log('Document ready called');

  $('#addEdu').click(function(event){
    event.preventDefault();
    if ( countEdu >= 9) {
      alert("Maximum of nine education entries exceeded");
      return;
    }
    countEdu++;
    window.console && console.log("Adding education "+countEdu);

    var source = $("#edu-template").html();
    $('#edu_fields').append(source.replace(/@COUNT@/g,countEdu));

    $('.school').autocomplete({
      source: "school.php"
    })     
});

$('.school').autocomplete({
      source: "school.php"
    })

  $('#addPos').click(function(event){
    event.preventDefault();
    if ( countPos >= 9) {
      alert("Maximum of nine position entries exceeded");
      return;
    }
    countPos++;
    window.console && console.log("Adding position "+countPos);
    $('#position_fields').append(
      '<div id="position'+countPos+'"> \
      <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
      <input type="button" value ="-" \
        onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
      <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea> \
      </div>');
  });
});
</script>

<script id="edu-template" type="text">
  <div id="edu@COUNT@">
    <p>Year: <input type="text" name="edu_year@COUNT@" value="" />
    <input type="button" value="-" onclick="$('#edu@COUNT@').remove();return false;"><br>
    <p>School: <input type="text" size="80" name="edu_school@COUNT@" class="school" value="" />
    </script>
</body>

