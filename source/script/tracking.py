'''Pedestrian tracking utilities for generating annotation proposals'''
import cv2
import numpy as np
import os
import random
import configparser

'''Pedestrian tracker configuration'''
config = configparser.RawConfigParser()
configPath = './trackingconf.conf'
config.read(configPath)
section = 'options'

try: 
    FRAMES_PATH = config.get(section, 'FRAMES_PATH')
except Exception:
    FRAMES_PATH = '../frames/'

#Tracking options
try: 
    USE_MOTION = config.getboolean(section, 'USE_MOTION')
except Exception:
    USE_MOTION = True
try: 
    USE_PEDESTRIAN_DETECTOR = config.getboolean(section, 'USE_PEDESTRIAN_DETECTOR')
except Exception:
    USE_PEDESTRIAN_DETECTOR = True    
try: 
    USE_KALMAN_FILTER = config.getboolean(section, 'USE_KALMAN_FILTER')
except Exception:
    USE_KALMAN_FILTER = True      

#Training size for MOG background substractor
try: 
    TRAIN_SIZE = config.getint(section, 'TRAIN_SIZE')
except Exception:
    TRAIN_SIZE = 40
    
#Tolerance for considering two bb similar
try: 
    TOLERANCE = config.getfloat(section, 'TOLERANCE')
except Exception:
    TOLERANCE = 1.5

try: 
    BOUNDING_BOX_TOLERANCE = config.getfloat(section, 'BOUNDING_BOX_TOLERANCE')
except Exception:
    BOUNDING_BOX_TOLERANCE = 0.5
    
#Padding for the current window
try: 
    WINDOW_OFFSET = config.getint(section, 'WINDOW_OFFSET')
except Exception:
    WINDOW_OFFSET = 150

#Showing result frames
try: 
    DISPLAY_RESULT = config.getboolean(section, 'DISPLAY_RESULT')
except Exception:
    DISPLAY_RESULT = False    
try: 
    DISPLAY_TEXT = config.getboolean(section, 'DISPLAY_TEXT')
except Exception:
    DISPLAY_TEXT = False  

#Minimum and maximum bounding box dimension
try: 
    MIN_BB_WIDTH = config.getint(section, 'MIN_BB_WIDTH')
except Exception:
    MIN_BB_WIDTH = 30
try: 
    MIN_BB_HEIGHT = config.getint(section, 'MIN_BB_HEIGHT')
except Exception:
    MIN_BB_HEIGHT = 50

#HOG people detector configuration
try: 
    HOG_STRIDE = config.getint(section, 'HOG_STRIDE')
except Exception:
    HOG_STRIDE = 8
try: 
    HOG_PADDING = config.getint(section, 'HOG_PADDING')
except Exception:
    HOG_PADDING = 8
try: 
    HOG_SCALE = config.getfloat(section, 'HOG_SCALE')
except Exception:
    HOG_SCALE = 1.05

FONT = cv2.FONT_HERSHEY_SIMPLEX
FONT_SIZE = 1

LEGEND_POSITION_X = 50
LEGEND_POSITION_Y = 50

'''Pedestrian tracking class'''
class PedestrianTracking:
    
    '''Initializing of the pedestrian tracker'''
    def __init__(self, previousFrames, nextFrames, camera):
        path = os.path.abspath(FRAMES_PATH)
        #Retrieving frames list
        imagesPath = os.path.join(path, str(camera) + '/')
        self.images = [os.path.join(imagesPath, f) 
            for f in os.listdir(os.path.abspath(imagesPath)) 
            if os.path.isfile(os.path.join(imagesPath, f))]
        self.nextImages = []
        self.previousImages = []
        self.previosuBB = []
        for i in range(len(previousFrames)):
            self.previousImages.append(os.path.join(path, previousFrames[i][0]))
            self.previosuBB.append(previousFrames[i][1])
        for j in range(len(nextFrames)):
            self.nextImages.append(os.path.join(path, nextFrames[j]))     
        self.setup()
         
    '''Initializing of the pedestrian tracker'''
    def setup(self):
        #Train background substractor
        self.trainBackgroundSubstractor()
        #Initializing HOG descriptor for people detection
        self.hog = cv2.HOGDescriptor()
        self.hog.setSVMDetector(cv2.HOGDescriptor_getDefaultPeopleDetector())

        #Set up the Kalman Filter
        self.kalman = cv2.KalmanFilter(5, 3, 0)
        
        #State: [x, y, v_x, v_y, ratio]
        #Measurement: [x, y, ratio]
        self.kalman.measurementMatrix = np.array([[1,0,0,0,0],[0,1,0,0,0],[0,0,0,0,0]],np.float32)
        self.kalman.transitionMatrix = np.array([[1,0,1,0,0],[0,1,0,1,0],[0,0,1,0,0],[0,0,0,1,0],[0,0,0,0,1]],np.float32)
        self.kalman.processNoiseCov = np.array([[1e-2,0,0,0,0],[0,1e-2,0,0,0],[0,0,20,0,0],[0,0,0,20,0],[0,0,0,0,20]],np.float32)
        self.kalman.measurementNoiseCov = self.kalman.measurementNoiseCov * 2
        self.kalman.errorCovPre = np.identity(5, np.float32) 

        self.prediction = np.zeros((3,1), np.float32)
        self.measurement = np.zeros((3,1), np.float32)

        #Elaborating previous frames (generating Kalman history)
        for k in range(len(self.previousImages)):
            frame = cv2.imread(self.previousImages[k])   
            #Current bounding box
            self.track_window = self.previosuBB[k]
            x, y , w, h = self.track_window 
            #Adding previous state to the kalman filter
            if k == 0:
                self.kalman.statePre = np.array([[np.float32(x + w/2)], [np.float32(y + h/2)], [0.], [0.], [np.float32(64/128)]], np.float32)     
            #else:    
            self.measurement = np.array([[np.float32(x + w/2)], [np.float32(y + h/2)], [np.float32(w/h)]])
            self.kalman.correct(self.measurement)

    '''Predicting person position based on motion, people detection and Kalman filter'''
    def predict(self):
        out = []
        for k in range(len(self.nextImages)):
            frame = cv2.imread(self.nextImages[k])
            height, width, channels = frame.shape
            #Generating current window
            self.getCurrentWindow(width, height)        
            
            if USE_MOTION:
                (c, r, w, h) = self.window
                roi = frame[r:r+h, c:c+w]  
                fgmask = self.bgs.apply(frame) 
    
                #Mask preprocessing, removing noise
                _, fgmask = cv2.threshold(fgmask, 200, 255, cv2.THRESH_BINARY) 
                
                #cv2.imshow('img', fgmask)
                #cv2.waitKey(0)
                
                fgmask = cv2.morphologyEx(fgmask, cv2.MORPH_OPEN, cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (2,2)))
                fgmask = cv2.dilate(fgmask, cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (15,20)))
                fgmask = cv2.morphologyEx(fgmask, cv2.MORPH_OPEN, cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (10,10)))
               # fgmask = cv2.morphologyEx(fgmask, cv2.MORPH_CLOSE, cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (8,8)))
                #cv2.imshow('img', fgmask)
                #cv2.waitKey(0)
                image, contours, hierarchy = cv2.findContours(fgmask.copy(),cv2.RETR_TREE,cv2.CHAIN_APPROX_SIMPLE)            
            
            (x, y, w, h) = self.track_window
            (wx, wy, ww, wh) = self.window
            
            if DISPLAY_RESULT:            
                cv2.rectangle(frame, (x, y), (x + w, y + h), (0, 255, 0), 2)                    
                
            best_people = None
            best_contour = None
            
            motion_score = 0
            people_detector_score = 0

            if USE_PEDESTRIAN_DETECTOR:
                #Detect people on the current window
                people = self.detectPeople(roi)     
                for person in people:
                    (x, y, w, h) = person
                    #Adjusting detection
                    pad_w, pad_h = int(0.15 * w), int(0.05 * h)
                    p = (wx + x + pad_w, wy + y + pad_h, w - pad_w, h - pad_h)
                    intersect, score = self.boundingBoxIntersect(frame, self.track_window, p)
                    if intersect:
                        if best_people != None:
                            if best_people[1] < score:
                                best_people = (p, score)
                        else:
                            best_people = (p, score)
                
                if DISPLAY_RESULT:
                    if best_people != None:
                        #print('Best person detected: ' + str(best_people[1]))
                        people_detector_score = best_people[1]
                        (x, y, w, h) = best_people[0]
                        cv2.rectangle(frame, (x, y), (x + w, y + h), (255, 0, 0), 2)                            
            
            if USE_MOTION:
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
                
                if DISPLAY_RESULT:
                    if best_contour != None:
                        #print('Best contour detected: ' + str(best_contour[1]))
                        motion_score = best_contour[1]
                        (x, y, w, h) = best_contour[0]
                        cv2.rectangle(frame, (x, y), (x + w, y + h), (0, 0, 255), 2)
                            
            
            result = self.track_window
            found = False
            if(best_people != None and best_contour != None and best_people[1] > best_contour[1]):
                result = best_people[0]
                found = True
            elif(best_people != None and best_contour != None and best_people[1] < best_contour[1]):
                result = best_contour[0]
                found = True
            elif(best_people != None and best_contour == None):
                result = best_people[0]
                found = True
            elif(best_people == None and best_contour != None):
                result = best_contour[0]
                found = True

            prediction = self.kalman.predict() 
            (x, y, w, h) = result
            
            if(found):
                #Using people detector/motion   
                if USE_KALMAN_FILTER:      
                    self.measurement = np.array([[np.float32(x + w/2)],[np.float32(y + h/2)], [np.float32(w/h)]])
                    self.kalman.correct(self.measurement)
                    if DISPLAY_RESULT:
                        cv2.circle(frame, (int (prediction[0]), int(prediction[1])), 4, (0, 153, 255), 4)
                        print (prediction[2])
                self.track_window = result
            else:
                if USE_KALMAN_FILTER:
                    #Using Kalman prediction
                    self.kalman.statePost = prediction
                    self.track_window = (int(prediction[0] - w/2), int(prediction[1] - h/2), w, h)
                    if DISPLAY_RESULT:
                        cv2.circle(frame, (int (prediction[0]), int(prediction[1])), 4, (0, 153, 255), 4)
                        print (prediction[2])            
                       
            (x, y, w, h) = self.track_window
            obj = {'x' : int(x), 'y' : int(y), 'width' : int(w), 'height': int(h)}
            out.append(obj)
            
            if DISPLAY_TEXT:
                using_kalman = ''
                if (motion_score == 0 and people_detector_score == 0):
                    using_kalman = 'ACTIVE'
    
                cv2.putText(frame, 'previous', (LEGEND_POSITION_X, LEGEND_POSITION_Y), FONT, FONT_SIZE, (0, 255, 0), 1)  
                cv2.putText(frame, 'people detector  {0:.2f}'.format(people_detector_score), (LEGEND_POSITION_X, LEGEND_POSITION_Y + 30), FONT, FONT_SIZE, (255, 0, 0), 1)
                cv2.putText(frame, 'kalman filter ' + using_kalman , (LEGEND_POSITION_X, LEGEND_POSITION_Y + 90), FONT, FONT_SIZE, (0, 153, 255), 1)
                cv2.putText(frame, 'motion {0:.2f}'.format(motion_score), (LEGEND_POSITION_X, LEGEND_POSITION_Y + 60), FONT, FONT_SIZE, (0, 0, 255), 1)
            
            if DISPLAY_RESULT:
                #cv2.rectangle(frame, (x, y), (x + w, y + h), (255, 0, 0), 2)             
                cv2.imshow('img', frame)    
                cv2.waitKey(0)   
                

                     
            
        return out
    
    '''Returns a list of detections of people'''
    def detectPeople(self, frame):
        found, w = self.hog.detectMultiScale(frame, winStride=(HOG_STRIDE, HOG_STRIDE), padding=(HOG_PADDING, HOG_PADDING), scale=HOG_SCALE)
        filtered = []
        for ri, r in enumerate(found):
            for qi, q in enumerate(found):
                if ri != qi and self.inside(r, q):
                    break
                else:
                    if not self.contains(filtered, r):
                        filtered.append(r) 
              
        return filtered

    '''Methods over bounding boxes'''
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
    
    '''Returning True if two bounding boxes intersect and evaluating score (based on intersect area)'''
    def boundingBoxIntersect(self, frame, r1, r2):
        (x1, y1, w1, h1) = r1
        (x2, y2, w2, h2) = r2
        
        if w2 < MIN_BB_WIDTH or h2 < MIN_BB_HEIGHT:
            return (False, 0)
        separate = (x1 + w1 < x2 or
            x1 > x2 + w2 or
            y1 > y2 + h2 or
            y1 + h1 < y2)
                
        compatible = (abs((w1*h1)/(w2*h2)) < TOLERANCE)
        #Intersect area
        w = abs(w1 - abs(x1 - x2))
        h = abs(h1 - abs(h1 - h2))
        
        intersectArea = w * h
        unionArea = (w1 * h1) + (w2 * h2) - intersectArea
        score = intersectArea/unionArea
        
        if (score < BOUNDING_BOX_TOLERANCE):
            return (False, 0)
        else:
        #return ((not separate) and compatible, (w * h/(w1 * h1)))
            return ((not separate) and compatible, score)
    
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
    
    '''Generating current window (bounding box and padding)'''
    def getCurrentWindow(self, maxw, maxh):
        (x, y, w, h) = self.track_window
        if x >= WINDOW_OFFSET:
            x = x - WINDOW_OFFSET
        else:
            x = 0
        if y >= WINDOW_OFFSET:
            y = y - WINDOW_OFFSET
        else:
            y = 0
        if x + w + WINDOW_OFFSET*2 < maxw:
            w = w + WINDOW_OFFSET*2
        else:
            w = w + abs(maxw - (x + w))
        if y + h + WINDOW_OFFSET*2 < maxh:
            h = h + WINDOW_OFFSET*2    
        else:
            h = h + abs(maxh - (y + h))           
        self.window = (x, y, w, h)
         
    '''Train MOG background substractor'''
    def trainBackgroundSubstractor(self):
        self.bgs = cv2.createBackgroundSubtractorMOG2()         
        for i in range(len(self.previousImages)):
            frame = cv2.imread(self.previousImages[i])
            self.bgs.apply(frame)
        for i in range(TRAIN_SIZE):
           # id = random.choice(range(len(self.images)));
            frame = cv2.imread(self.images[i])
            self.bgs.apply(frame)
        for i in range(len(self.nextImages)):
            frame = cv2.imread(self.nextImages[i])
            self.bgs.apply(frame)
        