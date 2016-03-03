import cv2
import numpy as np
import os
import configparser
import pymysql

config = configparser.RawConfigParser()
configPath = './trackingconf.conf'
config.read(configPath)
section = 'options'


try: 
    FRAMES_PATH = config.get(section, 'FRAMES_PATH')
except Exception:
    FRAMES_PATH = '../frames/'
      
connection = pymysql.connect(
        host='localhost',
        user='watss',
        password='watss',
        db='watss',
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor)

outputDirectory = '../img/validation'

def validateAnnotations(camera): 
    path = os.path.abspath(FRAMES_PATH)
    #Retrieving frames list
    imagesPath = os.path.join(path, str(camera) + '/')
    images = [os.path.join(imagesPath, f) 
        for f in os.listdir(os.path.abspath(imagesPath)) 
            if os.path.isfile(os.path.join(imagesPath, f))]
    
    people = []
    cameras = []
    
    if not os.path.exists(outputDirectory):
        os.makedirs(outputDirectory)
        
    try:
        with connection.cursor() as cursor:
            # Fetching cameras
            sql = "SELECT cameraid FROM `cameras`"
            cursor.execute(sql)
            result = cursor.fetchall()
            for i in range(len(result)):
                cam = result[i]['cameraid']
                cameras.append(cam)
     
            # Fetching people ids
            sql = "SELECT DISTINCT peopleid FROM people"
            cursor.execute(sql)
            result = cursor.fetchall()
            for i in range(len(result)):
                person = result[i]['peopleid']
                people.append(person)
                personDir = outputDirectory + '/person_' + str(person)
                if not os.path.exists(personDir):
                    os.makedirs(personDir)
                
                for j in range(len(cameras)):
                    cameraDir = personDir + '/camera_' + str(cameras[j])
                    if not os.path.exists(cameraDir):
                        os.makedirs(cameraDir)
               
            sql = "SELECT f.path, f.cameraid, f.frameid, p.peopleid, p.bb_x, p.bb_y, p.bb_width, p.bb_height FROM people AS p LEFT JOIN frames AS f ON p.frameid = f.frameid  AND p.cameraid = f.cameraid"
            cursor.execute(sql)
            result = cursor.fetchall()
            
            print('Total annotations: ' + str(len(result)))
            
            for i in range(len(result)):
                person = result[i]['peopleid']
                path = result[i]['path']
                fid = result[i]['frameid']
                cam = result[i]['cameraid']
                x = result[i]['bb_x']
                y = result[i]['bb_y']
                w = result[i]['bb_width']
                h = result[i]['bb_height']
                
                frame = cv2.imread('../frames/' + path)
                cropped = frame[y:y+h, x:x+w]
            
                filename = 'frame_' + path.split('_')[1] 
                filename = outputDirectory + '/' + 'person_' + str(person) + '/camera_' + str(cam) + '/' + filename
                cv2.imwrite(filename, cropped)
                
                percent = (i + 1) * 100 / len(result)
                print('Processed ' + str(i + 1) + ' images, {0:.2f}%'.format(percent))
         
            
    finally:
        connection.close()
        
validateAnnotations(1)
