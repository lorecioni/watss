#Tracking functions

import os
import numpy as np
import cv2

#debug
path1 = os.path.abspath('../frames/1/16h21m58s_3870.jpg')
path2 = os.path.abspath('../frames/1/16h21m5s_3871.jpg')
print(path1)

img = cv2.imread(path1)
#img = cv2.imread(path1)
cv2.imshow('image',img)
cv2.waitKey(0)
cv2.destroyAllWindows()
#print(img)
