$(document).ready(function(){
    
    
    $('#bpms-overview').each(function(){
        var w = $(this).children().attr('id') * 14 * 2;
        $(this).scrollLeft(w - $(this).width()/2);
        $(this).find('.bpms-tooltip').tooltip({minWidth:'10em'});
        console.log($(this).find('.progress a'));
    });
    
    // enable autocomplete
    $('.ac-user').each(function(){
        var id = $(this).attr('id').match(/ac_user_pid_(\d+)/);
        if(!id) return;
        var remote = '/project/'+id[1]+'/members/json?query=%QUERY';
        var users = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            //prefetch: '../data/films/post_1960.json',
            remote: remote
        });
     
        users.initialize();
     
        $(this).typeahead(null, {
            hint: true,
            highlight: true,
            minLength: 2,
            name: 'users',
            displayKey: 'name',
            source: users.ttAdapter()
        });
    });
});