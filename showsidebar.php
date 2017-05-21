<?php
$uid = $_SESSION['uid'];
$names = $_SESSION['fname'];
$logout = '';
$image_url = '';
if (!isset($_SESSION['picture_url'])) {
  $logout = '<a class="button left" href="logout.php"><span class="buttonimage left"></span>Logout</a>';
} else {
  $image_url = $_SESSION['picture_url'];
}
?>
<div class="sidebar">
  <div id="refresh_date"><p><?php echo refreshTimes(); ?></p></div>
  <div id="inform_section">
    <div class="tabHeader" style="margin-top: 5px"><?php echo 'Hi ' . $names ?></div>
    <img class="profileimage" name="" src="<?php echo $image_url; ?>" width="50" height="50" alt="<?php echo $names; ?>">
  </div>

  <p>We are glad that you joined this game. Have a great experience. EnJoY!</p><!--End User Profile Tab Header-->
  <div class="post" style="clear: both; width: 100%; float: left">
    <?php echo $logout; ?>
  </div>
  
  <div class="tabHeader">A Little Bit About Us</div>
  <p>This is currently a beta version of this game. We are improving it weekly. So, you might experience a very different experience at times.</p>

</div>
