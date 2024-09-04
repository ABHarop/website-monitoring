<?php include('includes/conn.php'); ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue layout-top-nav">
	<div class="wrapper">
		<?php include 'includes/navbar.php'; ?>
		<div class="content-wrapper">
			<div class="container">
        <section class="content">
          <div class="row"><br>
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header with-border">
                  <center><h3 style="color:#1f56a7"><b>WEBSITE UPTIME STATUS</b></h3></center>
                </div>
                <div class="box-body table-responsive">
                  <table id="refresh-table" class="table table-bordered table-condensed table-striped">
                    <thead>
                      <th style="width:4%">No.</th>
                      <th style="width:20%">Website</th>
                      <th style="width:34%">URL</th>
                      <th>Active</th>
                      <th>Expiry Date</th>
                      <th>Online</th>
                    </thead>
                    <tbody>
                    <?php
                        $conn = $pdo->open();
                        $today = date('Y-m-d');
                        try{
                          $stmt = $conn->prepare("SELECT * FROM websitetb WHERE dateexpire > '$today' ORDER BY website ASC ");
                          $stmt->execute();
                          $siteCount = 1;
                          foreach($stmt as $row){
                            $expiredStatus = ($row['active'] == 1 ) ? '<span class="label label-success fa fa-check-circle"><i></i></span>' : '<span class="label label-danger fa fa-close"><i></i></span>';
                            $onlineStatus = ($row['status'] == 1 ) ? '<span class="label label-success fa fa-check-circle"><i></i></span>' : '<span class="label label-danger fa fa-close"><i></i></span>';
                            $siteExpiryDate = $row['dateexpire'] == '0000-00-00' ? 'Not Set' : date('M d, Y', strtotime($row['dateexpire']));

                            $link = $row['link'];

                            // Check if the link starts with http:// or https://
                            if (strpos($link, 'http://') !== 0 && strpos($link, 'https://') !== 0) {
                                $sitelink = 'http://' . $link; // Add http:// if it's missing
                            }

                            echo "
                              <tr>
                                <td>{$siteCount}</td>
                                <td>{$row['website']}</td>
                                <td><a target='_blank' href='{$sitelink}'>{$link}</a></td>
                                <td>{$expiredStatus}</td>
                                <td>{$siteExpiryDate}</td>
                                <td>{$onlineStatus}</td>
                              </tr>
                            ";
                            $siteCount++;
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
