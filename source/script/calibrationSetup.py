import cv2
import cv
import numpy as np

def calibrate(cam):
    print "Camera calibration " + str(cam)
    print "Rotation matrix:"
    extrinsicPath = "/home/lcioni/calibExtr_" + str(cam) + ".yaml"
    VectRot = cv2.cv.Load(extrinsicPath, cv.CreateMemStorage(), name="Rotation")
    MatRot=cv.CreateMat(3,3,cv.CV_64FC1)
    cv.Rodrigues2(VectRot,MatRot)
    MatRot = np.matrix(MatRot)
    print MatRot
    print "Translation vector:"
    VectTrans = cv2.cv.Load(extrinsicPath, cv.CreateMemStorage(), name="Translation")    
    print VectTrans

            
calibrate(1)