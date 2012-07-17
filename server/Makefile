CC=g++
INCLUDES=-I.
CFLAGS = -Wall -g
DEPS= mobot.h
OBJ = server.o
LIBS = -lmobot

%.o: %.cpp $(DEPS)
	$(CC) -c -o $@ $< $(CFLAGS)

mobot_server: $(OBJ)
	$(CC) $(CFLAGS) $(INCLUDES) -o $@ $^ $(LIBS)
clean:
	rm *.o mobot_server

