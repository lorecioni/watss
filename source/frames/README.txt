## MuseumVisitors: a dataset for pedestrian and group detection, gaze estimation and behavior understanding ##

MuseumVisitors is a new dataset, under construction, acquired inside the National Museum of Bargello in
Florence. It was recorded with three IP cameras at a resolution of 1280 × 800 pixels and an average framerate of
five frames per second. Sequences were recorded following two scenarios. The first scenario consists of visitors 
watching different artworks (individuals), while the second one consists of groups of visitors watching the same 
artworks (groups). This dataset is specifically designed to support research on group detection, occlusion handling,
 tracking, re-identification and behavior analysis.  
In order to assess the difficulties of this dataset we have also performed some tests exploiting seven representative
 state-of-the-art pedestrian detectors. The results are showed in the paper (see below, CITE section).


CONTENTS:
- /frames: contains the labeled frames acquired in the National Museum of Bargello. The images are grouped with rescpect to the three cameras [1-4].
- annotations.csv: contains all annotations of the visitors in the frames
- README: The file you are reading now :)...


[*annotations.csv*]
The annotations are stored in this csv file. In particular, each row contains the follow fields, separated by commas:
  visitorid, frameid, cameraid, bb_x, bb_y, bb_width, bb_height, bbV_x, bbV_y, bbV_width, bbV_height, bbV_height, gazeAngle_x, gazeAngle_y, filename, operaid, groupid
  
  where:
   -  visitorid: id of the person
   - [cameraid, frameid ]: indentifies uniquely a frame in the camera
   - [bb_x, bb_y, bb_width , bb_height]: full bounding boxes of the visitor (included the occlusion area)
   - [bbV_x, bbV_y, bbV_width, bbV_height, bbV_height]: bounding boxes of the visitor (only the visible part of the people)
   - [gazeAngle_face, gazeAngle_face_z]: head gaze
   - [gazeAngle_body, gazeAngle_body_z]: body gaze
   - filename: partial path to the specific image in the folder '/frames'. 
   - poiid: poi under observation
   - groupid: group belonging


[WATSS: a Web Annotation Tool for Surveillance Scenarios]
In order to ease the annotation process we designed the user friendly web 
interface WATSS, that allows to annotate: bounding boxes, occlusion area, body orientation and head gaze, 
group belonging, and artwork under observation.

You can try WATSS at http://150.217.35.152/watss, using Name=Guest


[CITE]
If you use this dataset in a publication, We would be greateful if you cite:

@InProceedings\{BLSKD15,
  author       = "Bartoli, Federico and Lisanti, Giuseppe and Seidenari, Lorenzo and Karaman, Svebor and Del Bimbo, Alberto",
  title        = "MuseumVisitors: a dataset for pedestrian and group detection, gaze estimation and behavior understanding",
  booktitle    = "Proc. of CVPR Int'l. Workshop on Int.’l Workshop on Group And Crowd Behavior Analysis And Understanding",
  year         = "2015",
  url          = "http://www.micc.unifi.it/publications/2015/BLSKD15"
}
