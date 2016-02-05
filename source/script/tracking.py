
TRAIN_SIZE = 40

def trainBackgroundSubstractorMOG(frames):
    bgs = cv2.createBackgroundSubtractorMOG2()
    for i in range(TRAIN_SIZE):
        id = random.choice(range(len(frames)));
        filename = os.path.abspath('../frames/1/' + str(frames[id]))
        frame = cv2.imread(filename)
        bgs.apply(frame)
        print('processed ' + str(id))
    return bgs
    

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