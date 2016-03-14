/*
 * BackgroundModelSerializer.cpp
 *
 *  Created on: 14/mar/2016
 *      Author: lorenzocioni
 */

#include "BackgroundModelSerializer.h"

#include <iostream>

using namespace cv;
using namespace std;
namespace fs = boost::filesystem;


BackgroundModelSerializer::BackgroundModelSerializer(Ptr<BackgroundSubtractor> bgs, string filename){
	this->bgs = bgs;
	this->filename = filename;
}

void BackgroundModelSerializer::train(string imagesPath, int trainSize){
	using namespace boost::filesystem;
	path p (imagesPath);

    if(is_directory(p)) {
        cout << p << " is a directory containing:\n";

        for(auto& entry : boost::make_iterator_range(directory_iterator(p), {}))
            cout << entry << "\n";
    }
}

void BackgroundModelSerializer::load(){

}

void BackgroundModelSerializer::serialize(){
	FileStorage fs("./" + this->filename + ".yaml", FileStorage::WRITE);

	this->bgs.get()->write(fs);


	fs.release();
	cout << "done!" << endl;// explicit close
}

BackgroundModelSerializer::~BackgroundModelSerializer(){}
