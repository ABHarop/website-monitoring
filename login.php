<?php  
  Session_start();

  include('includes/conn.php');
  $conn = $pdo->open();

  extract($_POST);
  if(isset($loginSubmit))
  {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try{
      $stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM accounttb WHERE email = :email GROUP BY id ");
      $stmt->execute(['email'=>$email]);
      $row = $stmt->fetch();
      if($row['numrows'] > 0){
              if(password_verify($password, $row['password'])){
                  $_SESSION['user'] = $row['id'];
                  header('location: home');
              }
              else{
                  $_SESSION['error'] = 'Incorrect Password';
              }

      }
      else{
        $_SESSION['error'] = 'Email not found';
      }
    }
    catch(PDOException $e){
      echo "There is some problem in connection: " . $e->getMessage();
    }

  }

  $pdo->close();

?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition login-page">
<div class="login-box">
  	<?php
      if(isset($_SESSION['error'])){
        echo "
          <div class='callout callout-danger text-center'>
            <p>".$_SESSION['error']."</p> 
          </div>
        ";
        unset($_SESSION['error']);
      }
      if(isset($_SESSION['success'])){
        echo "
          <div class='callout callout-success text-center'>
            <p>".$_SESSION['success']."</p> 
          </div>
        ";
        unset($_SESSION['success']);
      }
    ?>
  	<div class="login-box-body">
    	<p class="login-box-msg">Manage Websites</p>

    	<form method="POST">
        <div class="form-group has-feedback">
          <input type="email" class="form-control" name="email" placeholder="Email" required>
          <span class="glyphicon glyphicon-user form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback">
          <input type="password" class="form-control" name="password" placeholder="Password" required>
          <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        </div>
		<hr>
        <center><button type="submit" class="btn btn-primary btn-block btn-flat" style="background:#1f56a7;border-radius:5px" name="loginSubmit"><i class="fa fa-sign-in"></i> Sign In</button><br><br>
        <a href="./" >View Websites </a></center>
    	</form>
  	</div>
</div>
	
<?php include 'includes/scripts.php' ?>
</body>
</html>