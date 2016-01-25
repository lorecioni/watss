SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


CREATE TABLE IF NOT EXISTS `poi` (
  `poiid` int(11) NOT NULL , 
  `cameraid` int(11) NOT NULL , 
  `location_x` int(11) NOT NULL ,
  `location_y` int(11) NOT NULL ,
  `width` int(11) NOT NULL ,
  `height` int(11) NOT NULL ,
  `name` varchar(256) NOT NULL ,
  PRIMARY KEY (`poiid`,`cameraid`),
  KEY `cameraid` (`poiid`, `cameraid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `camera` (
  `id` int(11)  AUTO_INCREMENT NOT NULL ,
  `calibration` int(11) NOT NULL ,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `people` (
  `peopleid` int(11) NOT NULL ,
  `frameid` int(11) NOT NULL ,
  `cameraid` int(11) NOT NULL ,
  `userid` int(11) NOT NULL,
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
  `poiid` int(11) NOT NULL,
  `groupid` int(11) NOT NULL,
  PRIMARY KEY (`peopleid`,`frameid`,`cameraid`,`userid`),
  KEY `poiid` (`poiid`),
  KEY `peopleid` (`peopleid`),
  KEY `userid` (`userid`),
  KEY `cameraid` (`cameraid`),
  KEY `groupid` (`groupid`),
  KEY `frameid` (`frameid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `tgroup` (
  `groupid` int(11) AUTO_INCREMENT NOT NULL ,
  `name` varchar(20) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `userid` int(11) NOT NULL,
  PRIMARY KEY (`groupid`,`userid`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `user` (
  `userid` int(11) AUTO_INCREMENT NOT NULL,
  `name` varchar(30) NOT NULL,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `video` (
  `frameid` int(11) NOT NULL,
  `cameraid` int(11) NOT NULL,
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

