var turn_interval = 3000;
var alive_interval = (3000 * 2) - turn_interval;
$(document).ready(function () {
  // mix setup
  $.ajaxSetup({cache: false});
  var val = '';
  var url_path = '';

  $('body').on('click', '.col', function () {
    var your_turn = parseInt($('#turn').val());
    var html = $(this).html();
    if (your_turn) {
      if (html) {
        // Check if the cell is already occupied or not
        setMessage("This cell is already occupied");
      } else {
        // if it is not occupied then make your move.
        val = $(this).attr("id");
        url_path = base_url + "ajax.php";
        clearInterval(clock);
        clearInterval(live);
        $.ajax({
          type: "POST",
          url: url_path,
          data: "data=" + val,
          dataType: 'json',
          cache: false,
          error: function (jqXhr, textStatus, errorThrown) {
            console.log(errorThrown);
          },
          success: function (data) {
            var game_id = parseInt(data);
            updateGames(game_id, getTheGames);
          }
        }).done(function () {
          refreshDate();
          alive = $("#alive").val();
          if (alive) {
            clock = setInterval(getInfo, turn_interval);
          }
        });
      }
    }
  });

  $('body').on('click', 'a.game', function () {
    game_arena = true;
    var temp = $("ul.tabs li.active a").attr("href").split(" ");
    var tabs_id = temp[0].substring(1);
    var game = $(this).attr("href").split("-");
    var items = {type: game[0], game_id: game[1], tabs_id: tabs_id};

    // show loading image when data loads
    showLoadImage("#" + tabs_id + "_contents");

    // disable ul tag links
    disableLinks();
    loadGame(items, loadGameCanvas);
    return false;
  });

  $('body').on('click', 'a.pager', function () {
    var page = $(this).attr("href").split("-");
    var pager = {type: page[0], page_num: page[1]};
    loadPagination(pager);
    return false;
  });

});
// load the games canvas and tiles
function loadGame(item, getGame) {
  var game_id = parseInt(item['game_id']);
  var type = item['type'].substring(1);
  var tabs_id = item['tabs_id'];
  var url_path = base_url + "ajax.php";

  $.ajax({
    type: "POST",
    url: url_path,
    data: "type=loads&subtype=" + type + "&gameid=" + game_id + "&tabid=" + tabs_id,
    cache: false,
    dataType: 'json',
    error: function (jqXhr, textStatus, errorThrown) {
      console.log(errorThrown);
    },
    success: function (data) {
      getGame(game_id, data);
    }
  }).done(function () {
    returnLinks();
    refreshDate();
  });
}

function loadPagination(page) {
  // show loading image when data loads
  showLoadImage("#tbl_data");

  var url_path = base_url + "ajax.php";
  $.ajax({
    type: "POST",
    url: url_path,
    data: "type=pager&subtype=gamepage&currentpage=" + parseInt(page['page_num']),
    cache: false,
    dataType: 'json',
    success: function () {
      $("#tbl_data").html("");
    }
  }).done(function (data) {
    $("#tbl_data").html(data['display_data']);
    refreshDate();
  });
}

function loadGameCanvas(gameid, data) {
  var game_area = data['arena'];
  var tabs_id = data['tabs_id'];

  // Display games data
  $("#" + tabs_id + "_contents").hide();
  $("#" + tabs_id + "_contents").html(game_area);
  $("#" + tabs_id + "_contents").fadeIn();

  alive = parseInt($("#alive").val());
  turn = parseInt($("#turn").val());
  if (alive) {
    clearInterval(live);
    if (!turn) {
      clock = setInterval(getInfo, turn_interval);
      alive = parseInt($("#alive").val());
    } else {
      clearInterval(clock);
      clearInterval(live);
    }
  } else {
    clearInterval(clock);
    live = setInterval(showAlive, alive_interval);
  }
}

function setMessage(msg) {
  $(".p_section p").html(msg);
}

function getInfo() {
  var game_id = parseInt($('#games_id').val());
  var your_turn = parseInt($('#turn').val());
  if (!your_turn) {
    updateGames(game_id, getTheGames);
  } else {
    clearInterval(clock);
    clearInterval(live);
  }
}

function updateGames(gameid, callback) {
  var url_path = base_url + "ajax.php";
  $.ajax({
    type: "POST",
    url: url_path,
    data: "type=update&gameid=" + gameid,
    cache: false,
    dataType: 'json',
    success: function (data) {
      callback(gameid, data);
    }
  });
}

function getTheGames(gameid, data) {
  canvas_flag = parseInt(data['canvas_flag']);
  turn = parseInt(data['turn']);
  var message = data['message'];

  if (canvas_flag) {
    $("#canvas").html(data['grid_area']);
  }
  $("#status_msg").html("<p>" + message + "</p>");
  setMessage(message);

  alive = parseInt($("#alive").val());
  // When the user is not alive then increase alive interval and check alive
  if (!alive) {
    clearInterval(clock);
    live = setInterval(showAlive, alive_interval);
  }
}

function showAlive() {
  var game_id = parseInt($('#games_id').val());
  checkAlive(game_id, getItAlive);
}

function checkAlive(gameid, calls) {
  var url_path = base_url + "ajax.php";
  $.ajax({
    type: "POST",
    url: url_path,
    data: "type=alive&gameid=" + gameid,
    cache: false,
    dataType: 'json',
    success: function (data) {
      calls(gameid, data);
    }
  });
}

function getItAlive(gameid, data) {
  alive = parseInt(data['heart_beat']);
//  console.log('checking IF alive');
  if (alive) {
    // When the user is alive then move control to check turn
    $('#alive').val(alive);
    clearInterval(live);
    if (!turn) {
      clock = setInterval(getInfo, turn_interval);
    } else {
      clearInterval(clock);
      clearInterval(live);
    }
  } else {
    $('#alive').val(alive);
  }
}