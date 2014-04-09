
$(document).ready(function(){
    
    $('form').submit(function(){ $(this).find('input[type=submit]').attr('disabled', true).val('Loading...'); return true; });
    
});