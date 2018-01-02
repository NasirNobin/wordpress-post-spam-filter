(function($){
	$(document).ready(function(){

		// click on a spam link append link to textarea and hide it
		$('.trickbd_psb_spam_link').on('click',function(e){
			// we dont want to redirect
			e.preventDefault();
			var link=$(this).attr('href');

			// grab old value
			var old_value = $("#trickbd_psb_keywords_list").val();
			if(old_value){
				$("#trickbd_psb_keywords_list").val(old_value+"\n"+link);
			}else{
				$("#trickbd_psb_keywords_list").val(link);
			}
			var txtareaheight = $("#trickbd_psb_keywords_list")[0].scrollHeight;
			$("#trickbd_psb_keywords_list").scrollTop(txtareaheight);
			$(this).hide();
		});

		// show and hide suggestion
		if(!$("#psb_show_suggetion").prop("checked")){
			$("#psb_suggestion").hide();
		}
		$("#psb_show_suggetion").on("click",function(e){
			$("#psb_suggestion").toggle();
		});
	});
})(jQuery)