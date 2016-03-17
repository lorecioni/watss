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
using namespace boost::filesystem;

BackgroundModelSerializer::BackgroundModelSerializer(Ptr<BackgroundSubtractor> bgs, string filename){
	this->bgs = bgs;
	this->filename = filename;
}

void BackgroundModelSerializer::train(string imagesPath, int trainSize){
	namespace fs = boost::filesystem;
	fs::path apk_path(imagesPath);
	fs::recursive_directory_iterator end;

	string filename;

	int count = 0;
	for (fs::recursive_directory_iterator i(apk_path); i != end; ++i){
		const fs::path cp = (*i);
		filename = cp.string();
		Mat img = imread(filename);
		Mat fgmask;
		this->bgs->apply(img, fgmask);
		img.release();
		fgmask.release();
		count++;
		cout << "Processed " << count << endl;
		if(count >= trainSize){
			break;
		}
	 }

	cout << "Train finished" << endl;

	this->serialize();
}

void BackgroundModelSerializer::load(){
	FileStorage fs("test.yml", FileStorage::READ);
	this->bgs = createBackgroundSubtractorMOG2();
	this->bgs.dynamicCast<cv::BackgroundSubtractorMOG2>()->setHistory((int) fs["history"]);
	this->bgs.dynamicCast<cv::BackgroundSubtractorMOG2>()->setNMixtures((int) fs["nmixtures"]);
	this->bgs.dynamicCast<cv::BackgroundSubtractorMOG2>()->setBackgroundRatio((double) fs["backgroundRatio"]);
	this->bgs.dynamicCast<cv::BackgroundSubtractorMOG2>()->setVarThreshold((double) fs["varThreshold"]);
	this->bgs.dynamicCast<cv::BackgroundSubtractorMOG2>()->setVarThresholdGen((double) fs["varThresholdGen"]);
	this->bgs.dynamicCast<cv::BackgroundSubtractorMOG2>()->setVarInit((double) fs["varInit"]);
	this->bgs.dynamicCast<cv::BackgroundSubtractorMOG2>()->setVarMin((double) fs["varMin"]);
	this->bgs.dynamicCast<cv::BackgroundSubtractorMOG2>()->setVarMax((double) fs["varMax"]);
	this->bgs.dynamicCast<cv::BackgroundSubtractorMOG2>()->setComplexityReductionThreshold((double) fs["complexityReductionThreshold"]);
	this->bgs.dynamicCast<cv::BackgroundSubtractorMOG2>()->setShadowThreshold((double) fs["shadowThreshold"]);

	cout << "Load finished" << endl;
}

void BackgroundModelSerializer::serialize(){
	FileStorage fs("./test.yaml", FileStorage::WRITE);
	this->bgs.get()->write(fs);
	fs.release();
	cout << "Serialized!" << endl;// explicit close
}

BackgroundModelSerializer::~BackgroundModelSerializer(){}
