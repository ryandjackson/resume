<?php
session_start();
require_once "pdo.php";
require_once 'util.php';

if ( ! isset($_SESSION['email']) ) {
  die('ACCESS DENIED');
}

if ( isset($_POST['cancel'])) {
  header('Location: index.php');
  return;
}

$stmt = $pdo->prepare('SELECT * FROM Profile
    WHERE profile_id = :prof AND user_id = :uid');
$stmt->execute(array( ':prof' => $_REQUEST['profile_id'],
    'uid' => $_SESSION['user_id']));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $profile === false ) {
    $_SESSION['error'] = "Could not load profile";
    header('Location: index.php');
    return;
}

if ( isset($_POST['first_name']) && isset($_POST['last_name']) && 
      isset($_POST['email']) && isset($_POST['headline']) && 
      isset($_POST['summary']) ) {
        
    
    $msg = validateProfile ();
    if ( is_string($msg) ) {
      $_SESSION['error'] = $msg;
      header('Location: edit.php?profile_id='.$_POST['profile_id']);
      return;
}
    $msg = validatePos ();
    if (is_string($msg) ) {
      $_SESSION['error'] = $msg;
      header('Location: edit.php?profile_id='.$_POST['profile_id']);
      return;
}   
    $msg = validateEdu ();
    if (is_string($msg) ) {
      $_SESSION['error'] = $msg;
      header('Location: edit.php?profile_id='.$_POST['profile_id']);
      return;
  }     
       
    $sql = "UPDATE profile SET user_id = :uid, first_name = :first, last_name = :last, email = :email, headline = :head, summary = :summary
            WHERE profile_id = :pid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
        'pid' => $_REQUEST['profile_id'],
        ':uid' => $_SESSION['user_id'],
        ':first' => $_POST['first_name'],
        ':last' => $_POST['last_name'],
        ':email' => $_POST['email'],
        ':head' => $_POST['headline'],
        ':summary' => $_POST['summary'])
  );

    $sql = "DELETE FROM Position 
              WHERE profile_id = :pid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array( 
        ':pid' => $_REQUEST['profile_id']));

   insertPositions($pdo,$_REQUEST['profile_id']);
    
    $sql = "DELETE FROM Education 
              WHERE profile_id = :pid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array( 
        ':pid' => $_REQUEST['profile_id']));

    
    insertEducations($pdo,$_REQUEST['profile_id']);

      $_SESSION['success'] = "Profile Updated";
      header("Location: index.php");
      return;
}  
       

$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}

$positions=loadPos($pdo, $_REQUEST['profile_id']);
$schools=loadEdu($pdo, $_REQUEST['profile_id']);

$stmt = $pdo->prepare("SELECT count(position_id) FROM Position WHERE profile_id = :xyz ORDER BY rank");
$stmt->execute(array(":xyz" => $_GET['profile_id'] ))
?>


<html>

<head>
<title>Ryan Jackson</title>

<?php require_once 'head.php'; ?>
<link rel="stylesheet" type="text/css" href="mystyle.css">
</head>

<body>
<p>Edit Profile</p>
<?php flashMessages(); ?>
<form method="post" action="edit.php">
<input type="hidden" name="profile_id" value="<?= htmlentities($_GET['profile_id']); ?>"
/> 
<table>
<tr><td>First Name:</td>
<td><input type="text" name="first_name" size="30" value=<?= htmlentities($profile['first_name']) ?>></td></tr>
<tr><td>Last Name:</td>
<td><input type="text" name="last_name" size="30" value=<?= htmlentities($profile['last_name']) ?>></td></tr>
<tr><td>Email:</td>
<td><input type="text" name="email" size="30" value=<?= htmlentities($profile['email']) ?>></td></tr>
<tr><td>Headline:</td>
<td><input type="text" name="headline" size="30" value=<?= htmlentities($profile['headline']); ?>></td></tr>
<tr><td>Summary:</td>
<td><textarea name="summary" rows="8" columns="80"><?= htmlentities($profile['summary']); ?></textarea> </td></tr>
</table>

<?php

$countEdu=0;

echo ('<p>Education: <input type="submit" id="addEdu" value="+">'."\n");
echo ('<div id="edu_fields">'."\n");
if ( count($schools) > 0 ) {
  foreach ( $schools as $school ) {
    $countEdu++;
    echo('<div id="edu'.$countEdu.'">');
    echo 
    '<p>Year: <input type="text" name="edu_year'.$countEdu.'" value="'.$school['year'].'" />
    <input type="button" value="-" onclick="$(\'#edu'.$countEdu.'\').remove();
    return false;"></p>
    <p>School: <input type="text" size="80" name="edu_school'.$countEdu.'" class="school" value="'.htmlentities($school['name']).'" />';
      echo "\n</div>\n";
  }
}
echo ("</div></p>\n");


$countPos=0;

echo ('<p>Position: <input type="submit" id="addPos" value="+">'."\n");
echo ('<div id="position_fields">'."\n");

if ( count($positions) > 0) {
  foreach( $positions as $position ) {
  $countPos++;
  echo('<div class="position" id="position'.$countPos.'">');
  echo
'<p>Year: <input type="text" name="year'.$countPos.'" value="'.htmlentities($position['year']).'" />
<input type="button" value="-" onclick="$(\'#position'.$countPos.'\').remove();return false;"><br>';

    echo '<textarea name="desc'.$countPos.'" rows="8" cols="80">'."\n";
    echo htmlentities($position['description'])."\n";
    echo "\n</textarea>\n</div>\n";
  }
}

echo ("</div></p>\n");
?>

<p><input type="submit" value="Save"/>
<input type="submit" name="cancel" value="Cancel"/>
</p>
</form>

<script>

countEdu = <?= $countEdu ?>;
countPos = <?= $countPos ?>;

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
</html>
