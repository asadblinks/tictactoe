<?php
require_once 'db.php';
$profileImage = '';
$access = FALSE;
$fb_js = '';
$errors = '';
$style = '';
$conn = connect();
if (ENV_LOCAL) {
  require_once 'accesscontrol.php';
  $full_site_path = SITE_DEV;
  $profileImage = 'profileImage()';
  if (isset($_SESSION['uid'])) {
    $uid = $_SESSION['uid'];
    $access = TRUE;
  }
} else {
  $full_site_path = SITE_ONLINE;
  $fb_js = '<script type="text/javascript" src="' . $full_site_path . 'js/facebook.js"></script>';

  $fb = connectFacebook();
  list($accessToken, $err) = fbCanvasLogin($fb);

  if (isset($accessToken)) {
    // Logged in.
    $access = TRUE;
    try {
      $token = (string) $accessToken->getValue();
      $_SESSION['fb_token'] = $token;
      $response = $fb->get('/me?fields=id,name,picture.width(50).height(50)', $token);
      $user = $response->getGraphUser();

      $uid = $user['id'];
      $_SESSION['uid'] = $uid;
      $_SESSION['fname'] = $user['name'];
      $_SESSION['picture_url'] = $user['picture']['url'];
    } catch (Facebook\Exceptions\FacebookResponseException $e) {
      // When Graph returns an error
      $errors.= 'Graph returned an error: ' . $e->getMessage();
    } catch (Facebook\Exceptions\FacebookSDKException $e) {
      // When validation fails or other local issues
      $errors.= 'Facebook SDK returned an error: ' . $e->getMessage();
    }
  } else {
    $errors = $err;
  }

  // when online and error
  if ($access === FALSE) {
    $style = 'display: none';
  }
}
?>
<html>
  <head>
    <title>TicTacToe Game</title>
    <link rel="stylesheet" type="text/css" href="css/reset-min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script type="text/javascript">
      var base_url = "<?php echo $full_site_path; ?>";
    </script>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
    <script type="text/javascript" src="<?php echo $full_site_path; ?>js/globals.js"></script>
    <?php echo $fb_js; ?>
    <script type="text/javascript" src="<?php echo $full_site_path; ?>js/myjava.js"></script>
    <script type="text/javascript" src="<?php echo $full_site_path; ?>js/script.js"></script>
  </head>
  <body onload="<?php echo $profileImage; ?>">
    <div class="wrapper" style="<?php echo $style; ?>">
      <input type="hidden" id="as123" value="<?php echo $errors; ?>"/>
      <div class="maincontent">
        <div class="logo"><p>TicTacToe</p></div>

        <ul class="tabs">
          <li class="tab1"><a href="#tab1">Games</a></li>
          <li class="tab5"><a href="#tab5">Tiles</a></li>
          <li class="tab2"><a href="#tab2">New Games</a></li>
          <li class="tab3"><a href="#tab3">Statistics</a></li>
          <li class="tab4"><a href="#tab4">Players</a></li>            
        </ul>

        <div class="tab_container">
          <div id="tab1" class="tab_content">
            <div id="tab1_contents">
                <?php
                if ($access === TRUE) {
                  $_SESSION['g_type'] = 'games';
                  ?>
                <div id="data_content">
                  <div id="t_error"></div>
                  <?php
                  $query = "SELECT COUNT(gameid) AS num FROM " . TABLE_GAME . " WHERE status = 'progress' AND ( uid = {$uid} OR playerid2 = {$uid} ) ";
                  $_SESSION['pager_count'] = $query;
                  list($num, $offset, $rowsperpage, $currentpage, $totalpages) = initialize_pager($conn, $query);
                  $inf = ($num) ? $num . " active Game(s)" : "no active Game";
                  ?>
                  <div class="post">
                    <p>
                      Currently, you have&nbsp;<?php echo $inf ?>. You can make your move from Actions or View Game while your opponent has not responded to your turn.
                      Do you want to create a <strong>new Game</strong>? Click <a class="more anchors" href="#tab2">here</a>.
                    </p>
                    <span class="line"></span>
                  </div>
                  <div id="tbl_data" style="float: left">
                      <?php
                      $sql = "SELECT gameid, playerid1, playerid2, created, turn, last_move FROM " . TABLE_GAME . " "
                              . "WHERE status = 'progress' AND ( uid = {$uid} OR playerid2 = {$uid} ) "
                              . "ORDER BY last_move DESC";
                      // save query for pagination
                      $_SESSION['pager_query'] = $sql;

                      $limit = " LIMIT {$offset}, {$rowsperpage}";
                      $game_query = $sql . $limit;
                      ?>
                    <table id="book_data" width="480">
                      <thead>
                        <tr>
                          <th width="280">Game Against</th>
                          <th width="100">Action</th>
                          <th width="100">Last Move</th>
                        </tr>
                      </thead>
                      <tbody>
                          <?php
                          $output = '';
                          if ($num) {
                            $result = db_query($conn, $game_query);
                            $output.= getGamesListing($conn, $result);
                          } else {
                            $output.= '<tr><td width="150" colspan="3">There are no Active Games</td></tr>';
                          }
                          echo $output;
                          ?>
                      </tbody>
                    </table>
                    <?php
                    if ($num > 10) {
                      echo display_pager($currentpage, $totalpages);
                    }
                    ?>                    
                  </div>
                </div>
                <?php
              }
              ?>
            </div>
          </div>
          <?php
          // All the other tabs which are part of this page too.
          include_once 'showtabs.php';
          ?>
        </div><!--End Tab Container -->
      </div><!--End Main Content-->
      <?php
      // The sidebar of site is present in the file included below.
      include_once 'showsidebar.php';
      ?>
    </div><!--End Wrapper -->
    <?php ?>
  </body>
</html>
