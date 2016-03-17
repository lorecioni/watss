/*
 * Persistance.cpp
 *
 *  Created on: 17/mar/2016
 *      Author: lorenzocioni
 */

#include "opencv2/core.hpp"
#include <string>
#include <iostream>

using namespace cv;
using namespace std;

Mat load(string path, string name){
	Mat output;
	cout << endl << "Reading: " << endl;
	FileStorage fs;
	fs.open(path, FileStorage::READ);
	if (fs.isOpened()){
		fs[name] >> output;
	}
	return output;
}

int main() {

	string f = "Applications/MAMP/htdocs/watss/calibration/calibExtr_1.yaml";
	string name = "Rotation";

	cout << name << endl;

	Mat o = load(f, name);
	cout << o << endl;
	return 0;
}

//BOOST_PYTHON_MODULE(opencv_filestorage){
//    using namespace boost::python;
//    def("test", store);
//}



