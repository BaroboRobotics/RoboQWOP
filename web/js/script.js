/* Author:

*/


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
        body[q.robot_number] += '</td></tr>';
        pos[q.robot_number]++;
    });
	var html = "";
	for (var i = 0; i < cnt; i++) {
		html += header[i] + body[i] + footer[i];
	}
	return html;
}


