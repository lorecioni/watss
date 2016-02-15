import cv2
import numpy as np
import os
import random

IMAGES_PATH = '../frames/'
OUTPUT_PATH = '../../results/'
OUTPUT_SIZE = (600, 420)
TRAIN_SIZE = 40
LIMIT = 200

def trainBackgroundSubstractorMOG(images):
    bgs = cv2.createBackgroundSubtractorMOG2()
    for i in range(TRAIN_SIZE):
        id = random.choice(range(len(images)));
        frame = cv2.imread(images[id])
        bgs.apply(frame)
    return bgs

def saveMotionImages(camera):  
    path = os.path.abspath(IMAGES_PATH)
    imagesPath = os.path.join(path, str(camera) + '/')
    images = [os.path.join(imagesPath, f) for f in os.listdir(os.path.abspath(imagesPath)) if os.path.isfile(os.path.join(imagesPath, f))]
    
    print('Training of background substractor')
    bgs = trainBackgroundSubstractorMOG(images)
    
    for i in range(LIMIT):                
        frame = cv2.imread(images[i])
        mask = bgs.apply(frame.copy())
        _, mask = cv2.threshold(mask, 200, 255, cv2.THRESH_BINARY) 
        
        frame = cv2.resize(frame, OUTPUT_SIZE, cv2.INTER_NEAREST) 
        mask = cv2.resize(mask, OUTPUT_SIZE, cv2.INTER_NEAREST) 
        
        motion = np.zeros((mask.shape[0], mask.shape[1], 3), np.uint8)
        motion[..., 0] = mask;
        motion[..., 1] = mask;
        motion[..., 2] = mask;
            
        out = np.hstack((frame, motion))
        
        filename = os.path.join(os.path.abspath(OUTPUT_PATH), "frame_mog_" + str(i) + ".jpg")  
        cv2.imwrite(filename, out) 
        print('Exported images ' + str(i + 1)) 
        
    print('Success!')

saveMotionImages(1)