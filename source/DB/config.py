import os
import datetime
import numpy as np
import ConfigParser
import sys


confFilename='./config.conf'
framesPath= '../frames'
connIniFilename='../php/connection.ini'
operaId=-1;

def loadFrames(fOutSQL):


	global framesPath
	print('\n[Loading Frames]')
	fOutSQL.write('\n\nINSERT INTO `video` (`frameid`, `cameraid`, `path`, `date`) VALUES');
	camerasFold=next(os.walk(framesPath))[1]

	isFirstLine=1
	for cam in sorted(camerasFold):
		if not cam.isdigit():
			raise Exception('Folder '+ cam +' is not valid. The camera folder must be an integer value')
		frameId=0;
	
 
		for imgFile in sorted(os.listdir(framesPath + '/' + cam)): 			
			if not (imgFile.endswith('jpg') or imgFile.endswith('jpeg') or imgFile.endswith('bmp') or imgFile.endswith('png')):
				raise Exception('Image file '+ imgFile +' not supported')
			frameId+=1;
			if not isFirstLine:
				fOutSQL.write(',');
			isFirstLine=0					
			fOutSQL.write("\n('F"+str(frameId).zfill(6)+"', 'C"+cam+"', '"+cam+"/"+imgFile+"','"+ datetime.datetime.now().date().strftime("%Y-%m-%d")+"')")					
			print  'Insert path of image '+ imgFile +' in camera ' + cam;

	fOutSQL.write(';');


def loadUsers(fOutSQL,users):
	fOutSQL.write('\n\nINSERT INTO `user` (`userid`, `name`) VALUES');
	userId=0
	isFirstLine=1
	print('\n[Loading Users]')
	for user in users:
		userId+=1
		if not isFirstLine:
			fOutSQL.write(',')
		isFirstLine=0					
		fOutSQL.write("\n('U"+str(userId)+"', '"+user+"')")
		print  'Insert user '+ user;
	fOutSQL.write(';')

def loadCameras(fOutSQL,cameraIds,calibs):
	fOutSQL.write('\n\nINSERT INTO `camera` (`cameraid`, `calibration`) VALUES');
	userId=0
	isFirstLine=1
	print('\n[Loading Cameras]')
	for i in range(0,len(cameraIds)):
		if not isFirstLine:
			fOutSQL.write(',')
		isFirstLine=0					
		fOutSQL.write("\n('C"+cameraIds[i]+"', '"+calibs[i]+"')")
		print  'Insert Camera '+ cameraIds[i];
	fOutSQL.write(';')


def loadpoi(fOutSQL,camerasStr,location_xStr, location_yStr ,widthStr, heightStr,nameStr):
	global operaId	
	operaId+=1		
	isFirstLine=1
	

	fOutSQL.write('\n\nINSERT INTO `poi` (`poiid`, `cameraid`, `location_x`, `location_y`, `width`, `height`, `name`) VALUES');

	cameras=camerasStr.split(',')
	l_x=location_xStr.split(',')
	l_y=location_yStr.split(',')
	w=widthStr.split(',')
	h=heightStr.split(',')


	for i in range(0,len(cameras)):
		if not isFirstLine:
			fOutSQL.write(',');
		isFirstLine=0					

		fOutSQL.write("\n('O"+str(operaId)+"', 'C"+cameras[i]+"', "+l_x[i]+", "+l_y[i]+", "+w[i]+", "+h[i]+", '"+nameStr+"')")
		print  'Insert poi '+ nameStr + ' in camera ' + cameras[i];
	fOutSQL.write(';');



if __name__ == '__main__':


	try:
		
		print 'Loading configuration parameters...'
		Config = ConfigParser.ConfigParser()
		Config.read(confFilename)


		print 'Create connection file: '+connIniFilename
		fOutIni=open(connIniFilename,'w')
		fOutIni.write('user="' + Config.get('DBConnection','user') + '"\n')
		fOutIni.write('password="' + Config.get('DBConnection','password') + '"\n')
		fOutIni.write('host="' + Config.get('DBConnection','host') + '"\n')
		fOutIni.write('db="' + Config.get('DBConnection','db') + '"\n')
		fOutIni.close()

		fOutSQL=open('insertData2DB.sql','w')

		#loading cameras in the database 
		loadCameras(fOutSQL,Config.get('camera','ids').split(','),Config.get('camera','calib').split(','))

		#loading images in the database 
		loadFrames(fOutSQL)
	
		# loading users
		loadUsers(fOutSQL,Config.get('user','names').split(','))

		print('\n[Loading poi]')
		
		defaultPos=""
		for nbCam in range(len(Config.get('camera','ids'))):
			if nbCam==len(Config.get('camera','ids')):
				defaultPos=defaultPos+"0"
			else:
				defaultPos=defaultPos+"0,"
		#loadpoi(fOutSQL,'1,2,3,4','0,0,0,0','0,0,0,0','0,0,0,0','0,0,0,0','no poi')
		loadpoi(fOutSQL,Config.get('camera','ids'),defaultPos,defaultPos,defaultPos,defaultPos,'no poi')
		
		poiList=[poi for poi in Config.sections() if poi.startswith("poi")]
		for poi in poiList:
			# loading poi
			loadpoi(fOutSQL,Config.get(poi,'cameras'),Config.get(poi,'location_x'),Config.get(poi,'location_y'),Config.get(poi,'width'),Config.get(poi,'height'),poi.split('_')[1])

	except Exception,e:	
		print 'error: ',str(e)
	print '\nLoading data completed!!!'	
	fOutSQL.close()
