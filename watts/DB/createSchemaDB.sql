SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


CREATE TABLE IF NOT EXISTS `poi` (
  `poiid` varchar(11) NOT NULL , 
  `cameraid` varchar(11) NOT NULL , 
  `location_x` int(11) NOT NULL ,
  `location_y` int(11) NOT NULL ,
  `width` int(11) NOT NULL ,
  `height` int(11) NOT NULL ,
  `name` varchar(256) NOT NULL ,
  PRIMARY KEY (`poiid`,`cameraid`),
  KEY `cameraid` (`cameraid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `camera` (
  `cameraid` varchar(11) NOT NULL ,
  `calibration` int(11) NOT NULL ,
  PRIMARY KEY (`cameraid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `people` (
  `peopleid` int(11) NOT NULL ,
  `frameid` varchar(11) NOT NULL ,
  `cameraid` varchar(11) NOT NULL ,
  `bb_x` int(11) NOT NULL ,
  `bb_y` int(11) NOT NULL ,
  `bb_width` int(11) NOT NULL ,
  `bb_height` int(11) NOT NULL ,
  `bbV_x` int(11) NOT NULL ,
  `bbV_y` int(11) NOT NULL ,
  `bbV_width` int(11) NOT NULL ,
  `bbV_height` int(11) NOT NULL ,
  `gazeAngle_face` int(3) NOT NULL ,
  `gazeAngle_face_z` int(3) NOT NULL,
  `gazeAngle_body` int(3) NOT NULL,
  `gazeAngle_body_z` int(3) NOT NULL,
  `color` varchar(7) NOT NULL ,
  `poiid` varchar(11) NOT NULL,
  `userid` varchar(5) NOT NULL,		
  `groupid` varchar(11) NOT NULL,
  PRIMARY KEY (`peopleid`,`frameid`,`cameraid`,`userid`),
  KEY `poiid` (`poiid`),
  KEY `peopleid` (`peopleid`),
  KEY `userid` (`userid`),
  KEY `cameraid` (`cameraid`),
  KEY `groupid` (`groupid`),
  KEY `frameid` (`frameid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `tgroup` (
  `groupid` varchar(11) NOT NULL ,
  `name` varchar(20) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `userid` varchar(5) NOT NULL,
  PRIMARY KEY (`groupid`,`userid`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `user` (
  `userid` varchar(5) NOT NULL,
  `name` varchar(30) NOT NULL,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `video` (
  `frameid` varchar(11) NOT NULL,
  `cameraid` varchar(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`frameid`,`cameraid`),
  KEY `frameid` (`frameid`,`cameraid`),
  KEY `frameid_2` (`frameid`),
  KEY `cameraid` (`cameraid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `real_people` (
  `peopleid` int(11) NOT NULL AUTO_INCREMENT,
  `face` int(11) NOT NULL,
  `face_z` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  PRIMARY KEY (`peopleid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



ALTER TABLE `poi`
  ADD CONSTRAINT `poi_ibfk_1` FOREIGN KEY (`cameraid`) REFERENCES `camera` (`cameraid`);

ALTER TABLE `people`
  ADD CONSTRAINT `people_ibfk_1` FOREIGN KEY (`poiid`) REFERENCES `poi` (`poiid`),
  ADD CONSTRAINT `people_ibfk_2` FOREIGN KEY (`userid`) REFERENCES `user` (`userid`),
  ADD CONSTRAINT `people_ibfk_3` FOREIGN KEY (`cameraid`) REFERENCES `camera` (`cameraid`),
  ADD CONSTRAINT `people_ibfk_4` FOREIGN KEY (`peopleid`) REFERENCES `real_people` (`peopleid`),
  ADD CONSTRAINT `people_ibfk_5` FOREIGN KEY (`groupid`) REFERENCES `tgroup` (`groupid`),
  ADD CONSTRAINT `people_ibfk_6` FOREIGN KEY (`frameid`) REFERENCES `video` (`frameid`);

ALTER TABLE `tgroup`
  ADD CONSTRAINT `group_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`userid`);

ALTER TABLE `video`
  ADD CONSTRAINT `video_ibfk_1` FOREIGN KEY (`cameraid`) REFERENCES `camera` (`cameraid`);


