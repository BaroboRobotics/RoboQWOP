#include "mobot.h"
#include "server.h"
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
#include <mysql.h>

using namespace std;

#define MAX_PENDING 5 /* Maximum number of pending connection requests */
#define ROBOTS 4
#define RECV_BUFFER_SIZE 256
#define CMD_MV_CONT 0
#define CMD_RESET 1
#define DB_HOST "localhost"
#define DB_USER "user"
#define DB_PASSWORD "changeme01"
#define DB_NAME "barobo"

CMobot mobot[ROBOTS];
char *addresses[ROBOTS];
int max_mobot_index = 0;
double last_speed = -1.0;

int main(int arc, char **argv) {
	int server_sock, client_sock;
	unsigned short server_port = 8082;
	unsigned int client_len;
	struct sockaddr_in server_addr, client_addr;

	init_mobots();

	server_sock = socket(AF_INET, SOCK_STREAM, 0);
	if (server_sock < 0) {
		error("ERROR opening socket");
	}

	memset(&server_addr, 0, sizeof(server_addr));
	server_addr.sin_family = AF_INET;
	server_addr.sin_addr.s_addr = htonl(INADDR_ANY);
	server_addr.sin_port = htons(server_port);

	if (bind(server_sock, (struct sockaddr *) &server_addr, sizeof(server_addr)) < 0) {
		error("ERROR binding to the local address.");
	}

	if (listen(server_sock, MAX_PENDING) < 0) {
		error("ERROR attempting to listening for incoming connections.");
	}
	while (1) {
		client_len = sizeof(client_addr);
		printf("Waiting to receive a message\n");
		if ((client_sock = accept(server_sock, (struct sockaddr *) &client_addr, &client_len)) < 0) {
			error("ERROR error occurred waiting for a client to connect");
		}

		printf("Message received from client %s\n", inet_ntoa(client_addr.sin_addr));
		handle_message(client_sock);
	}
	return 0;
}

void init_mobots() {
	int index;
	MYSQL *conn;
	MYSQL_RES *result;
	MYSQL_ROW row;

	conn = mysql_init(NULL);
	mysql_real_connect(conn, DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, 0, NULL, 0);
	// Query the number and address information from the robots table.
	mysql_query(conn, "SELECT number, address FROM robots");
	result = mysql_store_result(conn);
	for (index = 0; index < ROBOTS; index++) {
		addresses[index] = NULL;
	}
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
				error("ERROR connecting to the Mobot.");
			}
			printf("Connected successfully\n");
			mobot[index].moveToZero();
			max_mobot_index = index;
		}
	}
}

void error(const char *msg) {
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
	int cmd_type = CMD_MV_CONT;
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
			cmd_type = atoi(val);
			break;
		default:
			values[i - 2] = atof(val);
		}
		i++;
		val = strtok (NULL, " ,");
	}
	printf("Commands [%i, %i, %lf, %lf, %lf, %lf, %lf, %lf]\n",
			mobot_num, cmd_type,
			values[0], values[1], values[2], values[3], values[4], values[5]);
	if (mobot_num > max_mobot_index) {
		printf("invalid Mobot number reference");
		return 0;
	}
	while (!mobot[mobot_num].isConnected()) {
		printf("Lost connection to the Mobot, attempting to re-connect.\n");
		mobot[mobot_num].disconnect();
		usleep(50000);
		mobot[mobot_num].connectWithAddress(addresses[mobot_num], 1);
	}
	switch (cmd_type) {
	case CMD_MV_CONT:
		if (last_speed != values[4]) {
			last_speed = values[4];
			mobot[mobot_num].setJointSpeeds(values[4], values[4], values[4], values[4]);
		}
		mobot[mobot_num].moveContinuousNB(get_state(values[0]),
				get_state(values[3]),
				get_state(values[2]),
				get_state(values[1]));
		break;
	case CMD_RESET:
		mobot[mobot_num].moveToZero();
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
		error("ERROR retrieving message");
	}
	if (msg_size > 0) {
		process_command(buffer, msg_size);
	}
	while (msg_size > 0) {
		if ((msg_size = recv(client_sock, buffer, RECV_BUFFER_SIZE, 0)) < 0) {
			error("ERROR retrieving message");
		}
		if (msg_size > 0) {
			process_command(buffer, msg_size);
		}
	}
	close(client_sock);
	return 0;
}
