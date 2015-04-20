var tpj=jQuery;
tpj.noConflict();
tpj(document).ready(function() {
	var labelTag = tpj(".userName").prev('label').html();
	tpj(".userName").prev('label').html(labelTag+' '+'<span class="unameCheck"></span>');
	tpj(".userName").keyup(function(){
		var userName = this.value;			
		tpj.ajax({
		  type: 'POST',
		  url: adminUrl+'/admin-ajax.php',
		  data: {
		  action: 'userNameExitFunction',
		  usernameCheck:userName
		  },
		  success: function(data, textStatus, XMLHttpRequest){
		  //tpj("#uname").html('');
		  //tpj("#unameCheck").html('');
		  //tpj(".userName").after('');
		  		  
		  tpj(".unameCheck").html(data);
		  
		  
		  },
		  error: function(MLHttpRequest, textStatus, errorThrown){
		  alert(errorThrown);
		  }
		  
		  });
	});
	
});