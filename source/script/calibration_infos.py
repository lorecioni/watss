import numpy as np
import cv2
import cv

#--- Begin Local imports
import local_import_state
local_import_state.current="calibration_infos.py"
import local_importer

from connection import *
from camera_connector import *

# Not needed anymore?
#if local_import_state.list_created:
#	for module in local_import_state.modules_list:
#		try:
#			print "Importing",module
#			exec("from "+module+" import *") #Maybe dangerous?
#		except:
#			print "Failed: Importing",module
#			local_import_state.modules_list.append(module)
#			continue		
#	local_import_state.imported=True
#--- End Local imports


class calibration_infos:
	""" Class for I/O interactions for cameras parameters """
	MatCam = 0
	VectDist = 0
        #MatDist = 0
        MatTrans = 0        
	VectRot = 0
	MatRot = 0
	VectTrans = 0
	CameraId = -1
	HI2W=None
	HW2I=None
	HT=None
	HMeter=None
	HTMeter=None
	#TODO: Add error handling if paths not filled or incorrect
	# - setCamMatrix, re-code calibrationOpenCV.cpp in python?
	# - saveIntrinsic, in which folder? filename should contain camera_id. update path in db
	# - saveExtrinsic, in which folder? filename should contain camera_id. update path in db
	# Create a "calibration" folder in data?

	def __init__(self, cam_id):
		self.CameraId=cam_id
		self.CameraDB=Camera.select(clause=Camera.q.id==self.CameraId)[0]

	##### Intrinsic parameters
	def getIntrinsicPath(self):
		#Get intrinsic params file path
		return self.CameraDB.intrinsic

	def loadIntrinsicFromDB(self):
		intrinsic_path=self.getIntrinsicPath()
		self.loadCamMatrixFromFile(intrinsic_path)
		self.loadDistMatrixFromFile(intrinsic_path)

	# CamMatrix
	def loadCamMatrixFromFile(self,filename):
		#print mnemosyne_dir+filename
		try:
			#self.MatCam = cv.CreateMat(3,3,cv.CV_32FC1)
			#tmpMatCam = cv.Load(mnemosyne_dir+filename, cv.CreateMemStorage(), name="camera_matrix")
			self.MatCam = cv.Load(mnemosyne_dir+filename, cv.CreateMemStorage(), name="camera_matrix")
			#cv.Convert(tmpMatCam, self.MatCam)
		except:
			print "Could not load file:",mnemosyne_dir+filename
	

	def loadCamMatrixFromDB(self):
		intrinsic_path=self.getIntrinsicPath()
		self.loadCamMatrixFromFile(intrinsic_path)

	def getCamMatrix(self):
		if not self.MatCam:
			self.loadCamMatrixFromDB()
		return self.MatCam

	# DistMatrix
	def loadDistMatrixFromFile(self,filename):
		#print mnemosyne_dir+filename
		try:
			#self.VectDist = cv.CreateMat(5,1,cv.CV_32FC1)
			#tmpVectDist = cv.Load(mnemosyne_dir+filename, cv.CreateMemStorage(), name="distortion_coefficients")
			self.VectDist = cv.Load(mnemosyne_dir+filename, cv.CreateMemStorage(), name="distortion_coefficients")
			#cv.Convert(tmpVectDist, self.VectDist)
		except:
			print "Could not load file:",mnemosyne_dir+filename

	def loadDistMatrixFromDB(self):
		intrinsic_path=self.getIntrinsicPath()
		#print intrinsic_path
		self.loadDistMatrixFromFile(intrinsic_path)

	def getVectDist(self):
		if not self.VectDist:
			self.loadDistMatrixFromDB()
		return self.VectDist
	
#	def getMatDist(self): # Does not work VectDist is a 1x5 vector?
#		if not self.MatDist:
#			self.MatDist=cv.CreateMat(3,3,cv.CV_64FC1)
#			cv.Rodrigues2(self.getVectDist(),self.MatDist)
#		return self.MatDist

	##### Extrinsic parameters
	def getExtrinsicPath(self):
		#Get extrinsic params file path
		return Camera.select(clause=Camera.q.id==self.CameraId)[0].extrinsic

	def setExtrinsicPath(self,path):
		#Get extrinsic params file path
		return self.CameraDB.set(extrinsic=path)

	def loadExtrinsicFromDB(self):
		extrinsic_path=self.getExtrinsicPath()
		self.loadVectTransFromFile(extrinsic_path)
		self.loadVectRotFromFile(extrinsic_path)

	# Rotation vector and matrix
	def loadVectRotFromFile(self,filename):
		#print mnemosyne_dir+filename
		print (mnemosyne_dir+filename)
		self.VectRot = cv.Load(mnemosyne_dir+filename, cv.CreateMemStorage(), name="Rotation")
		if self.VectRot.type==5: #Everything should be in CV_64FC1
			tmpVectRot = self.VectRot
			self.VectRot = cv.CreateMat(3,1,cv.CV_64FC1)
			cv.Convert(tmpVectRot, self.VectRot)

	def loadVectRotFromDB(self):
		extrinsic_path=self.getExtrinsicPath()
		print mnemosyne_dir+extrinsic_path
		self.loadVectRotFromFile(extrinsic_path)

	def getVectRot(self):
		if not self.VectRot:
			self.loadVectRotFromDB()
		return self.VectRot

	def getMatRot(self):
		if not self.MatRot:
			self.MatRot=cv.CreateMat(3,3,cv.CV_64FC1)
			cv.Rodrigues2(self.getVectRot(),self.MatRot)
		return self.MatRot
	
	# Translation vector
	def loadVectTransFromFile(self,filename):
		#print mnemosyne_dir+filename
		self.VectTrans = cv.Load(mnemosyne_dir+filename, cv.CreateMemStorage(), name="Translation")
		if self.VectTrans.type==5: #Everything should be in CV_64FC1
			tmpVectTrans = self.VectTrans
			self.VectTrans = cv.CreateMat(3,1,cv.CV_64FC1)
			cv.Convert(tmpVectTrans, self.VectTrans)

	def loadVectTransFromDB(self):
		extrinsic_path=self.getExtrinsicPath()
		#print mnemosyne_dir+extrinsic_path
		self.loadVectTransFromFile(extrinsic_path)

	def getVectTrans(self):
		if not self.VectTrans:
			self.loadVectTransFromDB()
		return self.VectTrans

	def getMatTrans(self):
		if not self.MatTrans:
			self.MatTrans=cv.CreateMat(3,3,cv.CV_64FC1)
			cv.Rodrigues2(self.getVectTrans(),self.MatTrans)
		return self.MatTrans


	def drawFeetPoint(self,img,imgCoords,RWFeet):
		""" Draw a point as a red cross """
		typeImg=type(img)
		if typeImg==np.ndarray:
			cv2.line(img, (imgCoords[0]-5,imgCoords[1]), (imgCoords[0]+5,imgCoords[1]), cv.CV_RGB(255,0,0), thickness=1, lineType=8, shift=0)
			cv2.line(img, (imgCoords[0],imgCoords[1]-5), (imgCoords[0],imgCoords[1]+5), cv.CV_RGB(255,0,0), thickness=1, lineType=8, shift=0)
			#text="X:"+str(RWFeet[0])+"m, Y:"+str(RWFeet[1])+"m"
			text="X:"+"%0.2f" % (RWFeet[0],)+"m, Y:"+"%0.2f" % (RWFeet[1],)+"m"
			cv2.putText(img, text, (imgCoords[0]-len(text)*4,imgCoords[1]+12), cv2.FONT_HERSHEY_PLAIN, 1.0, (0, 0, 0), thickness = 1, lineType=cv2.CV_AA)

		else:				
			cv.Line(img, (imgCoords[0]-5,imgCoords[1]), (imgCoords[0]+5,imgCoords[1]), cv.CV_RGB(255,0,0), thickness=1, lineType=8, shift=0)
			cv.Line(img, (imgCoords[0],imgCoords[1]-5), (imgCoords[0],imgCoords[1]+5), cv.CV_RGB(255,0,0), thickness=1, lineType=8, shift=0)
			cv.PutText(img, "X:"+str(RWFeet[0])+"m, Y:"+str(RWFeet[1])+"m", (imgCoords[0]-len(str(RWFeet)*8),imgCoords[1]+8), cv.FONT_HERSHEY_PLAIN, 1.0, (0, 0, 0), thickness = 2, linetype=cv2.CV_AA)
			
	def drawPojection3DToImgPlane(self,img,RWFeet):		
		#Not working:
		return
		rvec=np.matrix(self.getVectRot(), dtype=np.float64)
		tvec=np.matrix(self.getVectTrans(), dtype=np.float64)
		cameraMatrix=np.matrix(self.getCamMatrix(), dtype=np.float64)
		distCoeffs=np.matrix(self.getVectDist(), dtype=np.float64)
		imgCoords=np.zeros((3,1), dtype=np.float64)
		rwCoords=np.array([[float(RWFeet[0])*100],[float(RWFeet[1])*100],[0]], dtype=np.float64)
		#print rvec
		#print tvec
		#print cameraMatrix
		#print distCoeffs
		#print imgCoords 
		#print rwCoords
		# Not working: OpenCV Error: Assertion failed (npoints >= 0 && (depth == CV_32F || depth == CV_64F)) in projectPoints
		cv2.projectPoints(rwCoords, rvec, tvec, cameraMatrix, distCoeffs, imgCoords)		
		if typeImg==np.ndarray:
			cv2.line(img, (imgCoords[0]-5,imgCoords[1]), (imgCoords[0]+5,imgCoords[1]), cv.CV_RGB(255,0,0), thickness=1, lineType=8, shift=0)
			cv2.line(img, (imgCoords[0],imgCoords[1]-5), (imgCoords[0],imgCoords[1]+5), cv.CV_RGB(255,0,0), thickness=1, lineType=8, shift=0)
		
	def initializeH(self):		
		# TODO: Shouldn't we take into account MatDist?
		#VectDist=self.getVectDist()
		#npVectDist=np.matrix(VectDist)
		# Getting matrices
		RMatRot=self.getMatRot()
		MatCam=self.getCamMatrix()
		MatTrans=self.getMatTrans()
		VectTrans=self.getVectTrans()		
		#MatCamRot=cv.CreateMat(3,3,cv.CV_64FC1)
		#MatCamTrans=cv.CreateMat(3,1,cv.CV_64FC1)
		# Multiply cvMat seems impossible with opencv's python bindings, we need to use numpy matrices...
		npMatCam=np.matrix(MatCam)				
		npMatRot=np.matrix(RMatRot)
		print(npMatRot)
		npMatTrans=np.matrix(MatTrans)
		npVectTrans=np.matrix(VectTrans)
		npVectTransMeter=npVectTrans/100.0
#		print npMatCam
#		print npMatRot
#		print npMatTrans
#		print npVectTrans
		npRotTrans=np.concatenate((npMatRot[0:3,0:2], npVectTrans), axis=1)
		npRotTransMeter=np.concatenate((npMatRot[0:3,0:2], npVectTransMeter), axis=1)
#		print npRotTrans		
		Hw=npMatCam*npRotTrans
		HwMeter=npMatCam*npRotTransMeter
#		print Hw		
#		#print np.invert(Hw)
#		print Hw.I
		#print MatPZ0[:,:]
		#self.H=np.invert(Hw)
		self.HT=(Hw.T).I
		self.HI2W=Hw.I
		self.HW2I=(self.HI2W.I).T
		#print self.H
		self.HTMeter=(HwMeter.T).I
		self.HMeter=HwMeter.I

	def getImgFeet(self,rwx,rwy):
		#Get feet coordinate in real world
		RWFeet=np.matrix([100*rwx, 100*rwy, 1]) 
		if self.HW2I==None:
			self.initializeH()
		ImgFeet=(RWFeet)*self.HW2I
		#Normalized by third coordinate
		#print ImgFeet,ImgFeet.shape
		ImgFeet/=ImgFeet[0,2]
		return ImgFeet



	def getRWFeet(self,imgx,imgy):
		#Get feet coordinate in real world
		ImgFeet=np.matrix([[imgx], [imgy], [1]]) #Why [1]?
		#ImgFeet=np.matrix([[imgx], [imgy], [0]]) 
#		print self.H
#		print ImgFeet
		#print self.H
		if self.HI2W==None:
			self.initializeH()
#		print self.H
		# Shouldn't we undistort these coordinates?...
		RWFeet=self.HI2W*ImgFeet
#		print RWFeet
		#Normalized by third coordinate
		RWFeet/=RWFeet[2]
#		print RWFeet
		RWFeet/=100 # should we convert in meters?
		#print ImgFeet, RWFeet.T
		return RWFeet

#Compute homography for speedHog in matlab as:		
#t=[ -37.57090759, 80.28246307, 324.10479736]
#t=t/100
#R = [ 0.99975443, -0.02184854, 0.00371061;
#-0.00788785, -0.5072881 , -0.86174047;
#0.02071012, 0.86149955, -0.50733584]
#K=[ 527.54589334, 0. , 320.85352958;
#0. , 495.32277906, 260.31248014;
#0. , 0. , 1. ]
#P=[K*R K*t']
#P(:,3)=[];
#Hi2w=P'
#invHi2w=inv(Hi2w)
#(invHi2w./invHi2w(9))'
#ans(:)
