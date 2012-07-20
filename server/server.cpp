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

int main() {
	double last_speed = -1.0;
	string line;
	if (mobot.connect() != 0) {
		cout << "Error connecting to the mobot" << endl;
		exit(-1);
	}
	printf("Connected successfully\n");
	mobot.moveToZero();
	while (1) {
		while (!mobot.isConnected()) {
			printf("Lost connection to the Mobot, attempting to re-connect.\n");
			mobot.disconnect();
			mobot.connect();
			usleep(100000);
		}
		int data_len = 0;
		// TODO change the input from file input to mysql database input.
		ifstream input_file("/tmp/mobot_movement.data");
		if (input_file.good()) {
			while (getline(input_file, line)) {
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
				mobot.setJointSpeeds(values[pos][4], values[pos][4],
						values[pos][4], values[pos][4]);
			}
			mobot.moveContinuousNB(getState(values[pos][0]),
					getState(values[pos][3]), getState(values[pos][2]),
					getState(values[pos][1]));
		}
	}
	return 0;
}
