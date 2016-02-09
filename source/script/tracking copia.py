import cv2
import numpy as np
import os
import random
import os
from os import listdir
from os.path import isfile, join
from PIL.ImageChops import offset

TRAIN_SIZE = 2
TOLERANCE = 80

HOG_STRIDE = 8
HOG_PADDING = 32
HOG_SCALE = 1.05

def trainBackgroundSubstractorMOG(images, next):
    bgs = cv2.createBackgroundSubtractorMOG2()
    for i in range(len(next)):
        frame = cv2.imread(next[i])
        bgs.apply(frame)
    for i in range(TRAIN_SIZE):
        id = random.choice(range(len(images)));
        frame = cv2.imread(images[id])
        bgs.apply(frame)
    return bgs

def initializeHOGDescriptor():
    hog = cv2.HOGDescriptor()
    hog.setSVMDetector(cv2.HOGDescriptor_getDefaultPeopleDetector())
    return hog
    
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

def inside(r, q):
    rx, ry, rw, rh = r
    qx, qy, qw, qh = q
    return rx > qx and ry > qy and rx + rw < qx + qw and ry + rh < qy + qh

def detectPeopleHOG(hog, frame):
    found, w = hog.detectMultiScale(frame, winStride=(8,8), padding=(32,32), scale=1.05)
    found_filtered = []
    for ri, r in enumerate(found):
        for qi, q in enumerate(found):
            if ri != qi and inside(r, q):
                break
            else:
                found_filtered.append(r)
    return found_filtered

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


def kalman(frame, window):
    
    
    update(frame)

    
    
    stateSize = 6;
    measSize = 4;
    contrSize = 0;
    kalman = cv2.KalmanFilter(6, 4, 0)
    state = 0.1 * np.random.randn(2, 1)
    kalman.transitionMatrix = np.array([[1., 1.], [0., 1.]])
    kalman.measurementMatrix = 1. * np.ones((1, 2))
    kalman.processNoiseCov = 1e-5 * np.eye(2)
    kalman.measurementNoiseCov = 1e-1 * np.ones((1, 1))
    kalman.errorCovPost = 1. * np.ones((2, 2))
    kalman.statePost = 0.1 * np.random.randn(2, 1)



def predictPerson(camera, previousFrames, nextFrames):  
    path = os.path.abspath('/Applications/MAMP/htdocs/watss/source/frames/')
    imagesPath = join(path, str(camera) + '/')
    images = [join(imagesPath, f) for f in listdir(os.path.abspath(imagesPath)) if isfile(join(imagesPath, f))]
    nextImages = []
    previousImages = []
    
    for i in range(len(previousFrames)):
        tmp = join(path, previousFrames[i][0])
        #frame = cv2.imread(tmp)
        previousImages.append(tmp)
        
    for j in range(len(nextFrames)):
        tmp = join(path, nextFrames[j])
        #frame = cv2.imread(tmp)
        nextImages.append(tmp)
        
    bgs = trainBackgroundSubstractorMOG(images, nextImages)
    hog = initializeHOGDescriptor()
    
##### KALMAN

   
    kalman = cv2.KalmanFilter(4,2)
    kalman.measurementMatrix = np.array([[1,0,0,0],[0,1,0,0]],np.float32)
    kalman.transitionMatrix = np.array([[1,0,1,0],[0,1,0,1],[0,0,1,0],[0,0,0,1]],np.float32)
    kalman.processNoiseCov = np.array([[1,0,0,0],[0,1,0,0],[0,0,1,0],[0,0,0,1]],np.float32) * 0.03
    measurement = np.array((2,1), np.float32)
    prediction = np.zeros((2,1), np.float32)
    term_crit = ( cv2.TERM_CRITERIA_EPS | cv2.TERM_CRITERIA_COUNT, 10,1 )
    center = None
    ########
    
    
    for k in range(len(previousImages)):
        frame = cv2.imread(previousImages[k])
        people = detectPeopleHOG(hog, frame)
    
    out = []
    
    for i in range(len(nextImages)):
        
        bb = previousFrames[len(previousFrames) - 1][1]
                
        frame = cv2.imread(nextImages[i])
        rects = getDetections(bgs, frame)
        people = detectPeopleHOG(hog, frame) 
        
        
        #debug
        people = []
        rects = []
        
        
        #KALMAN
        
        kalman.correct(center)
        prediction = kalman.predict()
        print(prediction)
        cv2.circle(frame, (int (prediction[0]), prediction[1]), 4, (0, 255, 1), 2)
        
        ########
        
        
        for person in people:
            (x, y, w, h) = person
            for x, y, w, h in rects:
                pad_w, pad_h = int(0.15*w), int(0.05*h)
                cv2.rectangle(frame, (x+pad_w, y+pad_h), (x+w-pad_w, y+h-pad_h), (0, 255, 0), 1)
        # the HOG detector returns slightly larger rectangles than the real objects.
        # so we slightly shrink the rectangles to get a nicer output.

        found = False
        for rect in rects:
            (x, y, w, h) = rect
            #frame = cv2.rectangle(frame, (x, y), (x + w, y + h), (255, 0, 0), 1)   
            if(boundingBoxIntersect(bb, rect)):
                #rect = adjustBoundingBox(bb, rect)
                obj = {'x': rect[0], 'y': rect[1], 'width': rect[2], 'height': rect[3]}
                out.append(obj)
                          
                frame = cv2.rectangle(frame, (x, y), (x + w, y + h), (0, 0, 255), 1)                        
                             
                previousFrames.append([nextFrames[i], rect])
                found = True
                break
        
        
        if(not found):
            obj = {'x' : bb[0], 'y' : bb[1], 'width' : bb[2], 'height': bb[3]}
            out.append(obj)
            previousFrames.append([nextFrames[i], bb])
            (x, y, w, h) = bb
            frame = cv2.rectangle(frame, (x, y), (x + w, y + h), (0, 0, 255), 3)   
            
        cv2.imshow('img', frame)    
        cv2.waitKey(0)    
        
    return out
