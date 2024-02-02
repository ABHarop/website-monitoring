<!-- Bootstrap 3.3.7 -->
<script src="./resource/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- DataTables -->
<script src="./resource/js/jquery.dataTables.min.js"></script>
<script src="./resource/js/dataTables.bootstrap.min.js"></script>

<!-- Slimscroll -->
<script src="./resource/js/jquery.slimscroll.min.js"></script>
<!-- AdminLTE App -->
<script src="./resource/js/adminlte.min.js"></script>

<!-- Data Table Initialize --> 
<script>
  $(function () {
    $('#example1').DataTable({
      responsive: true
    })
    $('#example2').DataTable({
      'paging'      : true,
      'lengthChange': false,
      'searching'   : false,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false
    })
  })
</script>
<script>
  $(function(){
    //CK Editor
    CKEDITOR.replace('editor1')
  });

</script>


