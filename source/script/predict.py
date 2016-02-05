#!/usr/bin/env python

#Tracking person script
import argparse
import sys
import json
from tracking import *

## Parsing arguments
parser = argparse.ArgumentParser()
parser.add_argument("-x", nargs='+', type=int, help="bounding box x position")
parser.add_argument("-y", nargs='+', type=int, help="bounding box y position")
parser.add_argument("-width", nargs='+', type=int, help="bounding box width")
parser.add_argument("-height", nargs='+', type=int, help="bounding box height")
parser.add_argument('-camera', type=int, help="camera id")
parser.add_argument('-frames', nargs='+', default=[], help='Frames list')
parser.add_argument('-predict', nargs='+', default=[], help='Frames list')
args = parser.parse_args()

frames = []

if(len(args.x) != len(args.y) or len(args.width) != len(args.height) or len(args.frames) != len(args.x)):
    print('Error: invalid parameters')
    sys.exit()
else:
    
    out_file = open("test.txt","w")
    out_file.write("This Text is going to out file\nLook at it and see\n")
    out_file.close()
    
    for i in range(len(args.x)):
        frames.append([args.frames[i], (args.x[i], args.y[i], args.width[i], args.height[i])])
    
    #Forward prediction
    out = predictPerson(args.camera, frames, args.predict)
    print(json.dumps(out))
 