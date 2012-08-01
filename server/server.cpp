#include "mobot.h"
#include "server.h"
#include <pthread.h>
#include <iostream>
#include <fstream>
#include <stdio.h>
#include <string.h>
#include <sstream>
#include <cstdlib>
#include <unistd.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <arpa/inet.h>
#include <my_global.h>

using namespace std;

#define MAX_PENDING 5 /* Maximum number of pending connection requests */
#define ROBOTS 4
#define RECV_BUFFER_SIZE 256
#define DB_HOST "localhost"
#define DB_USER "user"
#define DB_PASSWORD "changeme01"
#define DB_NAME "barobo"

CMobot mobot[ROBOTS];
char *addresses[ROBOTS] = { NULL };
int max_mobot_index = 0;
double last_speed[ROBOTS];
int num_seconds[ROBOTS] = { 0 };

int main(int arc, char **argv) {
	pthread_t threads[ROBOTS];
	int *robot_ids[ROBOTS];
	pthread_t db_thread, alive_thread;
	int i;
	void *status;

	init_mobots();

	if (pthread_create(&db_thread, NULL, queue_thread, (void *) NULL) != 0) {
		error_and_exit("ERROR unable to create DB thread.");
	}
	if (pthread_create(&alive_thread, NULL, keep_connected, (void *) NULL) != 0) {
		error_and_exit("ERROR unable to start the keep alive thread.");
	}
	for (i = 0; i <= max_mobot_index; i++) {
		robot_ids[i] = (int *) malloc(sizeof(int));
		*robot_ids[i] = i;
		if (pthread_create(&threads[i], NULL, comm_thread, (void *) robot_ids[i]) != 0) {
			error_and_exit("ERROR unable to create thread.");
		}
	}
	for (i = 0; i <= max_mobot_index; i++) {
		if (pthread_join(threads[i], &status) != 0) {
			error_and_exit("ERROR attempting to join thread.");
		}
	}

	return 0;
}

void *comm_thread(void *robot_id_val) {
	int server_sock, client_sock;
	unsigned short server_port = 8082;
	unsigned int client_len;
	struct sockaddr_in server_addr, client_addr;
	int *robot_id_ptr, robot_id;
	robot_id_ptr = (int *) robot_id_val;
	robot_id = *robot_id_ptr;

	server_sock = socket(AF_INET, SOCK_STREAM, 0);
	if (server_sock < 0) {
		error_and_exit("ERROR opening socket");
	}

	memset(&server_addr, 0, sizeof(server_addr));
	server_addr.sin_family = AF_INET;
	server_addr.sin_addr.s_addr = htonl(INADDR_ANY);
	server_addr.sin_port = htons(server_port + robot_id);

	if (bind(server_sock, (struct sockaddr *) &server_addr, sizeof(server_addr)) < 0) {
		error_and_exit("ERROR binding to the local address.");
	}

	if (listen(server_sock, MAX_PENDING) < 0) {
		error_and_exit("ERROR attempting to listening for incoming connections.");
	}
	while (1) {
		client_len = sizeof(client_addr);
		printf("Waiting to receive a message\n");
		if ((client_sock = accept(server_sock, (struct sockaddr *) &client_addr, &client_len)) < 0) {
			error_and_exit("ERROR error occurred waiting for a client to connect");
		}

		printf("Message received from client %s\n", inet_ntoa(client_addr.sin_addr));
		handle_message(client_sock);
	}
	pthread_exit(NULL);
}

void *keep_connected(void *id_val) {
	int robots;
	while (1) {
		for (robots = 0; robots <= max_mobot_index; robots++) {
			while (!mobot[robots].isConnected()) {
				printf("Lost connection to the Mobot, attempting to re-connect.\n");
				mobot[robots].disconnect();
				mobot[robots].connectWithAddress(addresses[robots], 1);
			}
			if (num_seconds[robots] >= 300) {
				num_seconds[robots] = 0;
				printf("Relaxing Mobot due to in-activity.\n");
				mobot[robots].moveContinuousNB(MOBOT_NEUTRAL, MOBOT_NEUTRAL, MOBOT_NEUTRAL, MOBOT_NEUTRAL);
			}
			num_seconds[robots]++;
		}
		sleep(1);
	}
	pthread_exit(NULL);
}

void *queue_thread(void *id_val) {
	MYSQL *conn;
	while(1) {
		conn = mysql_init(NULL);
		if (mysql_real_connect(conn, DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, 0, NULL, 0)) {
			mysql_query(conn,"START TRANSACTION");
			if (update_queue(conn) == 0) {
				mysql_query(conn,"COMMIT");
			} else {
				mysql_query(conn,"ROLLBACK");
			}
			mysql_close(conn);
		} else {
			perror(mysql_error(conn));
		}
		sleep(1);
	}
	pthread_exit(NULL);
}

int update_queue(MYSQL *conn) {
	MYSQL_RES *result;
	MYSQL_ROW row;
	char buffer[256];
	int id, user_id, robot_number, count, control_time, queue_id;
	mysql_query(conn, "SELECT id, user_id, robot_number "
			"FROM controllers where created < (NOW() - INTERVAL control_time SECOND)");
	result = mysql_store_result(conn);
	while ((row = mysql_fetch_row(result))) {
		id = atoi(row[0]);
		user_id = atoi(row[1]);
		robot_number = atoi(row[2]);
		// Remove controller record.
		sprintf(buffer, "DELETE FROM controllers where id = %i", id);
		if (mysql_query(conn, buffer) != 0) {
			perror(mysql_error(conn));
			return 1;
		}
		// Add the controller record back on the queue.
		sprintf(buffer, "INSERT INTO queue (created, last_active, user_id, robot_number) "
                "values (CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, %i, %i)", user_id, robot_number);
		if (mysql_query(conn, buffer) != 0) {
			perror(mysql_error(conn));
			return 1;
		}
	}
	mysql_free_result(result);
	// Remove all queue entries that are no longer active.
	count = 1;
	if (mysql_query(conn, "DELETE FROM queue WHERE last_active  < (NOW() - INTERVAL 30 SECOND)") != 0) {
		perror(mysql_error(conn));
		return 1;
	}
	for (robot_number = 0; robot_number <= max_mobot_index; robot_number++) {
		// Find number of controllers for the current robot number.
		sprintf(buffer, "SELECT count(*) from controllers where robot_number = %i", robot_number);
		if (mysql_query(conn, buffer) != 0) {
			perror(mysql_error(conn));
			return 1;
		}
		result = mysql_store_result(conn);
		row = mysql_fetch_row(result);
		count = atoi(row[0]);
		mysql_free_result(result);
		if (count == 0) {
			sprintf(buffer, "SELECT q.id, q.user_id, u.control_time FROM queue q "
                "INNER JOIN users as u on u.id = q.user_id WHERE q.robot_number = %i "
				"ORDER BY q.created asc LIMIT 1", robot_number);
			if (mysql_query(conn, buffer) != 0) {
				perror(mysql_error(conn));
				return 1;
			}
			result = mysql_store_result(conn);
			while ((row = mysql_fetch_row(result))) {
				queue_id = atoi(row[0]);
				user_id = atoi(row[1]);
				control_time = atoi(row[2]);
				sprintf(buffer, "INSERT INTO controllers (created, control_time, user_id, robot_number) "
                    "values (CURRENT_TIMESTAMP, %i, %i, %i)", control_time, user_id, robot_number);
				if (mysql_query(conn, buffer) != 0) {
					perror(mysql_error(conn));
					return 1;
				}
				sprintf(buffer, "DELETE FROM queue where id = %i", queue_id);
				if (mysql_query(conn, buffer) != 0) {
					perror(mysql_error(conn));
					return 1;
				}
			}
			mysql_free_result(result);
		}
	}
	return 0;
}

void init_mobots() {
	int index;
	MYSQL *conn;
	MYSQL_RES *result;
	MYSQL_ROW row;

	conn = mysql_init(NULL);
	if (!mysql_real_connect(conn, DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, 0, NULL, 0)) {
		error_and_exit(mysql_error(conn));
	}
	// Query the number and address information from the robots table.
	mysql_query(conn, "SELECT number, address FROM robots");
	result = mysql_store_result(conn);

	// Retrieve the address information.
	while ((row = mysql_fetch_row(result))) {
		index = atoi(row[0]);
		addresses[index] = row[1];
	}
	// Close the mysql connection.
	mysql_free_result(result);
	mysql_close(conn);
	for (index = 0; index < ROBOTS; index++) {
		if (addresses[index] != NULL) {
			printf("Attempting to connect at address %s\n", addresses[index]);
			if (mobot[index].connectWithAddress(addresses[index], 1) != 0) {
				error_and_exit("ERROR connecting to the Mobot.");
			}
			printf("Connected successfully\n");
			last_speed[index] = -1;
			mobot[index].moveToZero();
			max_mobot_index = index;
		}
	}
}

void error_and_exit(const char *msg) {
	perror(msg);
	exit(1);
}

mobotJointState_t get_state(double val) {
	if (val > 0.0) {
		return MOBOT_FORWARD;
	} else if (val == 0.0) {
		return MOBOT_HOLD;
	}
	return MOBOT_BACKWARD;
}

/**
 * Processes the commands received and sends them to the Mobot.
 */
int process_command(char *commands, int length) {
	char *val;
	int mobot_num = 0;
	command_t cmd_type = CMD_MOVE_CONTINUOUS;
	double values[6] = { 0.0 };
	int i = 0;
	if (length <= 0) {
		return 0;
	}
	val = strtok(commands, " ,");
	while (val != NULL && i < 8) {
		switch (i) {
		case 0:
			mobot_num = atoi(val);
			break;
		case 1:
			cmd_type = (command_t) atoi(val);
			break;
		default:
			values[i - 2] = atof(val);
		}
		i++;
		val = strtok (NULL, " ,");
	}
	printf("Commands [%i, %i, %lf, %lf, %lf, %lf, %lf, %lf]\n", mobot_num, cmd_type,
			values[0], values[1], values[2], values[3], values[4], values[5]);
	if (mobot_num > max_mobot_index) {
		printf("invalid Mobot number reference");
		return 1;
	}
	num_seconds[mobot_num] = 0;
	if (!mobot[mobot_num].isConnected()) {
		printf("Mobot is not connected, not processing command.\n");
		return 1;
	}
	switch (cmd_type) {
	case CMD_MOVE_CONTINUOUS:
		if (last_speed[mobot_num] != values[4]) {
			last_speed[mobot_num] = values[4];
			mobot[mobot_num].setJointSpeeds(values[4], values[4], values[4], values[4]);
		}
		mobot[mobot_num].moveContinuousNB(get_state(values[0]),
				get_state(values[3]),
				get_state(values[2]),
				get_state(values[1]));
		break;
	case CMD_MOVE_TO_ZERO:
		mobot[mobot_num].moveJointTo(MOBOT_JOINT2, 0);
		mobot[mobot_num].moveJointTo(MOBOT_JOINT3, 0);
		// mobot[mobot_num].moveToZero();
		break;
	default:
		printf("Unknown command\n");
	}
	return 0;
}

int handle_message(int client_sock) {
	char buffer[RECV_BUFFER_SIZE];
	int msg_size;

	if ((msg_size = recv(client_sock, buffer, RECV_BUFFER_SIZE, 0)) < 0) {
		error_and_exit("ERROR retrieving message");
	}
	if (msg_size > 0) {
		process_command(buffer, msg_size);
	}
	while (msg_size > 0) {
		if ((msg_size = recv(client_sock, buffer, RECV_BUFFER_SIZE, 0)) < 0) {
			error_and_exit("ERROR retrieving message");
		}
		if (msg_size > 0) {
			process_command(buffer, msg_size);
		}
	}
	close(client_sock);
	return 0;
}
