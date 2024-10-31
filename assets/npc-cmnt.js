jQuery(document).ready(function($){
	$(".npc-lazy-load-comment").click(function(e){
		var self = $(this);
		var preData = self.html();
		var postId = self.find('.load-comment').attr('data-postid');
		$.ajax({
			url:npc_vars.ajax_url,
			dataType: 'json',
			method: 'post',
			data: { action:'new_page_comment_template', id: postId },
			success:function(res){
				if(res.status==200){
					self.parents('.npcmnt-wrap').html(res.comments).removeClass('npcmnt-wrap');
				}else{
					self.html(preData);
				}
			}
		})
	});
});
