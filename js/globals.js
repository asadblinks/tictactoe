function refreshDate() {
  var url_path = base_url + "ajax.php";
  $.ajax({
    type: "POST",
    url: url_path,
    data: "type=refreshtime",
    cache: false,
    dataType: 'json',
    success: function (data) {
      var refreshtime = data['refreshtime'];
      $("#refresh_date").html('<p>' + refreshtime + '</p>');
    }
  });
}

function disableLinks() {
  $("ul.tabs li").each(function (index) {
    index++;
    $("ul.tabs li.tab" + index + " a").replaceWith($("ul.tabs li.tab" + index + " a").text());
  });
}

function returnLinks() {
  $('ul.tabs li').each(function (index) {
    index++;
    $(this).wrapInner('<a href="#' + $(this).attr("class").substring(0, 4) + '" />');
  });
}

function showLoadImage(element) {
  $(element).html('<div class="loader"></div>');
}
