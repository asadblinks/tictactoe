var turn;
var alive;
var clock;
var live;

$(document).ready(function () {
  $(".tab_content").hide(); //On page load hide all the contents of all tabs
  $("ul.tabs li:first").addClass("active").show(); //Default to the first tab
  $(".tab_content:first").show(); //Show the default tabs content

  $.ajaxSetup({cache: false});

  validationInputs();

  //When the user clicks on the tab
  $('body').on('click', 'ul.tabs li', function () {
    clearInterval(clock);
    clearInterval(live);

    var temp = $("ul.tabs li.active a").attr("href").split(" ");
    var prev = temp[0];
    $(prev + "_contents").html("");

    $("ul.tabs li").removeClass("active"); //Remove the active class
    $(this).addClass("active"); //Add the active tab to the selected tab
    $(".tab_content").hide(); //Hide all other tab content

    var activeTab = $(this).find("a").attr("href"); //Get the href's attribute value (class) and fade in the corresponding tabs content
    var temp = $(this).attr("class").split(" ");
    var tabs_id = temp[0];

    $('ul.tabs li').unbind('click');
    $(activeTab).fadeIn(); //Fade the tab in
    if ((activeTab !== '#tabs2') && (activeTab !== '#tabs1')) {
      // Show specific tab
      showSpecificTab(tabs_id);
    }
    $('ul.tabs li').bind('click');
    return false;
  });

  // Click here for creating new Game callback
  $('body').on('click', 'a.anchors', function () {
    $("ul.tabs li").removeClass("active"); //Remove the active class
    $("ul.tabs li.tab2").addClass("active"); //Add the active tab to the selected tab
    $(".tab_content").hide(); //Hide all other tab content

    var activeTab = $(this).attr("href");
    $(activeTab).fadeIn(); //Fade the tab in

    // Show specific tab
    showSpecificTab('tab2');
    return false;
  });


  $('body').on('click', '#save_tiles', function () {
    var selectedValue = 0;
    var selected = $("input[type='radio'][name='tiles']:checked");
    if (selected.length > 0) {
      selectedValue = selected.val();
    }

    var full_url = base_url + "gettabs.php";
    $.ajax({
      type: "POST",
      url: full_url,
      data: "tiles=" + selectedValue,
      cache: false,
      error: function (jqXhr, textStatus, errorThrown) {
        console.log(errorThrown);
      },
      success: function (data, status, xhr) {
        $("#tab5_contents").html("Updating Tile...");
        showSpecificTab('tab5');
      }
    }).done(function () {
      refreshDate();
    });
    return false;
  });
});

function showSpecificTab(tabs_id) {
  var full_url = base_url + "gettabs.php";
  showLoadImage("#" + tabs_id + "_contents");
//  console.log(full_url + " : " + tabs_id);
  disableLinks();
  $.ajax({
    type: 'POST',
    url: full_url,
    data: "tab=" + tabs_id,
    cache: false,
    dataType: 'json',
    error: function (jqXhr, textStatus, errorThrown) {
      console.log(errorThrown);
    },
    success: function (data, status, xhr) {
//      console.log(tabs_id);
//      var tab = data['tab'];
      var content = data['content'];
      $("#" + tabs_id + "_contents").hide();
      $("#" + tabs_id + "_contents").html(content);
    }
  }).done(function (data) {
//    var tab = data['tab'];
    $("#" + tabs_id + "_contents").fadeIn();
    returnLinks();
    refreshDate();
  });
}

function myTimer() {
  var dateDiff = new Date();
  var t = dateDiff.toLocaleTimeString();
  $(".server_time").html(t);
}

function profileImage() {
  var full_url = base_url + "images/profile-2.jpg";
  $(".profileimage").attr('src', full_url);
}

function validateEmail(sEmail) {
  var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
  var bool = null;
  if (filter.test(sEmail)) {
    bool = true;
  } else {
    bool = false;
  }
  return bool;
}
function validationInputs() {
  var validEmail = true;
  $(".input_fields input").blur(function () {
    if (!$(this).val()) {
      var id = $(this).attr('id');
      $(".err_" + id).html("");
      $(".err_" + id).append("<p>Input required</p>");
      $("#err_subs").val("0");
    } else {
      var id = $(this).attr('id');
      $(".err_" + id).html("");
      if (id === 'user') {
        var email = $(this).val();
        if (!validateEmail(email)) {
          $(".err_" + id).html("");
          $(".err_" + id).append("<p>Invalid Email Address</p>");
          $("#err_subs").val("0");
          validEmail = false;
        } else {
          validEmail = true;
        }
      }
      if (!validEmail) {
        $("#err_subs").val("0");
      } else {
        $("#err_subs").val("1");
      }
    }
  });

  $('#access-forms').submit(function (e) {
    var submission = parseInt($("#err_subs").val());
    if (!submission) {
      e.preventDefault();
      return false;
    }
  });
}