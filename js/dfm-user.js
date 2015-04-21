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
	
	var passLabelTag = tpj(".userPass").prev('label').html();	
	tpj(".userPass").prev('label').html(passLabelTag+' '+'<span id="passwordStrength"></span>');
	tpj(".userPass").after('<input type="hidden" id="passLabel" name="passLabel" value="" />');
	
	var passMatchLabelTag = tpj(".userRePass").prev('label').html();	
	tpj(".userRePass").prev('label').html(passMatchLabelTag+' '+'<span id="passwordMatch"></span>');
	tpj(".userRePass").after('<input type="hidden" id="passReLabel" name="passReLabel" value="" />');
	
	tpj('.userPass').keyup(function(e) {		
     var strongRegex = new RegExp("^(?=.{6,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g");
     var mediumRegex = new RegExp("^(?=.{5,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
     var enoughRegex = new RegExp("(?=.{4,}).*", "g");
     if (false == enoughRegex.test(tpj(this).val())) {
			 tpj('#passwordStrength').attr('class','veryWeak_pass');
			 tpj('#passLabel').val('very_weak');
             tpj('#passwordStrength').html('Very weak');
     } else if (strongRegex.test(tpj(this).val())) {
             tpj('#passwordStrength').attr('class','strong_pass');
			 tpj('#passLabel').val('strong');
             tpj('#passwordStrength').html('Strong!');
     } else if (mediumRegex.test(tpj(this).val())) {
             tpj('#passwordStrength').attr('class','medium_pass');
			 tpj('#passLabel').val('medium');
             tpj('#passwordStrength').html('Medium!');
     } else {
             tpj('#passwordStrength').attr('class','weak_pass');
			 tpj('#passLabel').val('weak');
             tpj('#passwordStrength').html('Weak!');
     }
	 
	 /*var userPassword = tpj(".userPass").val();
		 var userRePassword = tpj(".userRePass").val();
		 if(userPassword !='' && userRePassword != ''){
			if(userPassword == userRePassword){
				tpj('#passwordMatch').html('');
				tpj('#passwordStrength').attr('class','passMatch');
				tpj('#passwordStrength').html('Password match');
			} else {
				tpj('#passwordMatch').html('');
				tpj('#passwordStrength').attr('class','passNoMatch');
				tpj('#passwordStrength').html('Password mismatch!');
			}
	 } */
	 
     return true;
});

	
	
	tpj('.userRePass').keyup(function(e) {		
		 var userPassword = tpj(".userPass").val();
		 var userRePassword = tpj(".userRePass").val();
		 if(userPassword !='' && userRePassword != ''){
			if(userPassword == userRePassword){
				tpj('#passReLabel').val('match');
				tpj('#passwordMatch').attr('class','passMatch');
				tpj('#passwordMatch').html('Password match');
			} else {
				tpj('#passReLabel').val('mismatch');
				tpj('#passwordMatch').attr('class','passNoMatch');
				tpj('#passwordMatch').html('Password mismatch!');
			}
		 }
     return true;
	});
	
	
	
});