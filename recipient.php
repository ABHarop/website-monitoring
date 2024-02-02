<?php include 'includes/session.php'; ?>
<?php

  extract($_POST);
  // Adding recipients
  if(isset($add)){
		$recipient = $_POST['recipient'];
		$phone = $_POST['phone'];

    try{
      // insert in the category table
      $stmt = $conn->prepare("INSERT INTO recipienttb (recipient, phone) VALUES (:recipient, :phone)");
      $stmt->execute(['recipient'=>$recipient, 'phone'=>$phone]);
      
      $_SESSION['success'] = 'Recipient Added Successfully';

    }
    catch(PDOException $e){
      $_SESSION['error'] = $e->getMessage();
    }

	}

  // Saving changes 
  if(isset($saveChanges)){
    $id = $_POST['id'];
    $recipient = $_POST['recipient'];
    $phone = $_POST['phone'];

    try{
      $stmt = $conn->prepare("UPDATE recipienttb SET recipient=:recipient, phone=:phone WHERE id=:id");
      $stmt->execute(['recipient'=>$recipient,'phone'=>$phone, 'id'=>$id]);

      $_SESSION['success'] = 'Recipient Info Successfully Updated';
      
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
                  <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat" style="background:#1f56a7"><i class="fa fa-plus"></i> New Recipient</a>
                </div>
                <div class="box-body table-responsive">
                  <table id="refresh-table" class="table table-bordered table-condensed table-striped">
                    <thead>
                      <th>Name</th>
                      <th>Phone Number</th>
                      <th>Action</th>
                    </thead>
                    <tbody>
                      <?php
                        $conn = $pdo->open();
                        try{
                          $stmt = $conn->prepare("SELECT * FROM recipienttb ORDER BY recipient ASC ");
                          $stmt->execute();
                          foreach($stmt as $row){
                            echo "
                              <tr>
                                <td>".$row['recipient']."</td>
                                <td>".$row['phone']."</td>
                                <td>
                                  <button class='btn btn-success btn-sm edit btn-flat' data-id='".$row['id']."'><i class='fa fa-edit'></i> Edit</button>
                                </td>
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
<!-- Modal -->
<!-- Add recipient -->
<div class="modal fade" id="addnew">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" style="color:#1f56a7"><b><center>Add New Phone Number</center></b></h4>
      </div>
      <div class="modal-body" style="margin-top:-20px">
        <form class="form-horizontal" method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <div class="col-sm-6">
              <span>Name</span>
              <input type="text" class="input-fund" id="recipient" name="recipient" palceholder="Recipient Name"  required>
            </div>
            <div class="col-sm-6">
              <span>Phone Number</span>
              <input type="text" class="input-fund" id="phone" name="phone" palceholder="Enter Phone Number" required>
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
          <h4 class="modal-title" style="color:#1f56a7"><b><center>Update Recipient</center></b></h4>
        </div>
        <div class="modal-body" style="margin-top:-20px">
          <form class="form-horizontal" method="POST" enctype="multipart/form-data">
            <input id="recipientid" name="id" hidden>
            <div class="form-group">
              <div class="col-sm-6">
                <span>Name</span>
                <input type="text" class="input-fund" name="recipient" id="recipientname" required/>
              </div>
              <div class="col-sm-6">
                <span>Phone</span>
                <input type="text" class="input-fund" name="phone" id="recipientphone" required/>
              </div>
            </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default btn-flat pull-left" style="background:orange;color:white" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
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
      url: 'recipient-row.php',
      data: {id:id},
      dataType: 'json',
      success: function(response){
        $('#recipientid').val(response.id);
        $('#recipientname').val(response.recipient);
        $('#recipientphone').val(response.phone);
      }
    });
  }

</script>
</body>
</html>
