/*
 * BackgroundModelSerializer.h
 *
 *  Created on: 14/mar/2016
 *      Author: lorenzocioni
 */

#ifndef BACKGROUNDMODELSERIALIZER_H_
#define BACKGROUNDMODELSERIALIZER_H_
#include "boost/filesystem/operations.hpp"
#include "boost/filesystem/path.hpp"
#include "boost/progress.hpp"
#include <opencv2/highgui.hpp>
#include <opencv2/video.hpp>
#include "opencv2/videoio.hpp"
#include "opencv2/imgproc.hpp"
#include "opencv2/imgcodecs.hpp"
#include "string"

using namespace std;
using namespace cv;

class BackgroundModelSerializer{
	private:
		string filename;
		Ptr<BackgroundSubtractor> bgs;

	public:
		BackgroundModelSerializer(Ptr<BackgroundSubtractor> bgs, string filename);
		virtual ~BackgroundModelSerializer();
		void train(string imagesPath, int trainSize);

		void serialize();
		void load();

		const string& getFilename() const {
			return filename;
		}

		void setFilename(const string& filename) {
			this->filename = filename;
		}
};


#endif /* BACKGROUNDMODELSERIALIZER_H_ */
