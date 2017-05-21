<?php
require_once 'autoload.php';
include_once 'db.php';

$full_site_path = "https://secure.multiadz.com/secure_tic/";

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\FacebookCanvasLoginHelper;
use Facebook\GraphUser;

$appId = '149214754105';
$appSecret = 'b4817344fb6e5b5db38e8c2dcaceba9c';
FacebookSession::setDefaultApplication($appId, $appSecret);

$helper = new FacebookCanvasLoginHelper();
try {
  $session = $helper->getSession();
} catch (FacebookRequestException $ex) {
  echo "Exception occured, code: " . $ex->getCode();
  echo " with message: " . $ex->getMessage();
} catch (\Exception $ex) {
  // When validation fails or other local issues
}
?>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Tic Tac Toe</title>

    <link rel="stylesheet" type="text/css" href="css/reset-min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js" type="text/javascript"></script>
    <script type="text/javascript">
      var base_url = "https://secure.multiadz.com/secure_tic";
    </script>
    <script src="<?php echo $full_site_path; ?>js/facebook.js" type="text/javascript"></script>
    <script src="<?php echo $full_site_path; ?>js/myjava.js" type="text/javascript"></script>
    <script type="text/javascript" src="<?php echo $full_site_path; ?>js/script.js"></script>
    <script type="text/javascript" src="<?php echo $full_site_path; ?>js/process.js"></script>

  </head>
  <body>
      <?php
      if (isset($session)) {
        try {
          $request = new FacebookRequest(
                  $session, 'GET', '/me'
          );
          $response = $request->execute();
          $graphObject = $response->getGraphObject();
          $arr = $graphObject->asArray();

          $_SESSION['uid'] = $arr['id'];
          $_SESSION['fname'] = $arr['name'];
          $_SESSION['email'] = $arr['email'];
        } catch (FacebookRequestException $e) {
          echo "Exception occured, code: " . $e->getCode();
          echo " with message: " . $e->getMessage();
        }

        if (isset($_SESSION['uid'])) {
          $uid = $_SESSION['uid'];
          $names = $_SESSION['fname'];

          $init = FALSE;
          $game_tiles = '';
          if (isset($_GET['gameid'])) {
            $set = 0;
            $gameid = $_GET['gameid'];

            $sql = " SELECT status,gridstate,playerid1,playerid2,turn FROM " . TABLE_GAME . " WHERE gameid = {$gameid} ";
            $result = mysql_query($sql) or die('Cannot get database values from Games Table');
            $n = mysql_fetch_array($result);
            $grid = unserialize($n['gridstate']);
            $status = $n['status'];

            if ($status === 'progress' || $status === 'complete') {
              /**
               * When the GAME is in Progress then WIN/DRAW/LOST
               * Check the status of the Game Whether it is won or lost
               */
              list($sucess, $player) = gameResult($n);
              $player_won = '';
              if ($sucess === 777) {
                $set = 0; // Turn is not accessed
                $init = TRUE;
                // When Game is DRAW
                $player_won = checkGameDraw($conn, $player, $n, $gameid);
              } elseif (!$sucess) {
                $init = TRUE;
                // When Game has no result and it is the process
                $set = evaluateTurn($n); // Set the turns of the player
              } else {
                $set = 0; // Turn is not accessed
                $init = TRUE;
                // When Game has been WON
                $player_won = checkGameWin($conn, $player, $n, $gameid);
              }
              $game_tiles.= createGameTiles($n);
            } else {
              // When the GAME is NEW
              $init = TRUE;
              $sucess = FALSE;
              $set = 0;
            }
          }
          ?>
        <script type="text/javascript">
      turn = <?php echo $set; ?>;
        </script>
        <?php echo $game_tiles ?>
        <div class="wrapper">

          <div class="maincontent">

            <div class="logo">
              <!--<img src="images/logo.png" width="379" height="60" alt="webdesigntuts+ logo">-->
              <div class="logo"><p>TicTacToe</p></div>
            </div>

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
                  <div class="game-area">
                      <?php
                      if (isset($_GET['gameid'])) {
                        if ($init) {
                          $info_message = '';
                          if (!$sucess) {
                            if ($init && $set) {
                              $info_message = 'Your Turn!';
                            } else {
                              $info_message = 'Waiting for Opponent!';
                            }
                          } else {
                            $info_message = $player_won;
                          }

                          if ($n['playerid2']) {// when the game is not new
                            $canvas_flag = 1;
                          } else {
                            $canvas_flag = 0;
                          }

                          $p1_img = $p1_mark = '';
                          $p2_img = $p2_mark = '';
                          $p1_img_place = $p1_mark_place = '';
                          $p2_img_place = $p2_mark_place = '';
                          $p1_class = $p2_class = 'tp tile';

                          if ($canvas_flag) {
                            $d1 = mysql_query(" SELECT fname FROM " . TABLE_USER . " WHERE uid = {$n['playerid1']} ") or die('Cannot get Name of Player');
                            $p1_name = mysql_fetch_array($d1);

                            $d2 = mysql_query(" SELECT fname FROM " . TABLE_USER . " WHERE uid = {$n['playerid2']} ") or die('Cannot get Name of Player');
                            $p2_name = mysql_fetch_array($d2);

                            $p1_img = '<img class="profileimage" name="" src="" width="50" height="50" alt="' . $p1_name['fname'] . '">';
                            $p1_mark = 'X';
                            $p2_img = '<img class="profileimage" name="" src="" width="50" height="50" alt="' . $p2_name['fname'] . '">';
                            $p2_mark = '0';

                            if ($uid == $n['playerid1']) {
                              $p1_img_place = $p1_img;
                              $p1_mark_place = $p1_mark;

                              $p2_img_place = $p2_img;
                              $p2_mark_place = $p2_mark;

                              $p1_class .= 'p1';
                              $p2_class .= 'p2';
                            } else {
                              $p1_img_place = $p2_img;
                              $p1_mark_place = $p2_mark;

                              $p2_img_place = $p1_img;
                              $p2_mark_place = $p1_mark;

                              $p1_class .= 'p2';
                              $p2_class .= 'p1';
                            }
                          }
                          ?>
                        <div id="players_tile_section">
                          <div class="p_section">
                            <div class="p_img">
                                <?php echo $p1_img_place; ?>
                            </div>
                            <div class="<?php echo $p1_class; ?>"><?php echo $p1_mark_place; ?></div>
                          </div>
                          <div class="p_section"><p><?php echo $info_message; ?></p></div>
                          <div class="p_section">
                            <div class="p_img">
                                <?php echo $p2_img_place; ?>
                            </div>
                            <div class="<?php echo $p2_class; ?>"><?php echo $p2_mark_place; ?></div>
                          </div>
                        </div>
                        <!--<div id="status_msg"><p><?php echo $info_message; ?></p></div>-->
                        <div id="canvas">
                          <input id="games_id" type="hidden" value="<?php echo $gameid; ?>" />
                          <input id="turn" type="hidden" value="<?php echo $set; ?>" />
                          <?php print gamesGridDesign($grid, $gameid); ?>
                        </div>
                        <div class="clear"></div>
                        <?php
                      } else {
                        echo '<h1>Access Denied! The Game has been occupied.</h1>';
                      }
                    } else {
                      echo '<h1>Access Denied!</h1>';
                    }
                    ?>
                  </div>
                  <div class="clear"></div>
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
        <?php
      } else {
        echo 'Hello ' . $arr['name'] . '!<br />';
      }
    } else {
      echo '<p></p>';
    }
    ?>
  </body>
</html>
