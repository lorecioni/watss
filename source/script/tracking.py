import cv2
import numpy as np
import os
import random
import os
from os import listdir
from os.path import isfile, join

TRAIN_SIZE = 40
TOLERANCE = 80

HOG_STRIDE = 8
HOG_PADDING = 32
HOG_SCALE = 1.05

class PedestrianTracking:
    def __init__(self, previousFrames, nextFrames, camera):
        """init the pedestrian object with track window coordinates"""
        path = os.path.abspath('/Applications/MAMP/htdocs/watss/source/frames/')
        imagesPath = join(path, str(camera) + '/')
        self.images = [join(imagesPath, f) for f in listdir(os.path.abspath(imagesPath)) if isfile(join(imagesPath, f))]
        self.nextImages = []
        self.previousImages = []
        self.previosuBB = []
        for i in range(len(previousFrames)):
            self.previousImages.append(join(path, previousFrames[i][0]))
            self.previosuBB.append(previousFrames[i][1])
            
        for j in range(len(nextFrames)):
            self.nextImages.append(join(path, nextFrames[j]))
        
        self.setup()
        
    
    def setup(self):
        #train background substractor
        self.trainBackgroundSubstractor()

        self.hog = cv2.HOGDescriptor()
        self.hog.setSVMDetector(cv2.HOGDescriptor_getDefaultPeopleDetector())
        
        bb = self.previosuBB[0]
        x, y , w, h = bb #first bounding box
        self.track_window = bb
        
        #getting first frame
        frame = cv2.imread(self.previousImages[0])
        # set up the kalman
        self.kalman = cv2.KalmanFilter(4, 2, 0)
        self.kalman.measurementMatrix = np.array([[1,0,0,0],[0,1,0,0]],np.float32)
        self.kalman.transitionMatrix = np.array([[1,0,1,0],[0,1,0,1],[0,0,1,0],[0,0,0,1]],np.float32)
        self.kalman.processNoiseCov = np.array([[1,0,0,0],[0,1,0,0],[0,0,1,0],[0,0,0,1]],np.float32) * 0.03
        self.measurement = np.array((2,1), np.float32)
        self.prediction = np.zeros((2,1), np.float32)
        
        self.center = None
        
        #initialization
        self.center = np.array([[np.float32(x + w/2)],[np.float32(y + h/2)]])
        
        for k in range(len(self.previousImages)):
            frame = cv2.imread(self.previousImages[k])
            self.track_window = self.previosuBB[k]
            (x, y, w, h) = self.track_window
            self.kalman.statePre[0,0]  = 200
            self.kalman.statePre[1,0]  = 100
            self.kalman.statePre[2,0]  = 0
            self.kalman.statePre[3,0]  = 0
            
            cv2.rectangle(frame, (x, y), (x + w, y + h), (0,255, 0),2)
            cv2.imshow('img', frame)    
            cv2.waitKey(0)    
            
                        
    def predict(self):
        out = []
        for k in range(len(self.nextImages)):
            print('image ' + str(k))
            frame = cv2.imread(self.nextImages[k])
            fgmask = self.bgs.apply(frame) 
     
            _, fgmask = cv2.threshold(fgmask, 200, 255, cv2.THRESH_BINARY)
            fgmask = cv2.dilate(fgmask, cv2.getStructuringElement(cv2.MORPH_RECT, (8,8)))
            fgmask = cv2.morphologyEx(fgmask, cv2.MORPH_OPEN, cv2.getStructuringElement(cv2.MORPH_RECT, (15,15)))
            image, contours, hierarchy = cv2.findContours(fgmask.copy(),cv2.RETR_TREE,cv2.CHAIN_APPROX_SIMPLE)

            (x, y, w, h) = self.track_window
                        #self.center = np.array([[np.float32(x + w/2)],[np.float32(y + h/2)]])
                        
            cv2.rectangle(frame, (x, y), (x + w, y + h), (0,255, 0),4) #green


            people = self.detectPeople(frame)
            best_people = None
            for person in people:
                (x, y, w, h) = person
                pad_w, pad_h = int(0.15*w), int(0.05*h)
                p = (x+pad_w, y+pad_h, w - pad_w, h - pad_h)
                intersect, score = self.boundingBoxIntersect(frame, self.track_window, p)
                if intersect:
                    if best_people != None:
                        if best_people[1] < score:
                            best_people = (person, score)
                    else:
                        best_people = (person, score)
                    
                    
            if best_people != None:
                print('Best people score: ' + str(best_people[1]))
                (x, y, w, h) = best_people[0]
                cv2.rectangle(frame, (x, y), (x + w, y + h), (255,0, 0),2) #blue
            
            best_contour = None    
            for c in contours:
                if cv2.contourArea(c) > 500:
                    rect = cv2.boundingRect(c) 
                    intersect, score = self.boundingBoxIntersect(frame, self.track_window, rect)
                    if intersect:
                        if best_contour != None:
                            if best_contour[1] < score:
                                best_contour = (rect, score)
                        else:
                            best_contour = (rect, score)
            
            if best_contour != None:
                print('Best contour score: ' + str(best_contour[1]))
                (x, y, w, h) = best_contour[0]
                cv2.rectangle(frame, (x, y), (x + w, y + h), (0,0, 255),2) #red
                        
            if(best_people != None and best_contour != None and best_people[1] > best_contour[1]):
                self.track_window = best_people[0]
            elif(best_people != None and best_contour != None and best_people[1] < best_contour[1]):
                self.track_window = best_contour[0]

            (x, y, w, h) = self.track_window
                        #self.center = np.array([[np.float32(x + w/2)],[np.float32(y + h/2)]])
                        
            #cv2.rectangle(frame, (x, y), (x + w, y + h), (255,255,255),2)
                                
                        #self.kalman.correct(self.center)
                        #prediction = self.kalman.predict()
                        #cv2.circle(frame, (int (prediction[0]), int(prediction[1])), 4, (0, 255, 0), 4)
            
            cv2.imshow('img', frame)    
            cv2.waitKey(0)            
            
        return out
    
    def detectPeople(self, frame):
        found, w = self.hog.detectMultiScale(frame, winStride=(8,8), padding=(32,64), scale=1.02)
        filtered = []
        for ri, r in enumerate(found):
            for qi, q in enumerate(found):
                if ri != qi and self.inside(r, q):
                    break
                else:
                    if not self.contains(filtered, r):
                        filtered.append(r) 
              
        return filtered

    def contains(self, list, bb):
        found = False
        rx, ry, rw, rh = bb
        for e in list:
            qx, qy, qw, qh = e
            if(rx == qx and ry == qy and rw == qw and rh == qh):
                found = True
        return found
    
    def inside(self, r, q):
        rx, ry, rw, rh = r
        qx, qy, qw, qh = q
        return rx > qx and ry > qy and rx + rw < qx + qw and ry + rh < qy + qh
    
    def boundingBoxIntersect(self, frame, r1, r2):
        (x1, y1, w1, h1) = r1
        (x2, y2, w2, h2) = r2
        
        if w2 < 30 or h2 < 50:
            return (False, 0)
        
        separate = (x1 + w1 < x2 or
            x1 > x2 + w2 or
            y1 > y2 + h2 or
            y1 + h1 < y2)
        compatible = (abs(w1 - w2) < TOLERANCE and
             abs(h1 - h2) < TOLERANCE)
        
        #Intersect area
        w = abs(w1 - abs(x1 - x2))
        h = abs(h1 - abs(h1 - h2))
        
        return ((not separate) and compatible, (w * h/(w1 * h1)))
    
    def trainBackgroundSubstractor(self):
        self.bgs = cv2.createBackgroundSubtractorMOG2()
            
        for i in range(len(self.previousImages)):
            frame = cv2.imread(self.previousImages[i])
            self.bgs.apply(frame)
            
        for i in range(TRAIN_SIZE):
            id = random.choice(range(len(self.images)));
            frame = cv2.imread(self.images[id])
            self.bgs.apply(frame)
            
        for i in range(len(self.nextImages)):
            frame = cv2.imread(self.nextImages[i])
            self.bgs.apply(frame)
        
    def __del__(self):
        print ('Pedestrian tracking destroyed')

   

def initializeHOGDescriptor():
    hog = cv2.HOGDescriptor()
    hog.setSVMDetector(cv2.HOGDescriptor_getDefaultPeopleDetector())
    return hog
    
def getDetections(bgs, frame):
    fgmask = bgs.apply(frame)

    _, fgmask = cv2.threshold(fgmask, 127, 255, cv2.THRESH_BINARY)
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
