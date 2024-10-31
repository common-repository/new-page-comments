function npc_GetParamByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

jQuery(document).ready(function($){
    $(".npc-main-wrapper a").click(function(e){
        var href = $(this).attr("href");
        var currentTab = npc_GetParamByName("tab",href);
        if(!currentTab){
            currentTab = "dashboard";
        }
        $(this).siblings().removeClass("nav-tab-active");
        $(this).addClass("nav-tab-active");
        $(".form-wrap").find(".npc-"+currentTab).siblings().hide();
        $(".form-wrap .npc-"+currentTab).show();       
        window.history.pushState("", "", href);
        if(currentTab=='help'){
            $('.form-wrap').find('p.submit').hide();
        }else{ $('.form-wrap').find('p.submit').show(); }
        return false;
    });
    var url      = window.location.href;     // Returns full URL
    var currentTab = npc_GetParamByName("tab",url);
    if(currentTab=='help'){
        $('.npc-help').find("tr th:first").hide()
        $('.form-wrap').find('p.submit').hide();
    }


    $('input[name="new-page-comment-opt[comment-load-type]"]').change(function(){
        $()
    });


    //Help Query
    $(".npc-send-query").on("click", function(e){
        e.preventDefault();   
        var message = $("#npc_query_message").val();           
                    $.ajax({
                        type: "POST",    
                        url: ajaxurl,                    
                        dataType: "json",
                        data:{action:"npc_send_email_query_message", message:message, security_nonce:security_nonce},
                        success:function(response){                       
                          if(response['status'] =='t'){
                            $(".npc-query-success").show();
                            $(".npc-query-error").hide();
                          }else{
                            $(".npc-query-success").hide();  
                            $(".npc-query-error").show();
                          }
                        },
                        error: function(response){                    
                        console.log(response);
                        }
                        });
        
    });
    $("input[name='new-page-comment-opt[comment-load-type]']").click(function(e){
        var current = $(this).val();
        commentOption(current);
    });
    commentOption($("input[name='new-page-comment-opt[comment-load-type]']:checked").val());
});
var commentOption = function(current){
    if(current=='lazy_load'){
            $(".npc-main-wrapper").find("#comment-btn-txt").parents('tr').hide();
            $(".npc-main-wrapper").find("#slug").parents('tr').hide();
            $(".npc-main-wrapper").find("#need_read_comment").parents('tr').hide();
        }else{
            $(".npc-main-wrapper").find("#comment-btn-txt").parents('tr').show();
            $(".npc-main-wrapper").find("#slug").parents('tr').show();
            $(".npc-main-wrapper").find("#need_read_comment").parents('tr').show();
        }
}