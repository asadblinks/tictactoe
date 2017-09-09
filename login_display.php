<form id="access-forms" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
  <input id="err_subs" value="0" type="hidden">
  <div class="input_fields">
    <input class="input_login" type="text" id="user" name="user" placeholder="Email Address">
    <span class="error_fields err_user"><p></p></span>
  </div>
  <div class="input_fields">
    <input class="input_login" type="password" id="password" name="password" placeholder="Password">
    <span class="error_fields err_password"><p></p></span>
  </div>
  <input class="btn_submit" type="submit" id="login" name="login" value="Login"/>
</form>