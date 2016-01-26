<?php
/**
 * Set of functions for generating useful queries to database
 */

class Queries {
	
	/** Users queries **/
	function getUserIdFromName($name){
		return "SELECT userid FROM user WHERE name='".mysql_real_escape_string($name)."'";
	}
	
	function getUserNameById($userid){
		return "SELECT name FROM `user` WHERE userid='".intval($userid)."'";
	}
	
	/** Groups queries **/
	
	function countNoGroupsByUserName($name){
		return "SELECT g.groupid FROM `groups` as g, `user` as u WHERE g.userid=u.userid AND u.name='".$name."' AND g.groupid = 0";
	}
	
	function insertGroup($id, $name, $deleted, $userid){
		return "INSERT INTO `groups` (`id`, `name`, deleted, `userid`) VALUES (".$id.", '".$name."', ".$deleted.", '".$userid."');";
	}
	
	//Retrieving groups
	function getGroups($deleted, $query){
		if($query != null){
			if(!$deleted){
				return "SELECT N.groupid, N.name, COUNT( N.groupid ) AS people FROM ( SELECT g.groupid, p.peopleid, g.name, COUNT( p.groupid )
					FROM  `people` AS p RIGHT OUTER JOIN  `groups` AS g ON p.groupid = g.groupid GROUP BY p.groupid, p.peopleid ORDER BY p.groupid ) AS N
					GROUP BY N.groupid";
			} else {
				return "SELECT N.groupid, N.name, COUNT( N.groupid ) AS people FROM ( SELECT g.groupid, p.peopleid, g.name, COUNT( p.groupid ) FROM `people` AS p
					RIGHT OUTER JOIN  `groups` AS g ON p.groupid = g.groupid WHERE g.deleted=0 GROUP BY p.groupid, p.peopleid ORDER BY p.groupid ) AS N
					GROUP BY N.groupid";
			}
		} else {
			if(!$deleted){
				return 	"SELECT N.groupid, N.name, COUNT( N.groupid ) AS people FROM ( SELECT g.groupid, p.peopleid, g.name, COUNT( p.groupid ) 
					    FROM  `people` AS p RIGHT OUTER JOIN  `groups` AS g ON p.groupid = g.groupid WHERE  g.name LIKE '%".$query."%' 
					    GROUP BY p.groupid, p.peopleid ORDER BY p.groupid ) AS N GROUP BY N.groupid";
			
			} else {
				return "SELECT N.groupid, N.name, COUNT( N.groupid ) AS people FROM ( SELECT g.groupid, p.peopleid, g.name, COUNT( p.groupid ) 
					    FROM  `people` AS p RIGHT OUTER JOIN  `groups` AS g ON p.groupid = g.groupid WHERE   g.name LIKE '%".$query."%' AND g.deleted = 0 
					    GROUP BY p.groupid, p.peopleid ORDER BY p.groupid ) AS N GROUP BY N.groupid";
			}
		}
	}
	
	
	//Find removable groups
	function getDeletableGroups($userid){
		return "SELECT g.groupid, count(p.groupid) as people FROM `people` as p right outer join `groups` as g on p.groupid = g.groupid 
				WHERE g.userid=".$userid." AND g.deleted=0 GROUP BY g.groupid ORDER BY g.groupid ";
		
	}
	
	
	/** Frames queries **/
	
	function getFirstFrameId($cameraid){
		return "SELECT MIN(n) as id FROM (SELECT frameid as n FROM video WHERE cameraid = ".$cameraid.") as tab";
	}
	
	function getFirstUntaggedFrameId($userid, $cameraid){
		"SELECT MIN(frameid) as id FROM video WHERE frameid not in (SELECT frameid FROM people WHERE userid=".$userid." AND cameraid=".$cameraid.")";
	}
	
	function getFramesByQuery($cameraid, $term, $limit){
		return "SELECT `frameid` as id, `frameid` as txt FROM `video` WHERE `cameraid` = ".$cameraid." AND `frameid` LIKE '%".$term."%' ORDER BY `frameid`,2 ASC LIMIT ".$limit;
	}
	
	function getFramesForSelect($cameraid, $limit){
		return "SELECT `frameid` as id, `frameid` as txt FROM `video` WHERE `cameraid` = ".$cameraid." ORDER BY `frameid` ASC LIMIT ".$limit;	
	}
	
	/** Camera queries **/
	
	function getCameras(){
		return "SELECT c.cameraid FROM `camera` as c";
	}
	
	/** People queries **/
	
	function getPeopleFrame($cameraid, $frameid){
		return "SELECT * FROM `people` WHERE cameraid=".$cameraid." AND frameid=".$frameid."";
	}
	
	//Returns the union of current frame people id list and the previous frame people (not in current) as proposals
	function getPeople($cameraid, $frameid, $proposals){
		if($proposals){
			$previous = intval($frameid) - 1;
			return "SELECT *, true as previous FROM `people` WHERE cameraid = ".$cameraid." and frameid = ".$frameid."
				UNION (SELECT *, false as previous FROM people WHERE cameraid = ".$cameraid." and frameid = ".$previous."
				and peopleid NOT IN (SELECT peopleid from people where cameraid = ".$cameraid." and frameid = ".$frameid."))";
		} else {
			return $this->getPeopleFrame($cameraid, $frameid);
		}
	}
	
	function insertPerson($peopleid, $frameid, $cameraid, $bbx, $bby, $bbw, $bbh, $bbVx, $bbVy, $bbVw, $bbVh, $gaf, $gafz, $gab, $gabz, $color, $poiid, $userid, $groupid){
		return "INSERT INTO `people` (`peopleid`,`frameid`, `cameraid`, `bb_x`, `bb_y`, `bb_width`, `bb_height`, `bbV_x`, `bbV_y`, `bbV_width`, `bbV_height`, `gazeAngle_face`, `gazeAngle_face_z`, `gazeAngle_body`, `gazeAngle_body_z`,`color`, `poiid`, `userid`, `groupid`) VALUES
					(".$peopleid.", ".$frameid.", ".$cameraid.", ".$bbx.", ".$bby.", ".$bbw.", ".$bbh.", ".$bbVx.", ".$bbVy.", ".$bbVw.", ".$bbVh.", ".$gaf.", ".$gafz.", ".$gab.", ".$gabz.",'".$color."', ".$poiid.", ".$userid.", ".$groupid.");";
	}
	
	/** Real people queries **/
	
	function insertRealPeople($face, $facez, $path){
		return "INSERT INTO `real_people` (`face`,`face_z`,`image`) VALUES (".$face.", ".$facez.",'".$path."')";
	}
	

}

