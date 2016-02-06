import cv2
import numpy as np
import os
import random
import os
from os import listdir
from os.path import isfile, join
from PIL.ImageChops import offset

TRAIN_SIZE = 40
TOLERANCE = 80

def trainBackgroundSubstractorMOG(frames, nextFrames):
    bgs = cv2.createBackgroundSubtractorMOG2()
    for j in range(len(nextFrames)):
        frame = cv2.imread(nextFrames[j])
        bgs.apply(frame)
    for i in range(TRAIN_SIZE):
        id = random.choice(range(len(frames)));
        frame = cv2.imread(frames[id])
        bgs.apply(frame)
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



def boundingBoxIntersect(r1, r2):
    (x1, y1, w1, h1) = r1
    (x2, y2, w2, h2) = r2
    separate = (x1 + w1 < x2 or
        x1 > x2 + w2 or
        y1 > y2 + h2 or
        y1 + h1 < y2)
    compatible = (abs(r1[2] - r2[2]) < TOLERANCE and
         abs(r1[3] - r2[3]) < TOLERANCE)
    return (not separate and compatible)


def adjustBoundingBox(bb, predicted):
    adj = predicted
    offsetX = abs(bb[2] - predicted[2])/4
    offsetY = abs(bb[3] - predicted[3])/4
    #Bounding box predicted greater on width
    if bb[2] < predicted[2]:
        adj[0] = predicted[0] + offsetX
        adj[2] = bb[2] + offsetX
    else:
        adj[2] = predicted[2] - offsetX
        
    if bb[3] < predicted[3]:
        adj[1] = predicted[1] + offsetY
        adj[3] = bb[3] + offsetY
    else:
        adj[3] = predicted[3] - offsetY
        
    return adj



def predictPerson(camera, previousFrames, nextFrames):  
    path = os.path.abspath('/Applications/MAMP/htdocs/watss/source/frames/')
    imagesPath = join(path, str(camera) + '/')
    images = [join(imagesPath, f) for f in listdir(os.path.abspath(imagesPath)) if isfile(join(imagesPath, f))]
    nextImages = []
    for j in range(len(nextFrames)):
        nextImages.append(join(path, nextFrames[j]))
    
    bgs = trainBackgroundSubstractorMOG(images, nextImages)
    
    out = []
    
    for i in range(len(nextImages)):
        
        bb = previousFrames[len(previousFrames) - 1][1]
        filename = nextImages[i]
        
        frame = cv2.imread(filename)
        rects = getDetections(bgs, frame)
        
        found = False
        for rect in rects:
            (x, y, w, h) = rect
            frame = cv2.rectangle(frame, (x, y), (x + w, y + h), (255, 0, 0), 2)   
            if(boundingBoxIntersect(bb, rect)):
                #rect = adjustBoundingBox(bb, rect)
                obj = {'x': rect[0], 'y': rect[1], 'width': rect[2], 'height': rect[3]}
                out.append(obj)
                          
                frame = cv2.rectangle(frame, (x, y), (x + w, y + h), (0, 0, 0), 2)                        
                             
                previousFrames.append([nextFrames[i], rect])
                found = True
                break
        
        
        if(not found):
            obj = {'x' : bb[0], 'y' : bb[1], 'width' : bb[2], 'height': bb[3]}
            out.append(obj)
            previousFrames.append([nextFrames[i], bb])
            
            #(x, y, w, h) = bb
            #frame = cv2.rectangle(frame, (x, y), (x + w, y + h), (0, 0, 0), 2)                        
            
        #cv2.imshow('img', frame)    
        #cv2.waitKey(0)    
        
    return out
