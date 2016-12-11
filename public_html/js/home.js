$(".TOsource").click(function(){
            var id = $(this).attr('id');
            $(".lanjutkan").attr("id",id);
        });
$(".lanjutkan").click(function(){
    var basic = "{{url('tryout')}}/";
    var idTO = $(this).attr('id')+"/{{$bagian}}";
    window.location.href = basic+idTO;
});