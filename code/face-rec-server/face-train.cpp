/*
 * Copyright (c) 2011. Philipp Wagner <bytefish[at]gmx[dot]de>.
 * Released to public domain under terms of the BSD Simplified license.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in the
 *     documentation and/or other materials provided with the distribution.
 *   * Neither the name of the organization nor the names of its contributors
 *     may be used to endorse or promote products derived from this software
 *     without specific prior written permission.
 *
 *   See <http://www.opensource.org/licenses/bsd-license>
 *
 */
#include "opencv2/core/core.hpp"
#include "opencv2/highgui/highgui.hpp"
#include "opencv2/contrib/contrib.hpp"

#include <iostream>
#include <fstream>
#include <sstream>
#include <vector>
#include <iostream>
#include <algorithm>
#include <string>

#include <stdio.h>
#include <stdlib.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <unistd.h>
#include <errno.h>
#include <dirent.h>


using namespace cv;
using namespace std;

string NumberToString(int);
vector<string> getFiles(string);
vector<int> tokenise(string);

vector<int> tokenise(string string)
{
    char str[1000];
    strcpy(str, string.c_str());
    char *token;
    vector<int> retval;
    token = strtok(str, ",");
    while(token != NULL) {
        retval.push_back(atoi(token));
        token = strtok(NULL, ",");
    }
    return retval;
}

string NumberToString(int Number)
{
    ostringstream ss;
    ss << Number;
    return ss.str();
}

vector<string> getFiles(string path)
{
    DIR *dirp = opendir(path.c_str());
    struct dirent *direntp;
    char msg[256];
    string newPath;
    vector<string> retval;
    if (dirp == NULL) {
        perror(path.c_str());
    } else {
        while ((direntp = readdir(dirp)) != NULL) {
            newPath = path + "/" + direntp->d_name;
            struct stat fileInfo;
            int status = stat(newPath.c_str(), &fileInfo);
            if (status < 0) {
                sprintf(msg, "Cannot stat file '%s'", newPath.c_str());
                perror(msg);
            } else {
                if (! S_ISDIR(fileInfo.st_mode)) {
                    retval.push_back(direntp->d_name);
                }
            }
        }
        closedir(dirp);
    }
    return retval;
}

int main(int argc, const char *argv[])
{
    // Check for valid command line arguments, print usage
    // if no arguments were given.
    if (argc != 3) {
        cout << "usage: " << argv[0] << " group_id 'list of students'" << endl;
        exit(1);
    }
    string gid = string(argv[1]);
    string students = string(argv[2]);

    // These vectors hold the images and corresponding labels.
    vector<Mat> images;
    vector<int> labels;
    vector<int> dirs = tokenise(students);

    for (std::vector<int>::iterator it = dirs.begin(); it != dirs.end(); ++it) {
        vector<string> files = getFiles("faces/" + NumberToString(*it));
        for (std::vector<string>::iterator jt = files.begin(); jt != files.end(); ++jt) {
            images.push_back(imread("faces/" + NumberToString(*it) + "/" + *jt, CV_LOAD_IMAGE_GRAYSCALE));
            labels.push_back(*it);
        }
    }

    // Quit if there are not enough images for this demo.
    if (images.size() <= 1) {
        cout << "Needs at least 2 images to work. Please add more images to your data set!";
        exit(1);
    }

    Ptr<FaceRecognizer> model = createEigenFaceRecognizer();
    model->train(images, labels);
    model->save("models/" + gid + ".xml");
    return 0;
}