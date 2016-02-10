import cv2
import numpy as np
import os
import random
import os
from os import listdir
from os.path import isfile, join

TRAIN_SIZE = 40
TOLERANCE = 80
DELTA = 150

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
        height, width, channels = frame.shape
        
        #Generating current window
        self.getCurrentWindow(width, height)
        
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
            (x, y, w, h) = self.track_window
            
            self.kalman.statePre[0]  = 200
            self.kalman.statePre[1]  = 100
#             self.kalman.statePre[2]  = 0
#             self.kalman.statePre[3]  = 0
            
    def predict(self):
        out = []
        for k in range(len(self.nextImages)):
            frame = cv2.imread(self.nextImages[k])
            (c, r, w, h) = self.window
            roi = frame[r:r+h, c:c+w]  
            fgmask = self.bgs.apply(frame) 

            _, fgmask = cv2.threshold(fgmask, 200, 255, cv2.THRESH_BINARY) 
            fgmask = cv2.dilate(fgmask, cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (8,8)))
            fgmask = cv2.morphologyEx(fgmask, cv2.MORPH_OPEN, cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (15,20)))
            image, contours, hierarchy = cv2.findContours(fgmask.copy(),cv2.RETR_TREE,cv2.CHAIN_APPROX_SIMPLE)
            
            (x, y, w, h) = self.track_window
            (wx, wy, ww, wh) = self.window
            #self.center = np.array([[np.float32(x + w/2)],[np.float32(y + h/2)]])
            
                        
            cv2.rectangle(frame, (x, y), (x + w, y + h), (0,255, 0),4) #green

            people = self.detectPeople(roi)
            
            best_people = None
            for person in people:
                (x, y, w, h) = person
                pad_w, pad_h = int(0.15*w), int(0.05*h)
                p = (wx + x + pad_w, wy + y + pad_h, w - pad_w, h - pad_h)
                intersect, score = self.boundingBoxIntersect(frame, self.track_window, p)
                if intersect:
                    if best_people != None:
                        if best_people[1] < score:
                            best_people = (p, score)
                    else:
                        best_people = (p, score)
                    
                    
            if best_people != None:
                (x, y, w, h) = best_people[0]
                #cv2.rectangle(frame, (x, y), (x + w, y + h), (255,0, 0),2) #blue
            
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
                (x, y, w, h) = best_contour[0]
                #cv2.rectangle(frame, (x, y), (x + w, y + h), (0,0, 255),2) #red
                        
            result = None
            if(best_people != None and best_contour != None and best_people[1] > best_contour[1]):
                result = best_people[0]
            elif(best_people != None and best_contour != None and best_people[1] < best_contour[1]):
                result = best_contour[0]
            elif(best_people != None and best_contour == None):
                result = best_people[0]
            elif(best_people == None and best_contour != None):
                result = best_contour[0]
            else:
                result = self.track_window

            self.track_window = self.adjustBoundingBox(result)
            
            (x, y, w, h) = self.track_window
            cv2.rectangle(frame, (x, y), (x + w, y + h), (255,0,0),2)
            
            obj = {'x' : x, 'y' : y, 'width' : w, 'height': h}
            out.append(obj)
            
            #cv2.rectangle(frame, (x, y), (x + w, y + h), (0, 0, 255), 3)  
            #self.center = np.array([[np.float32(x + w/2)],[np.float32(y + h/2)]])        
                                
            #self.kalman.correct(self.center)
            #prediction = self.kalman.predict()
            #cv2.circle(frame, (int (prediction[0]), int(prediction[1])), 4, (0, 255, 0), 4)
            
            #cv2.imshow('img', frame)    
            #cv2.waitKey(0)            
            
        return out
    
    def detectPeople(self, frame):
        found, w = self.hog.detectMultiScale(frame, winStride=(8,8), padding=(8,16), scale=1.02)#todo
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
    
    '''Try to ajdust bounding box dimensione based on previous detection'''
    def adjustBoundingBox(self, bb):
        (cx, cy, cw, ch) = self.track_window
        (x, y, w, h) = bb
        (ox, oy, ow, oh) = bb
        if abs(cw - w) > TOLERANCE/2:
            ox, oy, ow = (cx + x)/2, (cy + y)/2, min([cw, w]) + TOLERANCE/4
        if abs(ch - h) > TOLERANCE/2:
            ox, oy, oh = (cx + x)/2, (cy + y)/2, min([ch, h]) + TOLERANCE/4    
        return (int(ox), int(oy), int(ow), int(oh))      
    
    def getCurrentWindow(self, maxw, maxh):
        (x, y, w, h) = self.track_window
        if x >= DELTA:
            x = x - DELTA
        else:
            x = 0
        if y >= DELTA:
            y = y - DELTA
        else:
            y = 0
        if x + w + DELTA*2 < maxw:
            w = w + DELTA*2
        else:
            w = w + abs(maxw - (x + w))
        if y + h + DELTA*2 < maxh:
            h = h + DELTA*2    
        else:
            h = h + abs(maxh - (y + h))           
        self.window = (x, y, w, h)
         
    
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
        