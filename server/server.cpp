#include "mobot.h"
#include <iostream>
#include <fstream>
#include <stdio.h>
#include <string.h>
#include <sstream>
#include <cstdlib>

using namespace std;

CMobot mobot;
double values[1000][5];

inline double convertToDouble(string const &s) {
	istringstream i(s);
	double x;
	if (!(i >> x)) {
		return 0;
	}
	return x;
}

inline mobotJointState_t getState(double val) {
	if (val > 0) {
		return MOBOT_FORWARD;
	} else if (val == 0) {
		return MOBOT_HOLD;
	}
	return MOBOT_BACKWARD;	
}

void *handle_disconnect(void *id) {
	double angle = 0.0;
	while (true) {
		if (mobot.getJointAngle(MOBOT_JOINT1, angle) != 0) {
			printf("Connection error, attempting to disconnect.\n");
			while (mobot.disconnect() != 0) {
				printf("Disconnection failed, attempting again.\n");
				usleep(1000);
			}
			printf("Attempting to connect to the Mobot.\n");
			while (mobot.connect() != 0) {
				printf("Connection failed, attempting to reconnect.\n");
				usleep(1000);
			}
			printf("Connected successfully, resuming operation\n");
		}
		usleep(4000);
	}
	return (void *) 0;
}

int main() {
	//int threadRef;
	//int id;
	//pthread_t thread;
	double last_speed = -1.0;
	if (mobot.connect() != 0) {
		cout << "Error connecting to the mobot" << endl;
		exit(-1);
	
	}
	printf("Connected successfully\n");
	mobot.moveToZero();
	//threadRef = pthread_create(&thread, NULL, handle_disconnect, &id);
	while (1) {
		if (!mobot.isConnected()) continue;
		int data_len = 0;
		string line;
		ifstream input_file ("/tmp/mobot_movement.data");
		if (input_file.good()) {
			while(getline(input_file, line)) {
				stringstream strm(line);
				string cell;
				int i = 0;
				while (getline(strm, cell, ',')) {
					values[data_len][i++] = convertToDouble(cell);
					printf("%lf ", convertToDouble(cell)); 		
				}
				data_len++;
				printf("\n");
			}
			if (data_len > 0 && remove("/tmp/mobot_movement.data")) {
				perror("Error deleting file\n");
			}
		}
		for (int pos = 0; pos < data_len; pos++) {
			if (last_speed != values[pos][4]) {
				last_speed = values[pos][4];
				mobot.setJointSpeeds(values[pos][4], values[pos][4], values[pos][4], values[pos][4]);
			}
			mobot.moveContinuousNB(getState(values[pos][0]),
					       getState(values[pos][3]),
					       getState(values[pos][2]),
					       getState(values[pos][1]));
			//printf("value fp1 %i\n", getState(values[pos][0]));
			//mobot.moveJointContinuousNB(MOBOT_JOINT1, getState(values[pos][0]));
                        //mobot.moveJointContinuousNB(MOBOT_JOINT2, getState(values[pos][2]));
                        //mobot.moveJointContinuousNB(MOBOT_JOINT3, getState(values[pos][3]));
                        //mobot.moveJointContinuousNB(MOBOT_JOINT4, getState(values[pos][1]));
		}
		while (!mobot.isConnected()) {
                        printf("Attempting to re-connect\n");
                        if (mobot.connect() != 0) {
                                printf("Connection failed.\n");
                        };
                        usleep(2000);
                }
		usleep(500);
		
	}
	// pthread_exit(&threadRef);	
	return 0;
}
