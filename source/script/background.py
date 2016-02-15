import cv2
import numpy as np
import os
import random
from os import listdir
from os.path import isfile, join

TRAIN_SIZE = 20

def trainBackgroundSubstractorMOG(images):
    bgs = cv2.createBackgroundSubtractorMOG2()
    for i in range(TRAIN_SIZE):
        id = random.choice(range(len(images)));
        frame = cv2.imread(images[id])
        bgs.apply(frame)
    return bgs

def saveMotionImages(camera):  
    path = os.path.abspath('/Applications/MAMP/htdocs/watss/source/frames/')
    imagesPath = os.path.join(path, str(camera) + '/')
    images = [os.path.join(imagesPath, f) for f in os.listdir(os.path.abspath(imagesPath)) if os.path.isfile(os.path.join(imagesPath, f))]
    
    bgs = trainBackgroundSubstractorMOG(images)

    out = []
    
    for i in range(len(images)):                
        frame = cv2.imread(images[i])
        mask = bgs.apply(frame.copy())
        _, mask = cv2.threshold(mask, 200, 255, cv2.THRESH_BINARY) 
        
        frame = cv2.resize(frame, (600, 420), cv2.INTER_NEAREST) 
        mask = cv2.resize(mask, (600, 420), cv2.INTER_NEAREST) 
        
        motion = np.zeros((mask.shape[0], mask.shape[1], 3), np.uint8)
        motion[..., 0] = mask;
        motion[..., 1] = mask;
        motion[..., 2] = mask;
            
        
        out = np.hstack((frame, motion))    
        cv2.imshow('img', out)    
        cv2.waitKey(100)    
        
    return out

saveMotionImages(1)