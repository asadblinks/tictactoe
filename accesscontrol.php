<?php
// accesscontrol.php
require_once 'db.php';
$conn = connect();
if (isset($_POST['user'])) {
  $user = $_POST['user'];
} else if (isset($_SESSION['user'])) {
  $user = $_SESSION['user'];
}

if (isset($_POST['password'])) {
  $pass = md5($_POST['password']);
} else if (isset($_SESSION['pass'])) {
  $pass = $_SESSION['pass'];
}

if (!isset($user)) {
  $message = "You are unauthorized to see this page. Please login to access";
  ?>
  <!DOCTYPE html>
  <html>
    <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <title>Tic Tac Toe</title>

      <link rel="stylesheet" type="text/css" href="css/reset-min.css">
      <link rel="stylesheet" type="text/css" href="css/style.css">
      <script src="js/jquery.js" type="text/javascript"></script>
      <!--  We will be creating this file in the next part of the tutorial, I have included
            it in this version to make the page tabs work -->
      <script src="js/myjava.js" type="text/javascript"></script>
      <script type="text/javascript">
        var turn;
        var clock = 0;
        $(document).ready(function () {
          $(".tab_content").hide(); //On page load hide all the contents of all tabs
          $("ul.tabs li:first").addClass("active").show(); //Default to the first tab
          $(".tab_content:first").show(); //Show the default tabs content
        });
      </script>
    </head>
    <body>
      <div class="wrapper">

        <div class="maincontent" style="width: 100%">

          <div class="logo"><p>TicTacToe</p></div>

          <ul class="tabs">
            <li><a href="#tabs1">Login</a></li>
            <li><a href="#tabs2">Register</a></li>
          </ul>

          <div class="tab_container">
            <div id="tabs1" class="tab_content">

              <?php
              if (isset($_POST['register'])) {
                echo $settings;
              } else {
                ?>
                <div class="post">
                  <h3><?php echo $message ?></h3>
                  <div id="log">
                    <form class="access-forms" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                      <input class="defaultText" title="Email Address" type="text" id="user" name="user" placeholder="Email Address">
                      <div class="err_user"></div>
                      <input type="password" id="password" name="password" placeholder="Password">
                      <div class="err_password"></div>
                      <input type="submit" id="login" name="login" value="Login"/>
                    </form>
                  </div>
                </div>
                <?php
              }
              ?>

            </div>

            <div id="tabs2" class="tab_content">

              <div class="post">
                <h3>Register!</h3>
                <div id="log">
                  <form class="access-forms" action="register.php" method="post">
                    <label for="fname">Full Name :</label>
                    <input type="text" id="fname" name="fname"/><br/>
                    <div class="err_fname"></div>
                    <label for="email">Email Address :</label>
                    <input type="text" id="email" name="email"/><br/>
                    <div class="err_email"></div>
                    <label for="pass">Password :</label>
                    <input type="password" id="pass" name="pass"/><br/>
                    <div class="err_pass"></div>
                    <label for="c_pass">Confirm Password :</label>
                    <input type="password" id="c_pass" name="c_pass"/><br/>
                    <div class="err_c_pass"></div>
                    <label for="icode">Type the code shown below :</label>
                    <input type="text" id="icode" name="icode"/><br/>
                    <div class="err_icode"></div>
                    <img alt="enter the text above as shown in this image..." height="30" src="randomImage.php" /><br/>
                    <input type="reset" value="Reset"/>
                    <input type="submit" id="register" name="register" value="Register"/>
                  </form>
                </div>
              </div>


            </div><!--End Tab 2 -->

          </div><!--End Tab Container -->




        </div><!--End Main Content-->

      </div><!--End Wrapper -->


    </body>
  </html>
  <?php
  exit;
}

$_SESSION['user'] = $user;
$_SESSION['pass'] = $pass;

$user = escapeString($conn, $user);
$pass = escapeString($conn, $pass);

$sql = "SELECT * FROM " . TABLE_USER . " WHERE email='{$user}' and pass='{$pass}'";
$result = db_query($conn, $sql);

if (numRows($result) == 0) {
  unset($_SESSION['user']);
  unset($_SESSION['pass']);
  $message = "Access Denied! Your username or Password is incorrect.";
  ?>
  <!DOCTYPE html>
  <html>
    <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <title>Tic Tac Toe</title>

      <link rel="stylesheet" type="text/css" href="css/reset-min.css">
      <link rel="stylesheet" type="text/css" href="css/style.css">
      <script src="js/jquery.js" type="text/javascript"></script>
      <!--  We will be creating this file in the next part of the tutorial, I have included
            it in this version to make the page tabs work -->
      <script src="js/myjava.js" type="text/javascript"></script>
      <script type="text/javascript">
        var turn;
        var clock = 0;
        $(document).ready(function () {
          $(".tab_content").hide(); //On page load hide all the contents of all tabs
          $("ul.tabs li:first").addClass("active").show(); //Default to the first tab
          $(".tab_content:first").show(); //Show the default tabs content
        });
      </script>
    </head>
    <body>
      <div class="wrapper">

        <div class="maincontent" style="width: 100%">

          <div class="logo"><p>TicTacToe</p></div>

          <ul class="tabs">
            <li><a href="#tabs1">Login</a></li>
            <li><a href="#tabs2">Register</a></li>
          </ul>

          <div class="tab_container">
            <div id="tabs1" class="tab_content">

              <?php
              if (isset($_POST['register'])) {
                echo $settings;
              } else {
                ?>
                <div class="post">
                  <h3><?php echo $message ?></h3>
                  <div id="log">
                    <form class="access-forms" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                      <input class="defaultText" title="Email Address" type="text" id="user" name="user" placeholder="Email Address">
                      <div class="err_user"></div>
                      <input type="password" id="password" name="password" placeholder="Password">
                      <div class="err_password"></div>
                      <input type="submit" id="login" name="login" value="Login"/>
                    </form>
                  </div>
                </div>
                <?php
              }
              ?>

            </div>

            <div id="tabs2" class="tab_content">

              <div class="post">
                <h3>Register!</h3>
                <div id="log">
                  <form class="access-forms" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <label for="fname">Full Name :</label>
                    <input type="text" id="fname" name="fname"/><br/>
                    <div class="err_fname"></div>
                    <label for="email">Email Address :</label>
                    <input type="text" id="email" name="email"/><br/>
                    <div class="err_email"></div>
                    <label for="pass">Password :</label>
                    <input type="password" id="pass" name="pass"/><br/>
                    <div class="err_pass"></div>
                    <label for="c_pass">Confirm Password :</label>
                    <input type="password" id="c_pass" name="c_pass"/><br/>
                    <div class="err_c_pass"></div>
                    <label for="icode">Type the code shown below :</label>
                    <input type="text" id="icode" name="icode"/><br/>
                    <div class="err_icode"></div>
                    <img alt="enter the text above as shown in this image..." height="30" src="randomImage.php" /><br/>
                    <input type="reset" value="Reset"/>
                    <input type="submit" id="register" name="register" value="Register"/>
                  </form>
                </div>
              </div>


            </div><!--End Tab 2 -->

          </div><!--End Tab Container -->




        </div><!--End Main Content-->

      </div><!--End Wrapper -->


    </body>
  </html>
  <?php
  exit;
} else {
  $info = db_fetch_assoc($result);

  $uid = $info['uid'];
  $_SESSION['uid'] = $uid;
  $fname = $info['fname'];
  $email = $info['email'];
  $_SESSION['fname'] = $fname;

  db_query($conn, "UPDATE " . TABLE_USER . " SET timestamp = UNIX_TIMESTAMP(now()) WHERE uid = {$uid} ");
}
?>