<?php
require_once 'db.php';
$conn = connect();
$message = "Please Login!";

//if (isset($_POST['register'])) {
if (filter_input(INPUT_POST, 'register', FILTER_NULL_ON_FAILURE)) {
  $icode = $_POST['icode'];
  if (md5($icode) != $_SESSION['image_random_value']) {
    echo 'Sorry, wrong number. Please try again';
  } else {
    $values = array();
    $fields = array();
    $confirm_code = md5(uniqid(rand()));
    $_POST['c_code'] = $confirm_code;
    foreach ($_POST as $key => $value) {
      $value = check_input($value);
      if (($key != 'register') && ($key != 'c_pass') && ($key != 'icode')) {
        $fields[] = sprintf("%s", $key);
        if ($key == 'pass') {
          $values[] = sprintf('"%s"', md5($value));
        } else {
          $values[] = sprintf('"%s"', $value);
        }
      }
    }
    $f = implode(", ", $fields);
    $val = implode(", ", $values);
    $done = insert($conn, TABLE_TEMP_USER, $f, $val);
    $settings = '';
    if ($done) {
      $path_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
      $settings.= 'Account activation Link is send on your Email : ' . $_POST['email'];
      $settings.= '<br /><br />';
      $settings.= $path_url . '/confirmation.php?passkey=' . $confirm_code;
      $settings.= '<br />';
    }
  }
}
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
                  <form class="access-forms" action="index.php" method="post">
                    <input class="defaultText" title="Email Address" type="text" id="user" name="user" placeholder="Email Address">
                    <div class="err_user"></div>
                    <input type="password" id="password" name="password" placeholder="Password">
                    <div class="err_password"></div>
                    <input type="submit" id="login" name="login" value="login"/>
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
