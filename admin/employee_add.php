<?php
	include 'includes/session.php';

									use  PHPMailer\PHPMailer\PHPMailer;
                                    use PHPMailer\PHPMailer\Exception;

                                    require 'phpmailer/src/Exception.php';
                                    require 'phpmailer/src/PHPMailer.php';
                                    require 'phpmailer/src/SMTP.php';

                                    $mail = new PHPMailer(true);


                                    $mail->isSMTP();
                                    $mail->Host = 'smtp.gmail.com';
                                    $mail->SMTPAuth = true;
                                    $mail->Username = 'eedugridpro@gmail.com';
                                    $mail->Password = 'rqrb kott gyof imsr';
                                    $mail->SMTPSecure = 'ssl';
                                    $mail->Port = '465';

                                    $mail->setFrom('eedugridpro@gmail.com');


	if(isset($_POST['add'])){
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$address = $_POST['address'];
		$birthdate = $_POST['birthdate'];
		$contact = $_POST['contact'];
		$gender = $_POST['gender'];
		$position = $_POST['position'];
		$schedule = $_POST['schedule'];
		$filename = $_FILES['photo']['name'];
		$qrcode = $_POST['generated_code'];

		if(!empty($filename)){
			move_uploaded_file($_FILES['photo']['tmp_name'], '../images/'.$filename);	
		}
		//creating employeeid
		$letters = '';
		$numbers = '';
		foreach (range('A', 'Z') as $char) {
		    $letters .= $char;
		}
		for($i = 0; $i < 10; $i++){
			$numbers .= $i;
		}
		$employee_id = substr(str_shuffle($letters), 0, 3).substr(str_shuffle($numbers), 0, 9);
		//
		$sql = "INSERT INTO employees (employee_id, firstname, lastname, address, birthdate, contact_info, email, position_id, schedule_id, photo, generatedcode, created_on) VALUES ('$employee_id', '$firstname', '$lastname', '$address', '$birthdate', '$contact', '$gender', '$position', '$schedule', '$filename', '$qrcode' , NOW())";
		if($conn->query($sql)){
			$_SESSION['success'] = 'Employee added successfully';

									$mail->addAddress($gender); //receiver address

                                    $mail->isHTML(true);

                                    $mail->Subject = 'COFFEE SHOP ATTENDANCE SYSTEM - YOUR QR CODE IS READY!';


                                    $mail->Body = 'Hi <strong>'.$firstname . ' ' . $lastname .'</strong>! with Employee ID <strong>'.$employee_id.'</strong>, You can access your QR code here at <strong>https://api.qrserver.com/v1/create-qr-code/?data='. $qrcode .'</strong>, Click the link and download or take a clear shot of your QR Code for our Attendance System.<br>
                                    Have a nice day!
                                      ';

                                    $mail->send();

                                    echo "<script language='javascript'>alert('QR Code successfully sent to employees email.')</script>";
                          			echo "<script>window.location.href='employee.php';</script>";   



		}
		else{
			$_SESSION['error'] = $conn->error;
		}

	}
	else{
		$_SESSION['error'] = 'Fill up add form first';
	}

	header('location: employee.php');
?>