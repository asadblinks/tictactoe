<?php

require_once 'db.php';
$conn = connect();
$fb = connectFacebook();

$helper = $fb->getJavaScriptHelper();

$errors = 'No Errors! ';
$no_error = TRUE;
try {
  $accessToken = $helper->getAccessToken();
} catch (Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  $errors = 'Graph returned an error: ' . $e->getMessage();
  $no_error = FALSE;
} catch (Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  $errors = 'Facebook SDK returned an error: ' . $e->getMessage();
  $no_error = FALSE;
}

if (!isset($accessToken)) {
  $errors = 'No cookie set or no OAuth data could be obtained from cookie.';
  $no_error = FALSE;
}

if (isset($accessToken)) {
  // Logged in.

  try {
    $token = (string) $accessToken->getValue();
    $_SESSION['fb_token'] = $token;
    $response = $fb->get('/me?fields=id,name,picture.width(50).height(50)', $token);
    $user = $response->getGraphUser();

    $_SESSION['uid'] = $user['id'];
    $_SESSION['fname'] = $user['name'];
    $_SESSION['picture_url'] = $user['picture']['url'];
  } catch (Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    $errors.= 'Graph returned an error: ' . $e->getMessage();
    $no_error = FALSE;
  } catch (Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    $errors.= 'Facebook SDK returned an error: ' . $e->getMessage();
    $no_error = FALSE;
  }
}

$string = '';
$temp = array();
if (isset($_POST['info_logs'])) {
  $temp['content'] = 0;
  if ($no_error === TRUE) {
    $data = '';
    $inform_section = '';
    $temp['content'] = 1;

    $uid = $_SESSION['uid'];
    $fname = $_SESSION['fname'];
    $pic = $_SESSION['picture_url'];
    $type = 'user';
    $time = time();

    $sql = "SELECT uid FROM " . TABLE_USER . " WHERE uid = " . $uid;
    $result = db_query($conn, $sql);
    if (numRows($result) == 0) {
      $inserts_user = "INSERT INTO " . TABLE_USER . " (uid, fname, type, timestamp) VALUES ({$uid}, '{$fname}', '{$type}', {$time})";
      if (db_query($conn, $inserts_user)) {
        $data.= '<div class="post">
                    <p>
                      Currently, you have no active Game. You can make your move from Actions or View Game while your opponent has not responded to your turn.
                      Do you want to create a <strong>new Game</strong>? Click <a class="more anchors" href="#tab2">here</a>.
                    </p>
                    <span class="line"></span>
                  </div>';
        $data.= '<div id="tbl_data" style="float: left">
                    <table id="book_data" width="480">
                      <thead>
                        <tr>
                          <th width="280">Game Against</th>
                          <th width="100">Action</th>
                          <th width="100">Last Move</th>
                        </tr>
                      </thead>
                      <tbody>
                      <tr><td width="150" colspan="3">There are no Active Games</td></tr>
                      </tbody>
                    </table>
                  </div>';

        $inform_section.= '<div class="tabHeader" style="margin-top: 5px">Hi ' . $fname . '</div>
          <img class="profileimage" name="" src="' . $pic . '" width="50" height="50" alt="' . $fname . '">';
      }
    }

    $temp['data'] = $data;
    $temp['inform_section'] = $inform_section;
  } else {
    $temp['data'] = $errors;
  }
  $string = json_encode($temp);
}
echo $string;
exit;
?>
