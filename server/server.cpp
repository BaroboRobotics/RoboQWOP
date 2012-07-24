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

using namespace std;

#define MAX_PENDING 5 /* Maximum number of pending connection requests */
#define RECV_BUFFER_SIZE 256
#define CMD_MV_CONT 0


CMobot mobot[4];
double last_speed = -1.0;

int main(int arc, char **argv) {
	int server_sock, client_sock;
	unsigned short server_port = 8082;
	unsigned int client_len;
	struct sockaddr_in server_addr, client_addr;
	if (mobot[0].connect() != 0) {
		error("ERROR connecting to the Mobot.");
	}
	printf("Connected successfully\n");
	mobot[0].moveToZero();

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
	printf("Commands: %s\n", commands);
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
	printf("Parsed commands [%i, %i, %lf, %lf, %lf, %lf, %lf, %lf]\n",
			mobot_num, cmd_type,
			values[0], values[1], values[2], values[3], values[4], values[5]);
	while (!mobot[mobot_num].isConnected()) {
		printf("Lost connection to the Mobot, attempting to re-connect.\n");
		mobot[mobot_num].disconnect();
		mobot[mobot_num].connect();
		usleep(100000);
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
