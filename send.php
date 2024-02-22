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

  $username = "";
  $password = "";
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