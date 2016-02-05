import cv2
import numpy as np
import os
import random
import os
from os import listdir
from os.path import isfile, join

TRAIN_SIZE = 40
TOLERANCE = 80

def trainBackgroundSubstractorMOG(frames):
    bgs = cv2.createBackgroundSubtractorMOG2()
    for i in range(TRAIN_SIZE):
        id = random.choice(range(len(frames)));
        filename = os.path.abspath('../frames/1/' + str(frames[id]))
        frame = cv2.imread(filename)
        bgs.apply(frame)
        #print('processed ' + str(id))
    return bgs
    
def getDetections(bgs, frame):
    fgmask = bgs.apply(frame)

    _, fgmask = cv2.threshold(fgmask, 200, 255, cv2.THRESH_BINARY)
    fgmask = cv2.dilate(fgmask, cv2.getStructuringElement(cv2.MORPH_RECT, (4,4)))
    fgmask = cv2.erode(fgmask, cv2.getStructuringElement(cv2.MORPH_RECT, (2,2)))
    
    image, contours, hierarchy = cv2.findContours(fgmask,cv2.RETR_TREE,cv2.CHAIN_APPROX_SIMPLE)
    detections = []
    for contour in contours:
        detections.append(cv2.boundingRect(contour))

    filtered = []
    for rect in detections:
        x,y,w,h = rect
        if w > 30 and h > 50:
            #intersects = [r for r in detections if is_intersection(rect, r)]
            #if len(intersects) <= 1:
            #cv2.rectangle(fgmask, (x,y), (x+w,y+h), 255, 3)
            filtered.append(rect)

    return filtered


def boundingBoxIntersect(bb, rect):
    a = abs(bb[0] - rect[0])
    b = abs(bb[1] - rect[1])
    c = abs(bb[2] - rect[2])
    d = abs(bb[3] - rect[3])
    if ((a + b + c + d) < TOLERANCE):
        return True
    else:
        return False


def predictPerson(camera, previousFrames, nextFrames):  
    path = os.path.abspath('../frames/' + str(camera) + '/')
    images = [f for f in listdir(os.path.abspath(path)) if isfile(join(path, f))]
    bgs = trainBackgroundSubstractorMOG(images)
    
    out = []
    
    for i in range(len(nextFrames)):
        
        bb = previousFrames[len(previousFrames) - 1][1]
        
        filename = os.path.abspath('../frames/1/' + str(nextFrames[i]))
        frame = cv2.imread(filename)
        rects = getDetections(bgs, frame)
        
        found = False
        for rect in rects:
            if(boundingBoxIntersect(bb, rect)):
                out.append(rect)
                previousFrames.append([nextFrames[i], rect])
                found = True
                break
        
        if(not found):
            out.append(bb)
            previousFrames.append([nextFrames[i], bb])
        
    return out
                #(x, y, w, h) = rect
                #frame = cv2.rectangle(frame, (x, y), (x + w, y + h), 255, 2)
                            
        #cv2.imshow('img', frame)    
        #cv2.waitKey(0)     

