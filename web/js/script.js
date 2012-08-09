/* Author:

*/
function delete_user_from_queue(user_id) {
    $.post('delete_user_from_queue.php', 'user_id_to_delete='+user_id);
}

var RoboQWOP = {};
RoboQWOP.compareAndSet = function(new_value, value) {
	if (new_value != value) {
		value = new_value;
		return true;
	}
	return false;
}
RoboQWOP.processQueue = function(json) {
	
	var header = [];
	var footer = [];
	var body = [];
	var pos = [];
	var cnt = 0;
	$.each(json.robots, function(idx1, robot) {
		header[robot.number] = '<table><thead><tr>';
		header[robot.number] += '<th colspan="2"><a href="authenticate.php?robot=' + robot.number + '">' + robot.name + '</a></th>';
		header[robot.number] += '</tr></thead><tbody>';
		body[robot.number] = '';
		footer[robot.number] = '</tbody></table>';
		pos[robot.number] = 1;
		cnt++;
	});
	$.each(json.controllers, function(index, controller) {
		body[controller.robot_number] = '<tr><td>' + pos[controller.robot_number]  + '</td><td>';
        body[controller.robot_number] += controller.first_name + ' ' + controller.last_name;
		if (is_admin) {
		    body[controller.robot_number] += ' <input type="button" value="X" onclick="delete_user_from_queue(' + controller.user_id + ')" />';
		}
        body[controller.robot_number] += '<br/>(' + controller.time_left + ' seconds left )'; 
        body[controller.robot_number] += '</td></tr>';
        pos[controller.robot_number]++;
    });
	$.each(json.queue, function(index, q) {
        body[q.robot_number] += '<tr><td>' + pos[q.robot_number] + '</td><td>';
        body[q.robot_number] += q.first_name + ' ' + q.last_name;
		if (is_admin) {
		    body[q.robot_number] += ' <input type="button" value="X" onclick="delete_user_from_queue(' + q.user_id + ')" />';
		}
        body[q.robot_number] += '</td></tr>';
        pos[q.robot_number]++;
    });
	var html = "";
	for (var i = 0; i < cnt; i++) {
		html += header[i] + body[i] + footer[i];
	}
	return html;
}
RoboQWOP.changeSpeed = function(event, ui) {
	$.ajax({
        type : 'GET',
        url : 'action.php',
        data : {"mode":3, "speed":ui.value },
        dataType : 'json'
    });
	$('.speed-slider').slider( "option", "value", ui.value );
}
/* Default Tab Function and Events */
RoboQWOP.qwop = function() {
		var q = 0; var w = 0;
		var o = 0; var p = 0;
        var u = 0; var i = 0; 
        var e = 0; var r = 0;
}
RoboQWOP.qwop.data = function() {
		return {"mode": 2,
		"q" : q, "w" : w, "e" : e, "r" : r,
		"u" : u, "i" : i, "o" : o, "p" : p} 
}
RoboQWOP.qwop.event =  function(keyCode, down) {
	var send = false;
	var temp = (down) ? 1 : 0;
    switch (keyCode) {
        case 81: // q
        	if (temp != q) {
	    		q = temp; send = true;
	    	}
            break;
        case 87: // w
        	if (temp != w) {
	    		w = temp; send = true;
	    	}
            break;
        case 69: // e
        	if (temp != e) {
	    		e = temp; send = true;
	    	}
            break;
        case 82: // r
        	if (temp != r) {
	    		r = temp; send = true;
	    	}
            break;
        case 85: // u
        	if (temp != u) {
	    		u = temp; send = true;
	    	}
            break;
        case 73: // i
        	if (temp != i) {
	    		i = temp; send = true;
	    	}
            break;
        case 79: // o
        	if (temp != o) {
	    		o = temp; send = true;
	    	}
            break;
        case 80: // p
        	if (temp != p) {
	    		p = temp; send = true;
	    	}
            break;  
    }
    return send;
}
RoboQWOP.qwop.clear = function() {
	q = 0; w = 0;
	o = 0; p = 0;
    u = 0; i = 0; 
    e = 0; r = 0;
}
RoboQWOP.qwop.debug = function() {
	console.log(q + ', ' + w);
}
RoboQWOP.qwop.init = function() {
	RoboQWOP.qwop.clear();
	$('#default-slider').slider({
        "max" : 120,
        "min" : 15,
        "value" : 120,
        stop: function(event, ui ) {
        	RoboQWOP.changeSpeed(event, ui)
        }
    });
}

/* Robo Mancer Functions and Events */
RoboQWOP.robomancer = function() {
	var up = 0;
	var down = 0;
	var left = 0;
	var right = 0;
}
RoboQWOP.robomancer.init = function() {
	RoboQWOP.robomancer.clear();
	function initSlider(sliderId, valueId) {
		$(sliderId ).slider({
	        orientation: "vertical",
	        min: -90,
	        max: 90,
	        value: 0,
	        slide: function(event, ui) {
	            $(valueId ).val( ui.value );
	        },
	        stop: function(event, ui) {
	        	RoboQWOP.robomancer.moveJoints();
	        }
	    });
		$(valueId ).val( $( sliderId ).slider( "value" ) );
	}
	initSlider("#mancer-joint-1", "#mancer-joint-val-1");
	initSlider("#mancer-joint-2", "#mancer-joint-val-2");
	initSlider("#mancer-joint-3", "#mancer-joint-val-3");
	initSlider("#mancer-joint-4", "#mancer-joint-val-4");
	$('#mancer-speed').slider({
        "max" : 120,
        "min" : 15,
        "value" : 120,
        stop: function(event, ui ) {
        	RoboQWOP.changeSpeed(event, ui)
        }
    });
}
RoboQWOP.robomancer.updateSliders = function(array) {
	$('#mancer-joint-val-1').val(array[0]);
	$('#mancer-joint-val-2').val(array[1]);
	$('#mancer-joint-val-3').val(array[2]);
	$('#mancer-joint-val-4').val(array[3]);
	$("#mancer-joint-1").slider( "option", "value", array[0] );
	$("#mancer-joint-2").slider( "option", "value", array[1] );
	$("#mancer-joint-3").slider( "option", "value", array[2] );
	$("#mancer-joint-4").slider( "option", "value", array[3] );
	$('.speed-slider').slider( "option", "value", array[4] );
}
RoboQWOP.robomancer.reset = function() {
	$.ajax({
        type : 'GET',
        url : 'action.php',
        data : {"mode":1},
        dataType : 'json'
    });
}
RoboQWOP.robomancer.moveJoints = function() {
	$.ajax({
        type : 'GET',
        url : 'action.php',
        data : {"mode":4, 
        	"j1":$("#mancer-joint-1").slider("option", "value"),
        	"j2":$("#mancer-joint-2").slider("option", "value"),
        	"j3":$("#mancer-joint-3").slider("option", "value"),
        	"j4":$("#mancer-joint-4").slider("option", "value")
        	},
        dataType : 'json'
    });
}
RoboQWOP.robomancer.doMotion = function(id) {
	$.ajax({
        type : 'GET',
        url : 'action.php',
        data : {"mode":6, "action":$('#' + id).val() },
        dataType : 'json'
    });
}
RoboQWOP.robomancer.doDirection = function(up, down, left, right) {
	var upVal = (up) ? 1 : 0;
	var downVal = (down) ? 1 : 0;
	var leftVal = (left) ? 1 : 0;
	var rightVal = (right) ? 1 : 0;
	$.ajax({
        type : 'GET',
        url : 'action.php',
        data : {"mode":5, "up":upVal, "down":downVal, "left":leftVal, "right":rightVal },
        dataType : 'json'
    });
}
RoboQWOP.robomancer.data = function() {
	return {"mode": 5, "up" : up, "down" : down, "left" : left, "right" : right} 
}
RoboQWOP.robomancer.event =  function(keyCode, on) {
	var send = false;
	var temp = (on) ? 1 : 0;
	switch (keyCode) {
	    case 38: // up
	    	if (temp != up) {
	    		up = temp; send = true;
	    	}
	        break;
	    case 40: // down
	    	if (temp != down) {
	    		down = temp; send = true;
	    	}
	        break;
	    case 37: // left
	    	if (temp != left) {
	    		left = temp; send = true;
	    	}
	        break;
	    case 39: // right
	    	if (temp != right) {
	    		right = temp; send = true;
	    	}
	        break;  
	}
	return send;
}
RoboQWOP.robomancer.clear = function() {
	up = 0; down = 0; left = 0; right = 0;
}
/* Oriented Control Functions and Events */
RoboQWOP.oriented = {};
RoboQWOP.oriented.init = function() {
	$('#oriented-slider').slider({
        "max" : 120,
        "min" : 15,
        "value" : 120,
        stop: function(event, ui ) {
        	RoboQWOP.changeSpeed(event, ui)
        }
    });
	$('#oriented-controls').mouseup(function(event) {
        q = 0;
        w = 0;
        o = 0;
        p = 0;
        u = 0;
        i = 0;
        e = 0;
        r = 0;
        send = true;
    });
}
