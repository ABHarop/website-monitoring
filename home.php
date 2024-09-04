<?php include 'includes/session.php'; ?>
<?php
  include 'includes/slugify.php';

  extract($_POST);
  // Adding websites
  if(isset($add)){
		$website = $_POST['website'];
    $slug = slugify($website);
		$link = $_POST['link'];
    $dateexpire = $_POST['dateexpire'];
    $today = date('Y-m-d');

    try{
      // insert in the websitetb table
      $stmt = $conn->prepare("INSERT INTO websitetb (website, slug, link, dateexpire, createdon) VALUES (:website, :slug, :link, :dateexpire, :createdon)");

      $stmt->execute(['website'=>$website, 'slug'=>$slug, 'link'=>$link, 'dateexpire'=>$dateexpire, 'createdon'=>$today]);
      $_SESSION['success'] = 'Website Added Successfully';

    }
    catch(PDOException $e){
      $_SESSION['error'] = $e->getMessage();
    }

	}

  // Saving changes 
  if(isset($saveChanges)){
    $id = $_POST['id'];
    $website = $_POST['website'];
    $link = $_POST['link'];
    $dateexpire = $_POST['dateexpire'];

    try{
      $stmt = $conn->prepare("UPDATE websitetb SET website=:website, link=:link, dateexpire=:dateexpire WHERE id=:id");
      
      $stmt->execute(['website'=>$website,'link'=>$link, 'dateexpire'=>$dateexpire, 'id'=>$id]);
      $_SESSION['success'] = 'Website Info Successfully Updated';
      
    }
    catch(PDOException $e){
      $_SESSION['error'] = $e->getMessage();
    }	
  }

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
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header with-border">
                <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat" style="background:#1f56a7"><i class="fa fa-plus"></i> New Website</a>
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
                    <th>Action</th>
                  </thead>
                  <tbody>
                    <?php
                      $conn = $pdo->open();
                      try{
                        $stmt = $conn->prepare("SELECT * FROM websitetb ORDER BY website ASC ");
                        $stmt->execute();
                        $siteCount = 1;
                        foreach($stmt as $row){
                          $expiredStatus = ($row['active'] == 1 ) ? '<span class="label label-success fa fa-check-circle"><i></i></span>' : '<span class="label label-danger fa fa-close"><i></i></span>';
                          $onlineStatus = ($row['status'] == 1 ) ? '<span class="label label-success fa fa-check-circle"><i></i></span>' : '<span class="label label-danger fa fa-close"><i></i></span>';
                          $siteExpiryDate = $row['dateexpire'] == '0000-00-00' ? 'Not Set' : date('M d, Y', strtotime($row['dateexpire']));

                          $link = $row['link'];

                          // Check if the link starts with http:// or https://
                          if (strpos($link, 'http://') !== 0 && strpos($link, 'https://') !== 0) {
                              $sitelink = 'http://' . $link; // Add http:// if missing
                          }

                          echo "
                            <tr>
                              <td>{$siteCount}</td>
                              <td>{$row['website']}</td>
                              <td><a target='_blank' href='{$sitelink}'>{$link}</a></td>
                              <td>{$expiredStatus}</td>
                              <td>{$siteExpiryDate}</td>
                              <td>{$onlineStatus}</td>
                              <td>
                                <button class='btn btn-success btn-sm edit btn-flat' data-id='".$row['id']."'><i class='fa fa-edit'></i> Edit</button>
                              </td>
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
<!-- Modal -->
<!-- Add website -->
<div class="modal fade" id="addnew">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" style="color:#1f56a7"><b><center>Add New Website</center></b></h4>
      </div>
      <div class="modal-body" style="margin-top:-20px">
        <form class="form-horizontal" method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <div class="col-sm-12">
              <span>Website</span>
              <input type="text" class="input-fund" id="website" name="website" palceholder="Website Name"  required>
            </div>
            <div class="col-sm-6">
              <span>URL</span>
              <input type="text" class="input-fund" id="link" name="link" palceholder="Website URL" required>
            </div>
            <div class="col-sm-6">
              <span>Expiry Date</span>
              <input type="text" class="input-fund" id="addexdate" name="dateexpire" palceholder="Choose Expiry Date"  required>
            </div>
          </div>          
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-flat pull-left" style="background:orange;color:white" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
        <button type="submit" class="btn btn-primary btn-flat" name="add"><i class="fa fa-save"></i> Save</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Edit -->
<div class="modal fade" id="edit">
  <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" style="color:#1f56a7"><b><center>Update Website</center></b></h4>
        </div>
        <div class="modal-body" style="margin-top:-20px">
          <form class="form-horizontal" method="POST" enctype="multipart/form-data">
            <input id="siteid" name="id" hidden>
            <div class="form-group">
              <div class="col-sm-12">
                <span>Website</span>
                <input type="text" class="input-fund" name="website" id="websiteid" required/>
              </div>
              <div class="col-sm-6">
                <span>URL</span>
                <input type="text" class="input-fund" name="link" id="linkid" required/>
              </div>
              <div class="col-sm-6">
                <span>Expiry Date</span>
                <input type="text" class="input-fund" name="dateexpire" id="expirydate" required/>
              </div>
          </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default btn-flat pull-left" style="background:orange;color:white;" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
          <button type="submit" class="btn btn-success btn-flat" name="saveChanges"><i class="fa fa-check-square-o"></i> Save</button>
          </form>
        </div>
    </div>
  </div>
</div>

<script>
  $(function(){
    $(document).on('click', '.edit', function(e){
      e.preventDefault();
      $('#edit').modal('show');
      var id = $(this).data('id');
      getRow(id);
    });
  });

  function getRow(id){
    $.ajax({
      type: 'POST',
      url: 'website-row.php',
      data: {id:id},
      dataType: 'json',
      success: function(response){
        $('#siteid').val(response.id);
        $('#websiteid').val(response.website);
        $('#linkid').val(response.link);
        $('#expirydate').val(response.dateexpire);
      }
    });
  }

  setInterval(function() {
    $('#refresh-table').load(location.href + ' #refresh-table');
  }, 3000);

  var currentDate = new Date();
  $('#addexdate').datepicker({
    dateFormat: 'yy-mm-dd',
    autoclose: true,
    minDate: currentDate,
    maxDate: '+24m',
  }).on('changeDate', function(ev) {
    $(this).datepicker('hide');
  });

  $('#addexdate').keyup(function() {
    if (this.value.match(/[^0-9]/g)) {
      this.value = this.value.replace(/[^0-9^-]/g, '');
    }
  });

  $('#expirydate').datepicker({
    dateFormat: 'yy-mm-dd',
    autoclose: true,
    minDate: currentDate,
    maxDate: '+24m',
  }).on('changeDate', function(ev) {
    $(this).datepicker('hide');
  });

  $('#expirydate').keyup(function() {
    if (this.value.match(/[^0-9]/g)) {
      this.value = this.value.replace(/[^0-9^-]/g, '');
    }
  });

</script>
</body>
</html>
