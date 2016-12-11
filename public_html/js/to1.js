$(".confirmKumpulTO").click(function(){
    $(window).unbind('beforeunload');
    $("#form").hide(1000);
    $("#process").show(1000);
    jQuery(function(){
          jQuery('.kumpulTO').click();
    });
    $(".progress-bar").animate({
        width: "100%"
    }, 4500);
});
$(".hideTimer").click(function(){
    $(".timer").toggle(1000);
});
$('.alt-2').countDown({
        css_class: 'countdown-alt-2'
    });
$('.alt-2').on('time.elapsed', function () {
    $(window).unbind('beforeunload');
    $("#form").hide(1000);
    $("#process").show(1000);
    jQuery(function(){
          jQuery('.kumpulTO').click().delay(5000);
    });
    $(".progress-bar").animate({
        width: "100%"
    }, 4500);
});
$(function () {
      $('[data-toggle="modal"]').tooltip()
    })
$(window).bind('beforeunload', function(){
  return 'Hasil tryout belum disubmit. Tetap tinggalkan?';
});