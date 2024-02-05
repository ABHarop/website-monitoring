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
                      <th>Domain Status</th>
                      <th>Online</th>
                    </thead>
                    <tbody>
                      <?php
                        $conn = $pdo->open();
                        try{
                          $stmt = $conn->prepare("SELECT *, websitetb.id, statustb.status AS wstatus FROM websitetb JOIN statustb ON websitetb.status = statustb.id ORDER BY website ASC");
                          $stmt->execute();
                          $siteCount = 1;
                          foreach($stmt as $row){
                            $status = ($row['status'] == 1 ) ? '<span class="label label-success">Running</span>' : '<span class="label label-danger">Expired</span>';
                            $onlinestatus = ($row['online'] == 1 ) ? '<span class="label label-success fa fa-check-circle"><i></i></span>' : '<span class="label label-danger fa fa-close"><i></i></span>';
                            echo "
                              <tr>
                                <td>".$siteCount."</td>
                                <td>".$row['website']."</td>
                                <td>".$row['link']."</td>
                                <td>".$row['wstatus']."</td>
                                <td>".$onlinestatus."</td>
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
