#include <stdio.h>
#include <errno.h>
#include <unistd.h>
#include <sys/types.h>
#include <sys/wait.h>

int main(int argc, char *argv[]) {
    // Check for valid command line arguments, print usage
    // if no arguments were given.
    if (argc < 3) {
        printf("Usage: %s gid image.jpg [image2.jpg ...]\n", argv[0]);
        return 1;
    }
    if (atoi(argv[1]) < 1) {
        printf("Invalid group %s\n", argv[1]);
        return 1;
    }

    // Fork as many processes as
    // there are images to recognise
    int numImages = argc - 2;
    int i = 0;
    pid_t pid;
    for (;i<numImages;i++) {
    	pid = fork();
    	if (pid < 0) {
	        perror("couldn't fork");
	        return 1;
    	} else if (pid == 0) {
    		// child process
    		break;
    	}
    }

    if (pid == 0) {
    	// Child process
    	// Do the actual recognition
		execl("./face-rec", "./face-rec", argv[1], argv[i+2], (char *) 0);
    } else {
    	// Parent process
    	// Wait for children to exit
    	while (1) {
    		int status;
    		pid = wait(&status);
    		if (pid > 0) {
    			if (! WIFEXITED(status) || WEXITSTATUS(status) > 0) {
    				return 1;
				}
    		} else {
    			break;
    		}
    	}
    }

	return 0;
}