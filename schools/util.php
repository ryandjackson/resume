<?php
//util.php
function flashMessages() {
	if ( isset ($_SESSION["success"])) {
    echo ('<p style="color: green;">'.htmlentities($_SESSION["success"])."</p>\n");
    unset ($_SESSION["success"]);
	}
	if ( isset ($_SESSION["error"])) {
    echo ('<p style="color: red;">'.htmlentities($_SESSION["error"])."</p>\n");
    unset ($_SESSION["error"]);
	}
}

function validateProfile() {
	if (strlen($_POST['first_name']) == 0 || strlen($_POST['last_name']) == 0 || strlen($_POST['email']) == 0 || strlen($_POST['headline']) == 0 || strlen($_POST['summary']) == 0) {
        return "All fields are required";
    }
    if  (strpos($_POST['email'],'@') === false) {
    	 return "email must have an @ sign";
    }  
    return true;    
}

function validatePos() {
		for($i=1; $i<=9; $i++) {
			if ( ! isset($_POST['year'.$i]) ) continue;
			if ( ! isset($_POST['desc'.$i]) ) continue;
			$year = $_POST['year'.$i];
			$desc = $_POST['desc'.$i];
			if (strlen($year) == 0 || strlen($desc) == 0 ) {
				return "All fields are required";
			}

			if (! is_numeric($year) ) {
				return "Position year must be numeric";
			}
		}
		return true;
}

function validateEdu() {
		for($i=1; $i<=9; $i++) {
			if ( ! isset($_POST['edu_year'.$i]) ) continue;
			if ( ! isset($_POST['edu_school'.$i]) ) continue;
			$edu_year = $_POST['edu_year'.$i];
			$edu_school = $_POST['edu_school'.$i];
			if (strlen($edu_year) == 0 || strlen($edu_school) == 0 ) {
				return "All fields are required";
			}

			if (! is_numeric($edu_year) ) {
				return "Education year must be numeric";
			}
		}
		return true;
}

function insertPositions($pdo,$profile_id) {
	$rank = 1;
    for($i=1; $i<=9; $i++) {
    if ( ! isset($_POST['year'.$i]) ) continue;
    if ( ! isset($_POST['desc'.$i]) ) continue;
    $year = $_POST['year'.$i];
    $desc = $_POST['desc'.$i];

    $sql = "INSERT INTO Position (profile_id,rank,year,description) 
                            VALUES (:pid, :rank, :year, :des)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
      ':pid' => $_REQUEST['profile_id'],
      ':rank' => $rank,
      ':year' => $year,
      ':des' => $desc)
      );
      $rank++;
      }
}

function insertEducations($pdo,$profile_id) {
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
}

function loadPos($pdo, $profile_id) {
	$sql = "SELECT * FROM Position
		WHERE profile_id = :prof ORDER BY rank";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(array(
			':prof' => $profile_id));
	$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $positions;
}

function loadEdu($pdo, $profile_id) {
	$sql = "SELECT year, name FROM Education
			INNER JOIN institution ON Education.institution_id = institution.institution_id
			WHERE profile_id = :prof ORDER BY rank";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(array(
			':prof' => $profile_id));
	$educations = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $educations;
}