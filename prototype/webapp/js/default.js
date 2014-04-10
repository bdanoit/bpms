

//Overview navigation
(function(window){

    // Bind to StateChange Event
    History.Adapter.bind(window,'statechange',function(){ // Note: We are using statechange instead of popstate
        var state = History.getState(); // Note: We are using History.getState() instead of event.state
        $.post("/project/"+overview.project_id+"/index-json", state, function(result) {
            $('#bpms_overview').each(function(){
                overview.result = result;
                overview.wrapper = $('#bpms_wrapper');
                overview.draw(result.data);
            });
        });
    });

})(window);

$(document).ready(function(){
    // enable autocomplete
    $('.ac-user').each(function(){
        var id = $(this).attr('id').match(/ac_user_pid_(\d+)/);
        if(!id) return;
        var remote = '/project/'+id[1]+'/members/json?query=%QUERY';
        var users = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
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
    
    $('#bpms_wrapper').each(function(){
        var wrapper = $(this);
        wrapper.children('.bpms-canvas-data').scroll(function (){
            wrapper.children(".bpms-canvas-header").css({ left: -1*this.scrollLeft });
        });
        var project_id = $(this).attr('project-id');
        overview.project_id = project_id;
        $.getJSON("/project/"+project_id+"/index-json", function(result) {
        $('#bpms_controls').each(function(){
            if(!result.milestones) return;
            var ms = $('<div class="bpms-milestones btn-group"></div>');
            $(this).append(ms);
            var a = $('<a class="btn btn-md btn-default" href="#">All</a>');
            ms.append(a);
            a.on('click', function(){
                ms.children().removeClass('active');
                $(this).addClass('active');
                var state = History.getState();
                var data = state.data;
                data.id = 'all';
                History.pushState(data, "BPMS", '?'+serialize(data));
                return false;
            });
            for(var idx in result.milestones){
                (function(milestone){
                    var a = $('<a class="btn btn-md btn-default'+(result.phase.id == milestone.id?' active':'')+'" href="#">'+milestone.name+'</a>');
                    ms.append(a);
                    a.on('click', function(){
                        ms.children().removeClass('active');
                        $(this).addClass('active');
                        var state = History.getState();
                        var data = state.data;
                        data.id = milestone.id;
                        History.pushState(data, "BPMS", '?'+serialize(data));
                        return false;
                    });
                })(result.milestones[idx]);
            }
            var completed = $('<label class="checkbox-inline"><input type="checkbox"> Hide completed tasks</label>');
            completed.children('input').on('change', function(){
                var state = History.getState();
                var data = state.data;
                data.status = this.checked ? 1 : 0;
                History.pushState(data, "BPMS", '?'+serialize(data));
            });
            $(this).append(completed);
        });
            $('#bpms_overview').each(function(){
                overview.result = result;
                overview.wrapper = wrapper;
                overview.draw(result.data);
            });
        });
    });
});

serialize = function(obj) {
  var str = [];
  for(var p in obj)
    if (obj.hasOwnProperty(p)) {
      str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
    }
  return str.join("&");
};

var overview = {
    project_id: null,
    result: null,
    wrapper: null,
    clear: function(){
        var wrapper = this.wrapper;
        var result = this.result;
        var canvas = wrapper.find('#bpms_overview');
        var heading = wrapper.find('#bpms_heading');
        var h_context = heading[0].getContext("2d");
        var context = canvas[0].getContext("2d");
        canvas.attr('width', 0);
        canvas.attr('height', 0);
        heading.attr('width', 0);
        heading.attr('height', 0);
        this.wrapper.prepend('<blockquote class="no-tasks alert-danger">No tasks found</blockquote>');
        this.wrapper.find('canvas').hide();
    },
    draw: function (data, pxd){
        if(!data || !data.length) return this.clear();
        this.wrapper.find('.no-tasks').remove();
        this.wrapper.find('canvas').show();
        var wrapper = this.wrapper;
        var result = this.result;
        var canvas = wrapper.find('#bpms_overview');
        var heading = wrapper.find('#bpms_heading');
        var h_context = heading[0].getContext("2d");
        var context = canvas[0].getContext("2d");
        canvas.parent().scrollLeft(0);
        canvas.parent().scrollTop(0);
        
        //data from db
        var s_in_day = 86400;
        var num_rows = (data.length >= 10) ? data.length : 10;
        var start = result.start;
        var end = result.end;
        var first_day_i = result.first_day_i;
        var last_day_i = result.last_day_i;
        var first_day_s = result.first_day_s;
        var elapsed = end-start;
        var num_months = result.num_months;
        //var num_days = (result.num_days >= 31) ? result.num_days : 31;
        var num_days = result.num_days;
        
        //pixel data
        var pxd = pxd ? pxd : 40; //pixels per day square
        var half_pxd = Math.round(pxd / 2);
        
        //set cavas size
        canvas.attr('width', num_days * pxd);
        canvas.attr('height', num_rows * pxd);
        heading.attr('width', num_days * pxd);
        heading.attr('height', 3 * pxd);
        
        //canvas font settings
        var font_size = Math.floor(pxd / 2.6);
        var half_font = Math.round(font_size / 2);
        var text_padding = Math.round(font_size * .5);
        context.font = h_context.font = "bold "+font_size+"px sans-serif";
        context.textAlign = h_context.textAlign = 'center';
        context.textBaseline = h_context.textBaseline = 'middle';
        
        //grid textual data
        var days = ['S','M','T','W','T','F','S'];
        var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        var current_month = new Date(start * 1000).getMonth();
        
        //draw the month grid
        var y_offset = half_pxd;
        var x = 0;
        var days_in_month = 0;
        for(var i = 0; i < num_months; i++){
            var date_info = new Date((days_in_month * s_in_day + start) * 1000);
            days_in_month = new Date(date_info.getYear(), current_month + 1, 0).getDate();
            if(i == 0){
                days_in_month = days_in_month - first_day_i + 1;
            }
            else if((i+1) == num_months){
                days_in_month = days_in_month - (days_in_month - last_day_i) - 1;
            }
            var month = months[current_month];
            h_context.fillText(month, days_in_month * pxd / 2 + x, y_offset);
            x+= days_in_month * pxd;
            h_context.moveTo(x + 0.5, 0);
            h_context.lineTo(x + 0.5, 1 * pxd);
            //update current_month
            current_month = (current_month == 11) ? 0 : current_month+1;
        }
        
        //draw the grid
        for (var x = 0.5 + pxd; x < num_days * pxd; x += pxd) {
            h_context.moveTo(x, pxd);
            h_context.lineTo(x, 3 * pxd);
            context.moveTo(x, 0);
            context.lineTo(x, num_rows * pxd);
        }
        for (var y = pxd-0.5; y <= 3 * pxd + pxd; y += pxd) {
            h_context.moveTo(0, y);
            h_context.lineTo(num_days * pxd, y);
        }
        for (var y = pxd-0.5; y < num_rows * pxd + pxd; y += pxd) {
            context.moveTo(0, y);
            context.lineTo(num_days * pxd, y);
        }
        context.strokeStyle = h_context.strokeStyle = "#ccc";
        context.stroke(); h_context.stroke();
        
        //draw the days (int and string)
        context.font = h_context.font = font_size+"px sans-serif";
        var x_offset = half_pxd;
        var y_offset = half_pxd;
        for(var i = 0; i < num_days; i++){
            var day = new Date((start + i*s_in_day) * 1000).getDate();
            h_context.fillText(day, i * pxd + x_offset, y_offset + pxd);
            h_context.fillText(days[(first_day_s + i) % 7], i * pxd + x_offset, y_offset + 2*pxd);
        }
        
        var task_coords = [];
        //draw the tasks
        for(var i = 0; i < data.length; i++){
            var task = data[i];
            var x_offset = (task.start - start) / 86400 * pxd;
            var width = (task.end - task.start) / 86400 * pxd;
            var height = Math.round(pxd * 0.8);
            var height_offset = Math.round((pxd-height)/2);
            var y_offset = i * pxd + height_offset;
            
            var gradient = context.createLinearGradient(0,y_offset,0,y_offset+height);
            switch(true){
                case task.complete == 1:
                    gradient.addColorStop(0,"#224e22");
                    gradient.addColorStop(1,"#449d44");
                    break;
                case task.is_late:
                    gradient.addColorStop(0,"#641816");
                    gradient.addColorStop(1,"#c9302c");
                    break;
                case task.is_due_soon:
                    gradient.addColorStop(0,"#764b0f");
                    gradient.addColorStop(1,"#ec971f");
                    break;
                default:
                    gradient.addColorStop(0,"#18586a");
                    gradient.addColorStop(1,"#31b0d5");
            }
            this.roundRect(context, x_offset, y_offset, width, height, 4);
            context.fillStyle = gradient;
            context.fill();
            context.strokeStyle = '#ccc';
            context.stroke();
            
            task_coords.push({x1:x_offset,y1:y_offset,x2:x_offset+width,y2:y_offset+height,task:task});
            
            context.save();
            context.clip();
            if(task.percent_complete){
            var gradient = context.createLinearGradient(0,y_offset,0,y_offset+height);
            switch(true){
                case task.complete == 1:
                    gradient.addColorStop(0,"#5cb85c");
                    gradient.addColorStop(1,"#449d44");
                    break;
                case task.is_late:
                    gradient.addColorStop(0,"#d9534f");
                    gradient.addColorStop(1,"#c9302c");
                    break;
                case task.is_due_soon:
                    gradient.addColorStop(0,"#f0ad4e");
                    gradient.addColorStop(1,"#ec971f");
                    break;
                default:
                    gradient.addColorStop(0,"#5bc0de");
                    gradient.addColorStop(1,"#31b0d5");
            }
                this.roundRect(context, x_offset, y_offset, width * (task.percent_complete/100), height, 4, 1, false);
                context.fillStyle = gradient;
                context.fill();
            }
            context.textAlign = 'left';
            var gradient = context.createLinearGradient(x_offset,0,x_offset+width,0);
            gradient.addColorStop(.55,"rgba(255,255,255,1)");
            gradient.addColorStop(1,"rgba(255,255,255,0)");
            context.fillStyle = gradient;
            context.fillText(task.name, x_offset + text_padding, y_offset - height_offset + half_pxd);
            context.restore();
        }
        var in_task = [];
        var task_html = '<div class="bpms-popover-container" data-toggle="popover" data-placement="top"></div>';
        var parent = canvas.parent();
        
        var canvas_id = canvas.attr('id');
        var elem_offset = canvas.offset();
        $(document).off("mousemove.Canvas");
        $(document).off("click.Canvas");
        $(document).on({
        "mousemove.Canvas":function(event){
            for(var i = 0; i < task_coords.length; i++){
                (function(coords){
                var pageX = event.pageX - elem_offset.left + parent.scrollLeft();
                var pageY = event.pageY - elem_offset.top + parent.scrollTop();
                if(pageX >= coords.x1 && pageX <= coords.x2 && pageY >= coords.y1 && pageY <= coords.y2){
                    if(!in_task[i]){
                        if(event.target.id != canvas_id) return false;
                        in_task[i] = $(task_html).css({
                            position:'absolute',
                            left:coords.x1,
                            top:coords.y1,
                            width:coords.x2-coords.x1,
                            height:coords.y2-coords.y1,
                            zIndex:99999
                        });
                        parent.append(in_task[i]);
                        var content = coords.task.name;
                        if(coords.task.collaborators.length) content+=' - ';
                        for(var idx in coords.task.collaborators){
                            var user = coords.task.collaborators[idx];
                            if(idx > 0) content+= ', ';
                            content+='<a href="">'+user.name+'</a>';
                        }
                        in_task[i].popover({
                            content:content,
                            placement:'top',
                            html:true,
                            trigger:'manual',
                            container:'body',
                            animation:false
                        }).popover('show');
                        var med = (coords.x2-coords.x1) / 2 + coords.x1 + elem_offset.left - parent.scrollLeft();
                        var left = Math.round((med - event.pageX) / 4);
                        $('div.popover').css({left:event.pageX - $('div.popover').width()/2 + left});
                    }
                }
                else if(in_task[i]){
                    $(in_task[i]).popover('destroy').remove();
                    in_task[i] = false;
                }
                })(task_coords[i]);
            }
        },
        "click.Canvas":function(event){
            for(var i = 0; i < task_coords.length; i++){
                (function(coords){
                var pageX = event.pageX - elem_offset.left + parent.scrollLeft();
                var pageY = event.pageY - elem_offset.top + parent.scrollTop();
                if(pageX >= coords.x1 && pageX <= coords.x2 && pageY >= coords.y1 && pageY <= coords.y2){
                    if(in_task[i]){
                        window.location.href = '/project/'+coords.task.project_id+'/tasks/view/'+coords.task.id;
                    }
                }
                })(task_coords[i]);
            }
        }
        });
    },
    roundRect: function(ctx, x, y, width, height, radius, fill, stroke) {
        if (typeof stroke == "undefined" ) {
            stroke = true;
        }
        if (typeof radius === "undefined") {
            radius = 5;
        }
        ctx.beginPath();
        ctx.moveTo(x + radius, y);
        ctx.lineTo(x + width - radius, y);
        ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
        ctx.lineTo(x + width, y + height - radius);
        ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
        ctx.lineTo(x + radius, y + height);
        ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
        ctx.lineTo(x, y + radius);
        ctx.quadraticCurveTo(x, y, x + radius, y);
        ctx.closePath();
        if (stroke) {
            ctx.stroke();
        }
        if (fill) {
            ctx.fill();
        }        
    }
};