CC=g++
INCLUDES=-I. ${shell mysql_config --include}
CFLAGS = -Wall -g
DEPS= mobot.h server.h
OBJ = server.o
LIBS = -lmobot ${shell mysql_config --libs}

%.o: %.cpp $(DEPS)
	$(CC) -c -o $@ $< $(CFLAGS) $(INCLUDES)

mobot_server: $(OBJ)
	$(CC) $(CFLAGS) $(INCLUDES) -o $@ $^ $(LIBS)
clean:
	rm *.o mobot_server

