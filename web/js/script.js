/* Author:

*/
function delete_user_from_queue(user_id) {
    $.post('delete_user_from_queue.php', 'user_id_to_delete='+user_id);
}

var RoboQWOP = {};

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

RoboQWOP.robomancer = {};

RoboQWOP.robomancer.init = function() {
	function initSlider(sliderId, valueId) {
		$(sliderId ).slider({
	        orientation: "vertical",
	        min: -180,
	        max: 180,
	        value: 0,
	        slide: function(event, ui ) {
	            $(valueId ).val( ui.value );
	        }
	    });
		$(valueId ).val( $( sliderId ).slider( "value" ) );
	}
	initSlider("#mancer-joint-1", "#mancer-joint-val-1");
	initSlider("#mancer-joint-2", "#mancer-joint-val-2");
	initSlider("#mancer-joint-3", "#mancer-joint-val-3");
	initSlider("#mancer-joint-4", "#mancer-joint-val-4");
}
