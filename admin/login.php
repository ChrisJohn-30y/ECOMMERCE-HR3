<?php
	session_start();
	include 'includes/conn.php';

	if(isset($_POST['login'])){
		$username = $_POST['username'];
		$password = $_POST['password'];


		$retrieve_query = mysqli_query($conn, "SELECT * FROM admin WHERE username = '$username' 
			AND password = '$password'");
                        $check_rows = mysqli_num_rows($retrieve_query);
                        if($check_rows > 0){

while($row =mysqli_fetch_assoc($retrieve_query)){

								$db_username = $row['username'];
                                $db_password = $row['password'];

                                if($username == $db_username && $password == $db_password){
                                	$_SESSION['admin'] = $row['id'];
                                }else{
                                	$_SESSION['error'] = 'Incorrect password';
                                }
			}	
		/*
		$sql = "SELECT * FROM admin WHERE username = '$username' AND password= '$password'";
		$query = $conn->query($sql);

		if($query->num_rows < 1){
			$_SESSION['error'] = 'Cannot find account with the username';
		}
		else{
			$row = $query->fetch_assoc();
			if(password_verify($password, $row['password'])){
				$_SESSION['admin'] = $row['id'];
			}
			else{
				$_SESSION['error'] = 'Incorrect password';
			}
		} */
		
	}
	else{
		$_SESSION['error'] = 'Input admin credentials first';
	}
}
	header('location: index.php');

?>