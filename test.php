<?php

  include('includes/conn.php');

  $conn = $pdo->open();

  // EgoSMS function to send messages is defined here
  function SendSMS($username, $password, $sender, $number, $message) {

    $url = "www.egosms.co/api/v1/plain/?";

    $parameters = "number=[number]&message=[message]&username=[username]&password=[password]&sender=[sender]";
    $parameters = str_replace("[message]", urlencode($message) , $parameters);
    $parameters = str_replace("[sender]", urlencode($sender) , $parameters);
    $parameters = str_replace("[number]", urlencode($number) , $parameters);
    $parameters = str_replace("[username]", urlencode($username) , $parameters);
    $parameters = str_replace("[password]", urlencode($password) , $parameters);
    $live_url = "https://" . $url . $parameters;
    $parse_url = file($live_url);
    $response = $parse_url[0];
    return $response;

  }

  $username = "harop";
  $password = "Jeepers02??";
  $sender = "Alert";

  function SendText($url) {
    
    // Initialize cURL session
    $ch = curl_init($url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Set timeout in seconds

    // Execute cURL session
    $response = curl_exec($ch);

    // Check if cURL request was successful and the HTTP status code is 200
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Close cURL session
    curl_close($ch);

    if ($response !== false && $httpCode >= 200 && $httpCode < 400) {
      return true; // Website is online
    } else {
      return false; // Website is not online or unreachable
    }

  }

  // Fetch websites from the database
  $stmt = $conn->prepare("SELECT * FROM websitetb GROUP BY id ");
  $stmt->execute();

  foreach($stmt as $row){
    $websiteId = $row["id"];
    $link = $row["link"];
    $domainOffStatus = 0;
    $domainOnStatus = 1;
    $offline = 0;
    $online = 1;
    $siteExpiryDate = $row["dateexpire"];
    $today = date('Y-m-d');

    // Check if the URL starts with "http://" or "https://"
    if (strpos($link, "http://") === 0 || strpos($link, "https://") === 0) {
      // The URL already has a valid scheme
      $websiteURL = $link;
    } else {
      // No scheme provided, add "https://" by default
      $websiteURL = "https://" . $link;
    }

    // Check the expiry date of the website before sending an alert
    if($siteExpiryDate > $today){
      if (sendText($websiteURL)) {
  
        // Change domain status to 1 and online status to 1 if the site is reachable
        $stmt1 = $conn->prepare("UPDATE websitetb SET status=:status, online=:online WHERE id=:id");
        $stmt1->execute(['status'=>$domainOnStatus, 'online'=>$online, 'id'=>$websiteId]);
  
      } else {
  
        // Update Website status to 0 if the site is unreachable
        $stmt2 = $conn->prepare("UPDATE websitetb SET online=:online WHERE id=:id");
        $stmt2->execute(['online'=>$offline, 'id'=>$websiteId]);
  
        $stmtr = $conn->prepare("SELECT * FROM recipienttb ");
        $stmtr->execute();
        foreach($stmtr as $rowr){  
  
          $sendMessage = "ALERT! Website $link is offline or unreachable.";   
          $recipientPhone = $rowr["phone"];
  
          // Send text message to recipients
          $number = $recipientPhone;
          $message = $sendMessage;
  
          SendSMS($username, $password, $sender, $number, $message);
        }

      }

    } else {

      // Change domain status to 0 and online status to 0 if the site expires
      $stmt3 = $conn->prepare("UPDATE websitetb SET status=:status, online=:online WHERE id=:id");
      $stmt3->execute(['status'=>$domainOffStatus, 'online'=>$offline, 'id'=>$websiteId]);

      $stmtr = $conn->prepare("SELECT * FROM recipienttb ");
      $stmtr->execute();
      foreach($stmtr as $rowr){  

        $sendMessage = "ALERT! Website $link has expired.";   
        $recipientPhone = $rowr["phone"];

        // Send text message to recipients
        $number = $recipientPhone;
        $message = $sendMessage;

        SendSMS($username, $password, $sender, $number, $message);
      }
    } 
  }

  // Call the function if executed from the command line
  // if (php_sapi_name() === 'cli') {
  //   SendText($websiteURL);
  // }

  SendText($websiteURL);
?>


<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue layout-top-nav">
	<div class="wrapper">
		<?php include 'includes/navbar.php'; ?>
		<div class="content-wrapper">
			<div class="container">
        <section class="content">
        <?php
              if(isset($_SESSION['error'])){
                echo "
                  <div class='callout callout-danger'>
                    ".$_SESSION['error']."
                  </div>
                ";
                unset($_SESSION['error']);
              }
              if(isset($_SESSION['success'])){
                echo "
                  <div class='callout callout-success'>
                    ".$_SESSION['success']."
                  </div>
                ";
                unset($_SESSION['success']); 
              }
          ?>
          <div class="row"><br>
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header with-border">
                  <center><h3 style="color:#1f56a7"><b>WEBSITE UPTIME STATUS</b></h3></center>
                </div>
                <div class="box-body table-responsive">
                  <table id="refresh-table" class="table table-bordered table-condensed table-striped">
                    <thead>
                      <th style="width:20%">Website</th>
                      <th style="width:30%">URL</th>
                      <th>Domain Status</th>
                      <th>Online</th>
                    </thead>
                    <tbody>
                      <?php
                        $conn = $pdo->open();
                        try{
                          $stmt = $conn->prepare("SELECT *, websitetb.id, statustb.status AS wstatus FROM websitetb JOIN statustb ON websitetb.status = statustb.id ORDER BY website ASC");
                          $stmt->execute();
                          foreach($stmt as $row){
                            $status = ($row['status'] == 1 ) ? '<span class="label label-success">Running</span>' : '<span class="label label-danger">Expired</span>';
                            $onlinestatus = ($row['online'] == 1 ) ? '<span class="label label-success fa fa-check-circle"><i></i></span>' : '<span class="label label-danger fa fa-close"><i></i></span>';
                            echo "
                              <tr>
                                <td>".$row['website']."</td>
                                <td>".$row['link']."</td>
                                <td>".$row['wstatus']."</td>
                                <td>".$onlinestatus."</td>
                              </tr>
                            ";
                          }
                        }
                        catch(PDOException $e){
                          echo $e->getMessage();
                        }

                        $pdo->close();
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
  </div>

  <!-- ./wrapper -->
  <script>
    // Refresh the table
    setInterval(function() {
      $('#refresh-table').load(location.href + ' #refresh-table');
    }, 3000);
  </script>
</body>
</html>
