$(document).ready(function(){
	$(document).on("submit", ".frm", function(e){
		e.preventDefault();
		var data = $(".frm").serialize();
		$(".error").text('');
		$(".link").attr('href', '').text('');
		$(".message").text('');
		$(".wrapper-charger").css({'display':'block'});
		$.ajax({
			type: 'POST',
			url: 'update_product_ajax.php',
			data: data,
			success: function(data) {
                console.log(data);
				data = $.parseJSON(data);
				var error = data.error, link = data.link, message = data.message, link_msj = data.link_msj;
				console.log(link);
				$(".error").text(error);
				$(".link").attr('href', link).text(link_msj);
				$(".message").text(message);
				$(':input','.frm').not(':button, :submit, :reset, :hidden').val('');
				$(".wrapper-charger").css({'display':'none'});
			}
	  	});
	  	return false;
	});
});