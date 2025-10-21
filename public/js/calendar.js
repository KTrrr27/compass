$(function(){
  //編集用のモーダルを引用して削除モーダル作成
  $('.day-delete-modal-open').on('click',function(){
    var reserve_id = $(this).attr('reserve_id');
    $('.delete-js-modal').fadeIn();
    $('.delete-modal-hidden-reserveId').val(reserve_id);
    $('.delete-js-modal form').attr('action',`/delete/calendar`);
    return false;
  });
  $('.js-modal-close').on('click', function () {
    $('.delete-js-modal').fadeOut();
    return false;
  });
});
