#Tracking functions
import numpy as np
import cv2
import os
from os import listdir
from os.path import isfile, join
import sys
import argparse
from tracking import *

parser = argparse.ArgumentParser()
parser.add_argument("-x", type=int, help="bounding box x position")
parser.add_argument("-y", type=int, help="bounding box y position")
parser.add_argument("-width", type=int, help="bounding box width")
parser.add_argument("-height", type=int, help="bounding box height")
parser.add_argument('-frame', action='append', dest='frames', default=[], help='Frames list',)
args = parser.parse_args()

bb = (args.x, args.y, args.width, args.height)

path = os.path.abspath('../frames/1/')
images = [f for f in listdir(os.path.abspath(path)) if isfile(join(path, f))]
bgs = trainBackgroundSubstractorMOG(images)

for i in range(len(args.frames)):
    filename = os.path.abspath('../frames/1/' + str(args.frames[i]))
    frame = cv2.imread(filename)
    rects = getDetections(bgs, frame)
    
    for rect in rects:
        (x, y, w, h) = rect
        frame = cv2.rectangle(frame, (x, y), (x + w, y + h), 255, 2)

    cv2.imshow('img', frame)    
    cv2.waitKey(0)
        
