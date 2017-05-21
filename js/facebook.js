window.fbAsyncInit = function () {
  FB.init({
    appId: '149214754105',
    cookie: true, // This is important, it's not enabled by default
    xfbml: true,
    version: 'v2.4'
  });

  FB.getLoginStatus(function (response) {
    if (response.status === 'connected') {

    } else if (response.status === 'not_authorized') {
      FB.login(function (response) {
        if (response.authResponse) {
          onLogin();
        } else {
          console.log('User cancelled login or did not fully authorize.');
        }
      }, {scope: 'user_friends'});
    } else {
      console.log('the user isn\'t logged in to Facebook.');
    }
  });

};

(function (d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) {
    return;
  }
  js = d.createElement(s);
  js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

function onLogin() {
  var url_path = base_url + "loads.php";
  var dataToSend = "info_logs=1";
  $.ajax({
    type: "POST",
    url: url_path,
    data: dataToSend,
    cache: false,
    dataType: 'json',
    error: function (jqXhr, textStatus, errorThrown) {
      console.log(errorThrown);
    },
    success: function (data) {
      var content = parseInt(data['content']);
      if (content) {
        var content = data['data'];
        var inform_section = data['inform_section'];
        $("#tab1_contents").html(content);
        $("#inform_section").html(inform_section);
      } else {
        $("#tab1_contents").html(content);
      }
    }
  }).done(function () {
    $(".wrapper").fadeIn();
    refreshDate();
  });
}