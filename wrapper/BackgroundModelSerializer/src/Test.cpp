//============================================================================
// Name        : Main.cpp
// Author      : Lorenzo Cioni
//============================================================================

//#include <boost/python.hpp>

#include <string>
#include <iostream>
#include "BackgroundModelSerializer.h"

using namespace cv;
using namespace std;

int main() {

	cout << "ciao" <<endl;
	Ptr<BackgroundSubtractor> mog2 = createBackgroundSubtractorMOG2();

	BackgroundModelSerializer* bms = new BackgroundModelSerializer(mog2, "test");
	Mat frame = imread("/Applications/MAMP/htdocs/watss/wrapper/OpenCV-FileStorage/test.jpg");
	Mat output;

	mog2->apply(frame, output);
	bms->train("/Applications/MAMP/htdocs/watss/source/frames/1/", 200);
	//bms->serialize();



	return 0;
}

/*
void store(){

}

BOOST_PYTHON_MODULE(opencv_filestorage){
    using namespace boost::python;
    def("test", store);
}
*/
