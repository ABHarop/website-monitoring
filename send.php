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
  $sender = "Website Monitoring";

  function pingWebsite($url) {
    
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
    $websiteStatus = $row["status"];
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

      // Check the website's current status
      $pingResult = pingWebsite($websiteURL);

      if ($websiteStatus == 0 && $pingResult == false) {

          // Send message when the website is offline (status = 0) and ping result is false
          $stmtr = $conn->prepare("SELECT * FROM recipienttb");
          $stmtr->execute();

          foreach ($stmtr as $rowr) {
            $sendMessage = "ALERT! $link is offline or unreachable.";
            $recipientPhone = $rowr["phone"];
            
            // Send text message to recipients
            SendSMS($username, $password, $sender, $recipientPhone, $sendMessage);

            // Insert in the activity_logtb table
            $stmt = $conn->prepare("INSERT INTO activity_logtb (activity) VALUES (:activity)");
            $stmt->execute(['activity' => $sendMessage]);
          }

      } elseif ($websiteStatus == 0 && $pingResult == true) {

          // Change website status & active to online if the site is now reachable
          $stmt1 = $conn->prepare("UPDATE websitetb SET status = :status, active = :active WHERE id = :id");
          $stmt1->execute(['status' => $online, 'active' => $online, 'id' => $websiteId]);

          // Log the status change
          $sendMessage = "$link is reachable.";
          $stmt = $conn->prepare("INSERT INTO activity_logtb (activity) VALUES (:activity)");
          $stmt->execute(['activity' => $sendMessage]);

      } elseif ($websiteStatus == 1 && $pingResult == false) {

          // Change website & active status to offline if the site is unreachable, but don't send a message
          $stmt1 = $conn->prepare("UPDATE websitetb SET status = :status, active = :active WHERE id = :id");
          $stmt1->execute(['status' => $offline, 'active' => $offline, 'id' => $websiteId]);

          // Log the status change
          $sendMessage = "ALERT! $link is unreachable.";
          $stmt = $conn->prepare("INSERT INTO activity_logtb (activity) VALUES (:activity)");
          $stmt->execute(['activity' => $sendMessage]);

      } elseif ($websiteStatus == 1 && $pingResult == true) {

          // No action needed if website is online and status is 1
          exit();
      }

    } else {

        // Check the website's status
        if ($websiteStatus == 1) {
  
          // Change domain status to offline and active status to offline if the site has expired
          $stmt3 = $conn->prepare("UPDATE websitetb SET status = :status, active = :active WHERE id = :id");
          $stmt3->execute(['status' => $offline, 'active' => $offline, 'id' => $websiteId]);

          // Notify recipients that the domain has expired
          $stmtr = $conn->prepare("SELECT * FROM recipienttb");
          $stmtr->execute();

          foreach ($stmtr as $rowr) {

            $sendMessage = "ALERT! $link domain has expired.";
            $recipientPhone = $rowr["phone"];

            // Send text message to recipients
            SendSMS($username, $password, $sender, $recipientPhone, $sendMessage);

            // Log the domain expiry alert
            $stmt = $conn->prepare("INSERT INTO activity_logtb (activity) VALUES (:activity)");
            $stmt->execute(['activity' => $sendMessage]);
          }
  
        } else {
  
            // No action needed if alert was already sent
            exit();
        }

    }

 
  }

  // Call the function if executed from the command line
  // if (php_sapi_name() === 'cli') {
  //   pingWebsite($websiteURL);
  // }

  pingWebsite($websiteURL);
?>