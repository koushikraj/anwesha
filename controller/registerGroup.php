<?php
require('model/model.php');
require('dbConnection.php');

$conn = mysqli_connect(SERVER_ADDRESS,USER_NAME,PASSWORD,DATABASE);
require('middleware/authMiddleware.php');
$userID = $_SESSION['userID'];
if(isset($_POST['IDs'])){
	$POST = $_POST;
	$POST['IDs'] = (array)json_decode($POST['IDs']);
} else {
	$json = file_get_contents('php://input');
	$POST = (array)json_decode($json);	
}
// var_dump($POST);
// var_dump($POST);
// $t = is_array($POST['IDs']);
// var_dump($t);
// die();
$userIDs;
$name;
if (isset($POST['IDs']) && !empty($POST['IDs']) && is_array($POST['IDs'])) {
	$userIDs = $POST['IDs'];
} else {
	header('Content-type: application/json');
	echo json_encode(array("status"=>false,"msg"=>"Incomplete request. IDs missing."));
	die();
}
// var_dump($userIDs);
if (isset($POST['name']) && !empty($POST['name'])) {
	/* $userIDs = $_POST['IDs']; */
    $name = $POST['name'];
} else {
	header('Content-type: application/json');
	echo json_encode(array("status"=>false,"msg"=>"Incomplete request. Team name missing."));
	die();
}
$eveID = $match[1];

$size = Events::getEventSize($eveID,$conn);
if($size == -1){
	mysqli_close($conn);
	header('Content-type: application/json');
	echo json_encode(array("status"=>false, "msg"=>"Event does not exist!"));
	die();
} elseif ($size == 1) {
	mysqli_close($conn);
	header('Content-type: application/json');
	echo json_encode(array("status"=>false,"msg"=>"This is not a group Event."));
	die();
}
for ($i=0; $i <count($userIDs) ; $i++) {
	if(Auth::sanitizeID($userIDs[$i])['status']){
		$userIDs[$i] = Auth::sanitizeID($userIDs[$i])['key'];
	} else {
		header('Content-type: application/json');
		echo json_encode(array("status"=>false,"msg"=>"ID is not valid " . $userIDs[$i]));
		die();
	}
}
array_push($userIDs,$userID);

$result = People::registerGroupEvent($userIDs,$size,$eveID,$name, $conn);
mysqli_close($conn);
header('Content-type: application/json');
echo json_encode($result);
?>
