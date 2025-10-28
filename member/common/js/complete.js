// 指定のコンテンツのステータスを完了とする。
// m_id：メンバーID
// ctg_id：カテゴリーID
// cont_id：コンテンツID
function complete(m_id, ctg_id, cont_id) {

  // ボタン連打防止
  if($("#complete").hasClass("disabled")) {
    return;
  } else {
    $("#complete").addClass("disabled");
  }

  var endpoint = "./complete.php";
  $.ajax(endpoint, {
    "type": "post",
    "data": {
      "m_id": m_id,
      "cont_id": cont_id,
      "ctg_id": ctg_id,
    },
    "cache": false,
  }).done(function(r) {
    if(r == "success") {
      $("#complete>a").remove();
      $("#complete").append("<div class=\"GrayBtn\">修了済み</div>");
    }
  }).fail(function(jqXHR, textStatus, errorThrown) {
    console.log(jqXHR);
  });
}

