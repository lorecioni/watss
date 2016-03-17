/*
 * Persistance.cpp
 *
 *  Created on: 17/mar/2016
 *      Author: lorenzocioni
 */

#include <boost/python.hpp>
#include <boost/python/def.hpp>
#include "opencv2/core.hpp"
#include <string>
#include <iostream>

using namespace cv;
using namespace std;

void load(string path, string name){
	Mat output;
	FileStorage fs;
	fs.open(path, FileStorage::READ);
	if (fs.isOpened()){
		fs[name] >> output;
	}
	cout << output << endl;
}

int main(){

	cout << "cio" << endl;
}

//BOOST_PYTHON_MODULE(cv_persistance){
//    using namespace boost::python;
//    def("load", load);
//}



