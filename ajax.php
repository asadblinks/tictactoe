<?php

require_once 'db.php';
$conn = connect();
$uid = $_SESSION['uid'];
$names = $_SESSION['fname'];
if (ENV_LOCAL) {
  
} else {
  $token = $_SESSION['fb_token'];
  $fb = connectFacebook();
}

function getFbProfilePic($fb, $uid) {
  $picture_url = '';
  try {
    $token = $_SESSION['fb_token'];
    $response = $fb->get('/' . $uid . '?fields=id,picture.width(50).height(50)', $token);
    $user = $response->getGraphUser();

    $picture_url.= $user['picture']['url'];
  } catch (Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    $errors.= 'Graph returned an error: ' . $e->getMessage();
  } catch (Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    $errors.= 'Facebook SDK returned an error: ' . $e->getMessage();
  }
  return $picture_url;
}

function setGridValues($n, $cell_no) {
  $grid = unserialize($n['gridstate']);
  $turn = $n['turn'];
  list($row, $col) = explode("-", $cell_no);
  $grid[$row][$col] = $turn;
  return serialize($grid);
}

$string = '';
$temp = array();
if (filter_input(INPUT_POST, 'data')) {
  $data = filter_input(INPUT_POST, 'data');
  $temp = explode("_", $data);
  $cell = $temp[1];
  $game_id = $temp[2];

  $sql = " SELECT gridstate, turn, playerid1, playerid2 FROM " . TABLE_GAME . " WHERE gameid = {$game_id} ";
  $result = db_query($conn, $sql);
  $n = db_fetch_assoc($result);
  $turn = $n['turn'];

  $grad = setGridValues($n, $cell);

  if ($turn === 'p2') {
    $turn = 'p1';
  } else {
    $turn = 'p2';
  }

  $last_move = time();

  $update = " UPDATE " . TABLE_GAME . " SET gridstate = '{$grad}', turn = '{$turn}', last_move = {$last_move} WHERE gameid = {$game_id}; ";
  db_query($conn, $update);
  // Update current timestamp of user
  db_query($conn, "UPDATE " . TABLE_USER . " SET timestamp = UNIX_TIMESTAMP(now()) WHERE uid = {$uid} ");

  $string = $game_id;
}
$grid_area = '';
if (filter_input(INPUT_POST, 'type') && filter_input(INPUT_POST, 'type') === 'getturn') {
  $gameid = filter_input(INPUT_POST, 'gameid', FILTER_VALIDATE_INT, 1);
  $sql = "  SELECT status,gridstate,playerid1,playerid2,turn FROM " . TABLE_GAME . " WHERE gameid = {$gameid}  ";
  $result = db_query($conn, $sql);

  $set = 0;
  if ($result) {
    $n = db_fetch_assoc($result);
    $set = evaluateTurn($n); // Set the turns of the player
  }
  $string = $set;
}
if (filter_input(INPUT_POST, 'type') && filter_input(INPUT_POST, 'type') === 'update') {
  $gameid = filter_input(INPUT_POST, 'gameid', FILTER_VALIDATE_INT, 1);
  $sql = " SELECT g.status,g.gridstate,g.playerid1,g.playerid2,g.turn,CONCAT(u.uid,'-',u.timestamp) as ptimes FROM " . TABLE_GAME . " g
            RIGHT JOIN " . TABLE_USER . " u ON g.playerid1 = u.uid or g.playerid2 = u.uid
            WHERE g.gameid = {$gameid} ";
  $result = db_query($conn, $sql);

  if (numRows($result) !== 0) {
    $t = array();
    while ($info = db_fetch_assoc($result)) {
      $n = $info;
      $t[] = "user" . $info['ptimes'];
    }
    $alive = checkIfOnline($n, $t);

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
        $set = 1; // Turn is not accessed
        $init = TRUE;
        // When Game is DRAW
        $grid_area = '';
        $grid_area.= '<input id="games_id" type="hidden" value="' . $gameid . '" />';
        $grid_area.= '<input id="turn" type="hidden" value="' . $set . '" />';
        $grid_area.= '<input id="alive" type="hidden" value="' . $alive . '" />';
        $player_won = checkGameDraw($conn, $player, $n, $gameid);
      } elseif (!$sucess) {
        $init = TRUE;
        // When Game has no result and it is the process
        $set = evaluateTurn($n); // Set the turns of the player

        $grid_area = '';
        $grid_area.= '<input id="games_id" type="hidden" value="' . $gameid . '" />';
        $grid_area.= '<input id="turn" type="hidden" value="' . $set . '" />';
        $grid_area.= '<input id="alive" type="hidden" value="' . $alive . '" />';

        // The Grid's Design from function
        $grid_area.= gamesGridDesign($grid, $gameid);
        if ($set) {
          $player_won = "Your Turn!";
        } else {
          $player_won = "Waiting for Opponent!";
        }
      } else {
        $set = 1; // Turn is not accessed
        $init = TRUE;
        $grid_area = '';
        $grid_area.= '<input id="games_id" type="hidden" value="' . $gameid . '" />';
        $grid_area.= '<input id="turn" type="hidden" value="' . $set . '" />';
        $grid_area.= '<input id="alive" type="hidden" value="' . $alive . '" />';

        // When Game has been WON
        $player_won = checkGameWin($conn, $player, $n, $gameid);
      }
    } else {
      $set = 0;
      $grid_area = '';
      $grid_area.= '<input id="games_id" type="hidden" value="' . $gameid . '" />';
      $grid_area.= '<input id="turn" type="hidden" value="' . $set . '" />';
      $grid_area.= '<input id="alive" type="hidden" value="' . $alive . '" />';

      // The Grid's Design from function
      $grid_area.= gamesGridDesign($grid, $gameid);
      $player_won = "Waiting for Opponent!";
    }

    if ($n['playerid2']) {// when the game is not new
      $canvas_flag = 1;
    } else {
      $canvas_flag = 0;
    }
    $temp['grid_area'] = $grid_area;
    $temp['turn'] = $set;
    $temp['message'] = $player_won;
    $temp['canvas_flag'] = $canvas_flag;
  } else {
    $temp['grid_area'] = '<p>You have Either won or lost</p>';
    $temp['turn'] = 0;
  }
  $string = json_encode($temp);
}
if (filter_input(INPUT_POST, 'type') && filter_input(INPUT_POST, 'type') === 'alive') {
  $gameid = filter_input(INPUT_POST, 'gameid', FILTER_VALIDATE_INT, 1);
  $sql = "  SELECT g.status,g.gridstate,g.playerid1,g.playerid2,g.turn,CONCAT(u.uid,'-',u.timestamp) as ptimes FROM " . TABLE_GAME . " g
            RIGHT JOIN " . TABLE_USER . " u ON g.playerid1 = u.uid or g.playerid2 = u.uid
            WHERE g.gameid = {$gameid} ";
  $result = db_query($conn, $sql);
  $t = array();
  while ($info = db_fetch_assoc($result)) {
    $n = $info;
    $t[] = "user" . $info['ptimes'];
  }
  $alive = checkIfOnline($n, $t);

  $temp['heart_beat'] = $alive;
  $string = json_encode($temp);
}
if (filter_input(INPUT_POST, 'type') && filter_input(INPUT_POST, 'type') === 'loads') {
  $game_id = filter_input(INPUT_POST, 'gameid', FILTER_VALIDATE_INT, 1);
  $subtype = filter_input(INPUT_POST, 'subtype');
  $tabs_id = $_POST['tabid'];

  $gameid = getCreatAcceptGame($conn, $subtype, $game_id);
  // Set gaming arena
  $init = FALSE;
  $game_tiles = '';
  $output = "";
  $set = 0;
  $sql = " SELECT g.status,g.gridstate,g.playerid1,g.playerid2,g.turn,CONCAT(u.uid,'-',u.timestamp) as ptimes FROM " . TABLE_GAME . " g
                              RIGHT JOIN " . TABLE_USER . " u ON g.playerid1 = u.uid or g.playerid2 = u.uid
                              WHERE g.gameid = {$gameid} ";
  $result = db_query($conn, $sql);

  $t = array();
  while ($info = db_fetch_assoc($result)) {
    $n = $info;
    $t[] = "user" . $info['ptimes'];
  }
  $alive = checkIfOnline($n, $t);

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
    $game_tiles.= createGameTiles($conn, $n);
  } else {
    // When the GAME is NEW
    $init = TRUE;
    $sucess = FALSE;
    $set = 0;
  }

  $output.= '<script type="text/javascript">
      turn = ' . $set . '; 
      alive = ' . $alive . ';
                </script>';
  $output.= $game_tiles;
  $output.= '<div class="game-area">';

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

    $fb_pic_p1 = '';
    $fb_pic_p2 = '';
    if ($canvas_flag) {
      $d1 = db_query($conn, " SELECT fname FROM " . TABLE_USER . " WHERE uid = {$n['playerid1']} ");
      $p1_name = db_fetch_assoc($d1);

      $d2 = db_query($conn, " SELECT fname FROM " . TABLE_USER . " WHERE uid = {$n['playerid2']} ");
      $p2_name = db_fetch_assoc($d2);

      if (!ENV_LOCAL) {
        $fb_pic_p1 = getFbProfilePic($fb, $n['playerid1']);
        $fb_pic_p2 = getFbProfilePic($fb, $n['playerid2']);
      }

      $p1_img = '<img class="profileimage" name="" src="' . $fb_pic_p1 . '" width="50" height="50" alt="' . $p1_name['fname'] . '">';
      $p1_mark = 'X';
      $p2_img = '<img class="profileimage" name="" src="' . $fb_pic_p2 . '" width="50" height="50" alt="' . $p2_name['fname'] . '">';
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
    $output.= '<div id="players_tile_section">
      <div class="p_section">
        <div class="p_img">' . $p1_img_place . '</div>
        <div class="' . $p1_class . '">' . $p1_mark_place . '</div>
      </div>
      <div class="p_section"><p>' . $info_message . '</p></div>
      <div class="p_section">
        <div class="p_img">' . $p2_img_place . '</div>
        <div class="' . $p2_class . '">' . $p2_mark_place . '</div>
      </div>
    </div>
    
    <div id="canvas">
      <input id="games_id" type="hidden" value="' . $gameid . '" />
      <input id="turn" type="hidden" value="' . $set . '" />
      <input id="alive" type="hidden" value="' . $alive . '" />';

    $output.= gamesGridDesign($grid, $gameid);
    $output.= '</div>
    <div class="clear"></div>';
  } else {
    $output.= '<h1>Access Denied! The Game has been occupied.</h1>';
  }
  $output.= '</div>
                  <div class="clear"></div>';

  $temp['arena'] = $output;
  $temp['tabs_id'] = $tabs_id;
  $temp['debug'] = $gameid;
  $string = json_encode($temp);
}

if (filter_input(INPUT_POST, 'type') && filter_input(INPUT_POST, 'type') === 'refreshtime') {
  $time = refreshTimes();
  $temp['refreshtime'] = $time;
  $string = json_encode($temp);
}

if (filter_input(INPUT_POST, 'type') && filter_input(INPUT_POST, 'type') === 'pager') {
  $currentpage = filter_input(INPUT_POST, 'currentpage', FILTER_VALIDATE_INT, 1);

  $uid = $_SESSION['uid'];
//  $g_query = "SELECT COUNT(gameid) AS num FROM ".TABLE_GAME." WHERE status = 'progress' AND ( uid = {$uid} OR playerid2 = {$uid} ) ORDER BY last_move DESC";
  $g_query = $_SESSION['pager_count'];
  list($num, $offset, $rowsperpage, $current_page, $totalpages) = initialize_pager($conn, $g_query, $currentpage);

//  $sql = "SELECT gameid, playerid1, playerid2, created, turn, last_move FROM ".TABLE_GAME." "
//          . "WHERE status = 'progress' AND ( uid = {$uid} OR playerid2 = {$uid} ) "
//          . "ORDER BY last_move DESC";

  $limit = " LIMIT {$offset}, {$rowsperpage}";
//  $g_sql = $sql . $limit;

  $g_sql = $_SESSION['pager_query'] . $limit;
  $result = db_query($conn, $g_sql);

  $play_game = 'Action';
  $g_created = 'Last Move';
  if ($_SESSION['g_type'] === 'new') {
    $play_game = 'Play Game';
    $g_created = 'Created';
  }
  $output = '';
//  $output.= '<p>' . $_SESSION['pager_query'] . '</p>';
//  $output.= '<p>' . $_SESSION['pager_count'] . '</p>';
  $output.= '<table id="book_data" width="480">
                    <thead>
                      <tr>
                        <th width="280">Game Against</th>
                        <th width="100">' . $play_game . '</th>
                        <th width="100">' . $g_created . '</th>
                      </tr>
                    </thead>
                    <tbody>';
  if ($num) {
    if ($_SESSION['g_type'] === 'new') {
      $output.= getNewGamesListing($conn, $result);
    } else {
      $output.= getGamesListing($conn, $result);
    }
  } else {
    $output.= '<tr><td width="150" colspan="3">There are no Active Games</td></tr>';
  }
  $output.= '</tbody>
                  </table>';

  if ($num > 10) {
    $output.= display_pager($currentpage, $totalpages);
  }

  $temp['display_data'] = $output;
  $string = json_encode($temp);
}
echo $string;
exit;
?>
