$(function () {
  $('[data-toggle="tooltip"]').tooltip();

  $(document).on("click", ".delete", function (e) {
    e.preventDefault();
    $("#load").fadeIn();
    var id = $(this).attr("id");
    var string = "id=" + id;
    var commentContainer = $("#img-" + id);
    var pic_id;
    $.ajax({
      type: "POST",
      url: "/gallery/default/remove-image",
      data: string,
      cache: false,
      success: function () {
        commentContainer.hide();
        $("#ximg-" + id).show();
        $("#" + id).hide();
        $.pjax.reload({ container: "#gallery", async: false });
        //  window.location.reload() ;
      },
    });
    return false;
  });

  $(".image_select form").hide();
  $(".image_select a")
    .file()
    .choose(function (e, input) {
      input.appendTo(".image_select form");
      $(".image_select form").ajaxSubmit({
        beforeSubmit: function (formData, jqForm, options) {
          var theId = $(".image_select form");
          var queryString = $.param(formData);
          var formElement = formData[1]["pict"];
          pic_id = formElement;
          console.log("Form Data Q:" + JSON.stringify(formData));
          console.log("Pic Id :" + formData[1]["value"]);
          return true;
        },
        success: function (responseText) {
          var span = $("<span/>");
          var btn = $("<i/>");
          btn.attr("id", responseText);

          btn.addClass("fa fa-minus-inverse");
          var img = $("<img/>")
            .bind("load", function (e) {
              $(e.target).click(function () {
                $(this).hide();
              });

              span.html(e.target);
              img.closest("span").append(btn);
              $("#add-file-" + pic_id).append(span);
              $("#ximg-" + parseInt(responseText) + " form").hide();
            })
            .attr("id", "img-" + responseText)
            .attr(
              "src",
              "/gallery/default/create?id=" +
                responseText.toString() +
                "&version=small&key=" +
                new Date().getTime()
            );
          console.log("responseText" + responseText);
          $.pjax.reload({ container: "#gallery", async: false });
          //   window.location.reload();
        },
      }); //ajaxSubmit
    });
});
