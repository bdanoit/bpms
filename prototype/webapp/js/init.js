$(document).ready(function(){
    $('input.tokeninput').each(function(){
        $(this).tokenInput('/users-json', {hintText:"Type in a user name...",preventDuplicates:true});
    });
});