<header class="main-header">
  <nav class="navbar navbar-fixed-top" style="background-color:#1f56a7;font-size:12px; ">
    <div class="container">
      <div class="navbar-header">
        <a href="home" class="navbar-brand">
          <img src="./img/pahappa.jpeg" class="user-image" style="width:60px;margin-top:-12px;border-radius:5px"  alt="Pahappa Logo">
        </a>


        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
          <i class="fa fa-bars"></i>
        </button>
      </div>

      <!-- Collect the nav links, forms, and other content for toggling -->
      <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
        <ul class="nav navbar-nav">
          <li><a onclick="location.href='home'">PAHAPPA WEBSITE STATUS</a></li>
        </ul>
      </div>
      <!-- /.navbar-collapse -->
      <!-- Navbar Right Menu -->
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <?php
              if(isset($_SESSION['user'])){
                echo '
                  <li><a href="./home">HOME</a></li>
                  <li><a href="./recipient">RECIPIENTS</a></li>
                  <li><a href="./logout">LOGOUT</a></li>
                ';
              }
              else{
                echo "
                  <li><a href='login'>LOGIN</a></li>
                ";
              }
          ?>
        </ul>
      </div>
    </div>
  </nav>
</header><br /><br />
