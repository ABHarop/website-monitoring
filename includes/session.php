<?php
	include './includes/conn.php';
	$conn = $pdo->open();
	session_start();

	if(isset($_SESSION['user'])){

		try{
			$stmt = $conn->prepare("SELECT * FROM accounttb WHERE id=:id");
			$stmt->execute(['id'=>$_SESSION['user']]);
			$user = $stmt->fetch();
		}
		catch(PDOException $e){
			echo "There is some problem in connection: " . $e->getMessage();
		}

		$pdo->close();
	}
	else{
		header('location: ./');
		exit();
	}
?>
