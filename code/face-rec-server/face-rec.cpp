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

#define DEBUG 1;

#include <stdio.h>
#include <stdlib.h>
#include <string.h>

using namespace cv;
using namespace std;

int main(int argc, const char *argv[])
{
    // Check for valid command line arguments, print usage
    // if no arguments were given.
    if (argc != 2) {
        cout << "usage: " << argv[0] << " image.png" << endl;
        exit(1);
    }
    string image_file = string(argv[1]);

    Mat testSample;
    testSample = imread(image_file, CV_LOAD_IMAGE_GRAYSCALE);

    Ptr<FaceRecognizer> model = createEigenFaceRecognizer();
    model->load("models/model.xml");
    cout << model->predict(testSample) << endl;

    return 0;
}