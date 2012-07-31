#ifndef _SERVER_H_
#define _SERVER_H_

#include <mysql.h>

typedef enum {
	CMD_MOVE_CONTINUOUS = 0,
	CMD_MOVE_TO_ZERO
} command_t;

/**
 * Prints out the error and exits the program.
 */
void error_and_exit(const char *msg);
/**
 * Initializes the mobots from the database table.
 */
void init_mobots();
/**
 * Worker thread that attempts to keep the mobot connected.
 */
void *keep_connected(void *id_val);
/**
 * Worker thread that handles communication.
 */
void *comm_thread(void *robot_id_val);
/**
 * Worker thread that manages the queue.
 */
void *queue_thread(void *id_val);
/**
 * Takes a value and converts it to a joint state.
 */
mobotJointState_t get_state(double val);
/**
 * Processes commands to the mobots.
 */
int process_command(char *commands, int length);
/**
 * Helper method to handle the communication messages.
 */
int handle_message(int client_sock);
/**
 * Performs maintenance on the queue and controller tables.
 */
int update_queue(MYSQL *conn);
#endif
