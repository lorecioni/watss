#Tracking functions
import numpy as np
import cv2
import os
import pickle
from os import listdir
from os.path import isfile, join

def trainBackgroundSubstractorMOG(frames):
    bgs = cv2.createBackgroundSubtractorMOG2()
    for i in range(20, 40):
        filename = os.path.abspath('../frames/1/' + str(frames[i]))
        frame = cv2.imread(filename)
        bgs.apply(frame)
        print('processed ' + str(i))
    return bgs
    

def match_rects_to_paths(rects, paths, frame_num):
    for rect in rects:
        added_path = False
        waiting = False
        for path in paths:
            # Check if we can add rect to path
            last_box = path[-1]
            if (abs(last_box['rect'][0] - rect[0]) < 20 and
                abs(last_box['rect'][1] - rect[1]) < 20 and
                (frame_num - last_box['frame']) < 15):
                if (frame_num - last_box['frame']) > 3:
                    path.append({'visible':True, 'rect':rect, 'frame':frame_num})
                    added_path = True
                else:
                    waiting = True
        if not added_path and not waiting:
            # Create new path
            new_path = []
            if frame_num > 0:
                new_path.append({'visible':False, 'rect':rect, 'frame':0})
            new_path.append({'visible':True, 'rect':rect, 'frame':frame_num})
            paths.append(new_path)
    return paths

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


path = os.path.abspath('../frames/1/')
frames = [f for f in listdir(os.path.abspath(path)) if isfile(join(path, f))]

bb = (1152, 192, 80, 158)


bgs = trainBackgroundSubstractorMOG(frames)

paths = []

for i in range(20, len(frames)):
    filename = os.path.abspath('../frames/1/' + str(frames[i]))
    frame = cv2.imread(filename)
    rects = getDetections(bgs, frame)
    
    for rect in rects:
        (x, y, w, h) = rect
        frame = cv2.rectangle(frame, (x, y), (x + w, y + h), 255, 2)

    paths = match_rects_to_paths(rects, paths, i)
    print(paths)
    cv2.imshow('img', frame)    
    cv2.waitKey(0)
        
