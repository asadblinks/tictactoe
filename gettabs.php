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
// Update current timestamp of user
db_query($conn, "UPDATE " . TABLE_USER . " SET timestamp = UNIX_TIMESTAMP(now()) WHERE uid = {$uid} ");
if (isset($_POST['tab'])) {
  $tab_id = trim($_POST['tab']);
  $output = '';
  if ($tab_id === 'tab1') {// Games - Main TAB
    $_SESSION['g_type'] = 'games';
    $uid = $_SESSION['uid'];
    $query = "SELECT COUNT(gameid) AS num FROM " . TABLE_GAME . " WHERE status = 'progress' AND ( uid = {$uid} OR playerid2 = {$uid} ) ";
    $_SESSION['pager_count'] = $query;
    list($num, $offset, $rowsperpage, $currentpage, $totalpages) = initialize_pager($conn, $query);
    $inf = ($num) ? $num . " active Game(s)" : "no active Game";
    $output.= '<div class="post">
                  <p>
                    Currently, you have ' . $inf . '. You can make your move from Actions or View Game while your opponent has not responded to your turn.
                    Do you want to create a <strong>new Game</strong>? Click <a class="more anchors" href="#tab2">here</a>.
                  </p>
                  <span class="line"></span>
                </div>';

    $sql = "SELECT gameid, playerid1, playerid2, created, turn, last_move FROM " . TABLE_GAME . " "
            . "WHERE status = 'progress' AND ( uid = {$uid} OR playerid2 = {$uid} ) "
            . "ORDER BY last_move DESC";

    // save query for pagination
    $_SESSION['pager_query'] = $sql;
    $limit = " LIMIT {$offset}, {$rowsperpage}";
    $game_query = $sql . $limit;

    $output.= '<div id="tbl_data" style="float: left">
                  <table id="book_data" width="480">
                    <thead>
                      <tr>
                        <th width="280">Game Against</th>
                        <th width="100">Action</th>
                        <th width="100">Last Move</th>
                      </tr>
                    </thead>
                    <tbody>';
    if ($num) {
      $result = db_query($conn, $game_query);
      $output.= getGamesListing($conn, $result);
    } else {
      $output.= '<tr><td width="150" colspan="3">There are no Active Games</td></tr>';
    }
    $output.= '</tbody>
                  </table>';

    if ($num > 10) {
      $output.= display_pager($currentpage, $totalpages);
    }

    $output.= '</div>';
  }
  if ($tab_id === 'tab2') {// New Game TAB
    $_SESSION['g_type'] = 'new';
    $output.= '<div class="post">
                  <p>Click here to <a class="game" href="#new-0">create New Game</a></p>
                  <span class="line"></span>
               </div>';

    $sql = "SELECT COUNT(gameid) AS num FROM " . TABLE_GAME . " WHERE status = 'new'";
    $_SESSION['pager_count'] = $sql;
    list($num, $offset, $rowsperpage, $currentpage, $totalpages) = initialize_pager($conn, $sql);
    $output.= '<div id="tbl_data" style="float: left">
                  <table id="book_data" width="480">
                    <thead>
                      <tr>
                        <th width="280">Game</th>
                        <th width="100">Play Game</th>
                        <th width="100">Created</th>
                      </tr>
                    </thead>
                    <tbody>';
    $q_sql = "SELECT gameid, playerid1, created, turn FROM " . TABLE_GAME . " WHERE status = 'new' ORDER BY created DESC";
    $limit = " LIMIT {$offset}, {$rowsperpage}";
    $_SESSION['pager_query'] = $q_sql;
    $new_query = $q_sql . $limit;

    if ($num) {
      $result = db_query($conn, $new_query);
      $output.= getNewGamesListing($conn, $result);
    } else {
      $output.= '<tr><td width="150" colspan="3">There are no Active Games</td></tr>';
    }
    $output.= '</tbody>
                  </table>';
    if ($num > 10) {
      $output.= display_pager($currentpage, $totalpages);
    }
    $output.= '</div>';
  }
  if ($tab_id === 'tab3') {// New Game TAB
    $output.= '<div class="post">
                  <p>These are statistics showing how many games you have played, won and lost.</p>
                  <p>More stats are coming soon!</p>
                  <span class="line"></span>
               </div>';
    $sql = " SELECT n_games,won,draw,lost,score FROM " . TABLE_USER . " WHERE uid = {$uid} ";
    $result = db_query($conn, $sql);

    $output.= '<div id="tbl_data3" style="float: left">
          <table id="book_data3" width="480">
            <thead>
              <tr>
                <th width="180">Games Played</th>
                <th width="75">Won</th>
                <th width="75">Draw</th>
                <th width="75">Lost</th>
                <th width="75">Score</th>
              </tr>
            </thead>
            <tbody>';
    $info = db_fetch_assoc($result);

    $n_games = $info['n_games'];
    $won = $info['won'];
    $draw = $info['draw'];
    $lost = $info['lost'];
    $score = $info['score'];

    $output.= '<tr>';
    $output.= '<td align="center" width="250">' . $n_games . '</td>';
    $output.= '<td align="center" width="100">' . $won . '</td>';
    $output.= '<td align="center" width="100">' . $draw . '</td>';
    $output.= '<td align="center" width="100">' . $lost . '</td>';
    $output.= '<td align="center" width="100">' . $score . '</td>';
    $output.= '</tr>';

    $output.= '</tbody>
          </table>
        </div>';
  }
  if ($tab_id === 'tab4') {// Games - Main TAB
    $output.= '<div class="post">
                  <p>Following are the people who are online this time around. You can click on them to challenge for the game.</p>
                  <span class="line"></span>
               </div>';
  }
  if ($tab_id === 'tab5') {// Games - Main TAB
    // Get the Game Tile of current user
    $t = db_query($conn, "  SELECT tile_id FROM " . TABLE_USER . " WHERE uid = {$uid} ");
    $tile = db_fetch_assoc($t);
    $current_tile = ($tile['tile_id']) ? intval($tile['tile_id']) : 3;

    $output.= '<div class="post">
                  <p>Please select Tile to set the default color to be shown in background</p>
                  <span class="line"></span>
               </div>';

    $output.= '<div id="tbl_data5" style="float: left">
          <table id="book_data3" width="480">
            <thead>
              <tr>
                <th width="25"></th>
                <th width="275">Tiles</th>
                <th width="180" colspan="2">Preview</th>
              </tr>
            </thead>
            <tbody>';
    $sql = "  SELECT * FROM " . TABLE_TILE;
    $result = db_query($conn, $sql);
    $num = numRows($result);

    if ($num) {
      $p = 0;
      while ($info = db_fetch_assoc($result)) {
        $c = '';
        $tile_id = intval($info['tile_id']);
        $name = $info['name'];
        $def_tile = $info['def_tile'];
        $sec_tile = $info['sec_tile'];
        if ($current_tile) {
          if ($current_tile === $tile_id) {
            $c.= 'checked="checked"';
          }
        }
        $class = ($p % 2) ? 'odd' : 'even';
        $p++;
        $output.= "<tr class=\"{$class}\">";
        $output.= '<td width="25" style="text-align: center"><input type="radio" ' . $c . ' value="' . $tile_id . '" name="tiles" class="tiles" /></td>';
        $output.= '<td width="275">' . $name . '</td>';
        $output.= '<td width="90" style="background: #' . $def_tile . '"></td>';
        $output.= '<td width="90" style="background: #' . $sec_tile . '"></td>';
        $output.= '</tr>';
      }
    } else {
      $output = '<tr><td width="150" colspan="4">There are no Tiles</td></tr>';
    }
    $output.= '</tbody>
          </table>
        </div>';
    $output.= '<input class="button" type="submit" value="Set Tile" name="save_tiles" id="save_tiles" style="margin-top: 20px" />';
  }

  $temp = array();
  $temp['tab'] = $tab_id;
  $temp['content'] = $output;
  $string = json_encode($temp);
}
if (isset($_POST['tiles'])) {
  $tile = intval($_POST['tiles']);
  $update = " UPDATE " . TABLE_USER . " SET tile_id = {$tile} WHERE uid = {$uid} ";
  db_query($conn, $update);
}

echo $string;
exit;
?>
