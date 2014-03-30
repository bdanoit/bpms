$(document).ready(function(){
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