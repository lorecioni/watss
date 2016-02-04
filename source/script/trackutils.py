from models import Video, Path, Box
import tracking.base

def is_intersection((x1, y1, w1, h1), (x2, y2, w2, h2)):
    separate = (x1 + w1 < x2 or
        x1 > x2 + w2 or
        y1 > y2 + h2 or
        y1 + h1 < y2)
    return not separate

# List of detections
# A detection is a tuple of the form (x, y, w, h)
def get_detections(bgs, frame):
    fgmask = bgs.apply(frame)
    _, fgmask = cv2.threshold(fgmask, 200, 255, cv2.THRESH_BINARY)
    fgmask = cv2.dilate(fgmask, cv2.getStructuringElement(cv2.MORPH_RECT, (4,4)))
    fgmask = cv2.erode(fgmask, cv2.getStructuringElement(cv2.MORPH_RECT, (2,2)))
    contours, hierarchy = cv2.findContours(fgmask, cv2.RETR_TREE, cv2.CHAIN_APPROX_SIMPLE)
    detections = []
    for contour in contours:
        detections.append(cv2.boundingRect(contour))

    filtered_detections = []
    for rect in detections:
        x,y,w,h = rect
        if w > 15 and h > 15:
            #intersects = [r for r in detections if is_intersection(rect, r)]
            #if len(intersects) <= 1:
            cv2.rectangle(fgmask, (x,y), (x+w,y+h), 255, 3)
            filtered_detections.append(rect)
    cv2.imshow("test", fgmask)
    cv2.waitKey(10)
    return filtered_detections

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

def clean_paths(paths):
    return [path for path in paths if len(path) > 3]

def convert_to_db(paths, job, label):
    paths = clean_paths(paths)
    paths_db = []
    for path in paths:
        path_db = Path(job = job, label=label)
        for box in path:
            box_db = Box(path = path_db)
            box_db.xtl = box['rect'][0]
            box_db.ytl = box['rect'][1]
            box_db.xbr = box['rect'][0] + box['rect'][2]
            box_db.ybr = box['rect'][1] + box['rect'][3]
            box_db.frame = box['frame']
            box_db.outside = 0 if box['visible'] else 1
            box_db.occluded = 0
            path_db.boxes.append(box_db)

        paths_db.append(path_db)
    return paths_db

def totrackpaths(paths):
    convertedpaths = {}
    for path in paths:
        boxes = path.getboxes()
        convertedpaths[path.userid] = tracking.base.Path(
            id=path.userid,
            label=path.label,
            boxes={box.frame: box for box in boxes}
        )
    return convertedpaths

def fromtrackpath(path, job, start, stop):
    newpath = Path(job = job)
    frames = sorted(path.boxes.keys())
    laststored = None
    blowradius = job.segment.video.blowradius
    for frame in frames:
        if frame != start and frame != stop:
            box = path.boxes[frame]
            if laststored is None or frame - laststored > blowradius:
                newpath.boxes.append(tovaticbox(newpath, box))
                laststored = frame
            elif box.lost or box.occluded:
                newpath.boxes[-1] = tovaticbox(newpath, box)
                laststored = frame
    return newpath

def tovaticbox(path, box):
    newbox = Box()
    newbox.frombox(box)
    return newbox

def get_paths(v):
    video = v
    for segment in video.segments:
        # Each path is a list of boxes where a box is {'rect':r, 'frame':frame}
        bgs = cv2.BackgroundSubtractorMOG()
        paths = []
        job = segment.jobs[0]
        for frame_num in range(segment.start, segment.stop):
            print "Processing frame: {0}".format(frame_num)
            image_location = Video.getframepath(frame_num, video.location)
            frame = cv2.imread(image_location, cv2.IMREAD_COLOR)
            rects = get_detections(bgs, frame)
            match_rects_to_paths(rects, paths, frame_num)
        convert_to_db(paths, job, video.labels[0])
