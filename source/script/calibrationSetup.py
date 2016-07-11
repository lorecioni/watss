import cv2
import cv
import numpy as np


def calibrate(cam):
    print "\n\nCamera calibration " + str(cam)

    intrinsicPath = "/var/www/MuseumVisitors/calibration/calibIntrCam" + str(cam) + "-R1280x800.yml"
    extrinsicPath = "/var/www/MuseumVisitors/calibration/calibExtr_" + str(cam) + ".yaml"

    print "Rotation matrix:"
    VectRot = cv2.cv.Load(extrinsicPath, cv.CreateMemStorage(), name="Rotation")
    MatRot=cv.CreateMat(3,3,cv.CV_64FC1)
    cv.Rodrigues2(VectRot,MatRot)
    MatRot = np.matrix(MatRot)
    print MatRot

    print "Translation vector:"
    VectTrans = cv2.cv.Load(extrinsicPath, cv.CreateMemStorage(), name="Translation")
    VectTrans = np.matrix(VectTrans)
    print VectTrans

    print "Camera matrix:"
    MatCam = cv2.cv.Load(intrinsicPath, cv.CreateMemStorage(), name="camera_matrix")
    MatCam = np.matrix(MatCam)
    print MatCam

    RotTrans = np.concatenate((MatRot[0:3,0:2], VectTrans), axis=1)
    print(RotTrans)

    Hw = MatCam * RotTrans
    print(Hw)
    HT=(Hw.T).I
    HI2W=Hw.I
    HW2I=(HI2W.I).T
    print "HI2W:"
    print(HI2W)

    print "HW2I:"
    print(HW2I)




calibrate(1)
calibrate(2)
calibrate(3)
calibrate(4)
