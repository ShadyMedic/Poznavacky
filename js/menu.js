$(function(){
    $('#add-classes-button').click(function(){
		$('#add-classes-button img').hide();
		$('#classes-form').show();
	});
	$('#enter-class-code-button').click(function(){
		$('#enter-class-code-button').hide();
		$("#class-code-form").show();
	})
	$('#close-class-code-button').click(function(){
		$('#enter-class-code-button').show();
		$("#class-code-form").hide();
	})
});
