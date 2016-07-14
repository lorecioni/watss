<?php
/**
 * Set of functions for generating useful queries to database
 */

class Queries {
	
	//Tables map, for updating easily table names
	private $tables;
	
	public function __construct(){
		$this->tables = new stdClass();
		$this->tables->users = 'users';
		$this->tables->groups = 'groups';
		$this->tables->poi = 'poi';
		$this->tables->frames = 'frames';
		$this->tables->avatars = 'avatars';
		$this->tables->people = 'people';
		$this->tables->cameras = 'cameras';
	}
	
	
	/**
	 * ****************************
	 *  	  Users queries 
	 * ****************************
	 * */
	
	function getUsers(){
		return "SELECT * FROM ".$this->tables->users."";
	}
	
	function getUserIdFromName($name, $password){
		return "SELECT userid FROM `".$this->tables->users."` WHERE `name`='".mysql_real_escape_string($name)."' AND `password` = '".mysql_real_escape_string($password)."'";
	}
	
	function getUserNameById($userid){
		return "SELECT name FROM `".$this->tables->users."` WHERE userid='".intval($userid)."'";
	}
	
	function insertUser($name){
		return "INSERT INTO `".$this->tables->users."` (`name`) VALUES ('".$name."');";
	}
	
	function deleteUser($userid){
		return "DELETE FROM `".$this->tables->users."` WHERE `userid` = ".$userid."";
	}
	
	function updateUserPassword($userid, $password){
		return "UPDATE `".$this->tables->users."` SET `password` = '".$password."'  WHERE `userid` = ".$userid."";
	}
	
	/**
	 * ****************************
	 *  	  Groups queries 
	 * ****************************
	 * */
	
	function countNoGroupsByUserName($name){
		return "SELECT g.groupid FROM `".$this->tables->groups."` as g, `".$this->tables->users."` as u WHERE g.userid=u.userid AND u.name='".$name."' AND g.groupid = 0";
	}
	
	function insertGroup($id, $name, $deleted, $userid){
		return "INSERT INTO `".$this->tables->groups."` (`id`, `name`, deleted, `userid`) VALUES (".$id.", '".$name."', ".$deleted.", '".$userid."');";
	}
	
	//Retrieving groups
	function getGroups($deleted, $query){
		if($query != null){
			if(!$deleted){
				return "SELECT N.groupid, N.name, COUNT( N.groupid ) AS people FROM ( SELECT g.groupid, p.peopleid, g.name, COUNT( p.groupid )
					FROM  `".$this->tables->people."` AS p RIGHT OUTER JOIN  `".$this->tables->groups."` AS g ON p.groupid = g.groupid GROUP BY p.groupid, p.peopleid ORDER BY p.groupid ) AS N
					GROUP BY N.groupid";
			} else {
				return "SELECT N.groupid, N.name, COUNT( N.groupid ) AS people FROM ( SELECT g.groupid, p.peopleid, g.name, COUNT( p.groupid ) FROM `".$this->tables->people."` AS p
					RIGHT OUTER JOIN  `".$this->tables->groups."` AS g ON p.groupid = g.groupid WHERE g.deleted=0 GROUP BY p.groupid, p.peopleid ORDER BY p.groupid ) AS N
					GROUP BY N.groupid";
			}
		} else {
			if(!$deleted){
				return 	"SELECT N.groupid, N.name, COUNT( N.groupid ) AS people FROM ( SELECT g.groupid, p.peopleid, g.name, COUNT( p.groupid ) 
					    FROM  `".$this->tables->people."` AS p RIGHT OUTER JOIN  `".$this->tables->groups."` AS g ON p.groupid = g.groupid WHERE  g.name LIKE '%".$query."%' 
					    GROUP BY p.groupid, p.peopleid ORDER BY p.groupid ) AS N GROUP BY N.groupid";
			
			} else {
				return "SELECT N.groupid, N.name, COUNT( N.groupid ) AS people FROM ( SELECT g.groupid, p.peopleid, g.name, COUNT( p.groupid ) 
					    FROM  `".$this->tables->people."` AS p RIGHT OUTER JOIN  `".$this->tables->groups."` AS g ON p.groupid = g.groupid WHERE   g.name LIKE '%".$query."%' AND g.deleted = 0 
					    GROUP BY p.groupid, p.peopleid ORDER BY p.groupid ) AS N GROUP BY N.groupid";
			}
		}
	}
	
	function getMaxGroupId(){
		return "SELECT MAX(groupid) as id FROM `".$this->tables->groups."`";
	}
	
	function removeGroup($id){
		return "DELETE FROM `".$this->tables->groups."` WHERE  groupid = ".$id."";
	}
	
	
	//Find removable groups
	function getDeletableGroups($userid){
		return "SELECT g.groupid, count(p.groupid) as people FROM `".$this->tables->people."` as p right outer join `".$this->tables->groups."` as g on p.groupid = g.groupid 
				WHERE g.userid=".$userid." AND g.deleted=0 GROUP BY g.groupid ORDER BY g.groupid ";	
	}
	
	
	/**
	 * ****************************
	 *  	  Frames queries 
	 * ****************************
	 * */	
	function getFirstFrameId($cameraid){
		return "SELECT MIN(n) as id FROM (SELECT frameid as n FROM ".$this->tables->frames." WHERE cameraid = ".$cameraid.") as tab";
	}
	
	function getFirstUntaggedFrameId($userid, $cameraid){
		return "SELECT MIN(frameid) as id FROM ".$this->tables->frames." WHERE frameid not in (SELECT frameid FROM ".$this->tables->people." WHERE userid = ".$userid." AND cameraid = ".$cameraid.") AND cameraid = ".$cameraid;
	}
	
	function getFramesByQuery($cameraid, $term, $limit){
		return "SELECT `frameid` as id, `frameid` as txt FROM `".$this->tables->frames."` WHERE `cameraid` = ".$cameraid." AND `frameid` LIKE '%".$term."%' ORDER BY `frameid`,2 ASC LIMIT ".$limit;
	}
	
	function getFramesForSelect($cameraid, $limit){
		return "SELECT `frameid` as id, `frameid` as txt FROM `".$this->tables->frames."` WHERE `cameraid` = ".$cameraid." ORDER BY `frameid` ASC LIMIT ".$limit;	
	}
	
	function getFrameById($frameid, $cameraid){
		return "SELECT * FROM `".$this->tables->frames."` WHERE `frameid`=".$frameid." AND `cameraid`=".$cameraid."";
	}
	
	function getFrameIdList($cameraid, $limit, $current){
		if($limit == null){
			return "SELECT frameid FROM ".$this->tables->frames." WHERE cameraid = ".$cameraid;
		} else {
			return "SELECT frameid FROM ".$this->tables->frames."
                  WHERE frameid >= ".($current - $limit)."
                  		and frameid <= ".($current + $limit)."
                  		and cameraid = '".$cameraid;
		}
	}

	function getPreviousFrameBB($frameid, $cameraid, $person){
		return "SELECT path, people.bb_x, people.bb_y, people.bb_width, people.bb_height 
				FROM `frames`, people where frames.frameid = ".$frameid." and frames.cameraid = ".$cameraid." and people.peopleid = ".$person." 
							and people.frameid = frames.frameid and people.cameraid = frames.cameraid";
	}
	
	function getNextFramesPath($frameid, $cameraid, $length){
		$nums = "";
		$current = $frameid + 1;
		for ($i = 0; $i < $length; $i++){
			$nums .= "".$current;
			if($i < $length - 1) $nums .= ", ";
			$current++;
		}
		return "SELECT path FROM frames WHERE frameid IN (".$nums.") AND cameraid = ".$cameraid;
	}
	
	/**
	 * ****************************
	 *  	  Cameras queries 
	 * ****************************
	 * */
	
	function getCameras(){
		return "SELECT * FROM ".$this->tables->cameras."";
	}
	
	function getCamerasList(){
		return "SELECT c.cameraid FROM `".$this->tables->cameras."` as c";
	}
	
	function deleteCamera($id){
		return "DELETE FROM `".$this->tables->cameras."` WHERE `cameraid` = ".$id."";
	}
	
	function updateCameraCalibrationActive($id, $calibration){
		return "UPDATE `".$this->tables->cameras."` SET `calibration` = ".$calibration." WHERE `cameraid` = ".$id."";
	}
	
	function updateCameraCalibrationIntrinsic($id, $intrinsic){
		return "UPDATE `".$this->tables->cameras."` SET `intrinsic` = '".$intrinsic."' WHERE `cameraid` = ".$id."";
	}
	
	function updateCameraCalibrationOmography($id, $omo){
		return "UPDATE `".$this->tables->cameras."` SET `omography` = '".$omo."' WHERE `cameraid` = ".$id."";
	}
	
	function updateCameraCalibrationParam($id, $param){
		return "UPDATE `".$this->tables->cameras."` SET `param` = ".$param." WHERE `cameraid` = ".$id."";
	}
	
	function insertCamera($calibration){
		return "INSERT INTO `".$this->tables->cameras."` (`calibration`) VALUES ('".$calibration."');";
	}
	
	function getCameraCalibration($cameraid){
		return "SELECT intrinsic, omography, param FROM `".$this->tables->cameras."` WHERE cameraid = ".$cameraid."";
	}
	
	function getCameraCalibrationActive($cameraid){
		return "SELECT calibration from `".$this->tables->cameras."` WHERE cameraid = ".$cameraid."";
	}
	
	/**
	 * ****************************
	 *  	  People queries 
	 * ****************************
	 * */
	
	function getPeopleFrame($cameraid, $frameid){
		return "SELECT * FROM `".$this->tables->people."` WHERE cameraid=".$cameraid." AND frameid=".$frameid."";
	}
	
	//Returns the union of current frame people id list and the previous frame people (not in current) as proposals
	function getPeople($cameraid, $frameid, $proposals){
		if($proposals){
			$previous = intval($frameid) - 1;
			return "SELECT *, true as previous FROM `".$this->tables->people."` WHERE cameraid = ".$cameraid." and frameid = ".$frameid."
				UNION (SELECT *, false as previous FROM ".$this->tables->people." WHERE cameraid = ".$cameraid." and frameid = ".$previous."
				and peopleid NOT IN (SELECT peopleid from ".$this->tables->people." where cameraid = ".$cameraid." and frameid = ".$frameid."))";
		} else {
			return $this->getPeopleFrame($cameraid, $frameid);
		}
	}
	
	function insertPerson($peopleid, $frameid, $cameraid, $bbx, $bby, $bbw, $bbh, $bbVx, $bbVy, $bbVw, $bbVh, $gaf, $gafz, $gab, $gabz, $color, $poiid, $userid, $groupid){
		return "INSERT INTO `".$this->tables->people."` (`peopleid`,`frameid`, `cameraid`, `bb_x`, `bb_y`, `bb_width`, `bb_height`, `bbV_x`, `bbV_y`, `bbV_width`, `bbV_height`, `gazeAngle_face`, `gazeAngle_face_z`, `gazeAngle_body`, `gazeAngle_body_z`,`color`, `poiid`, `userid`, `groupid`) VALUES
					(".$peopleid.", ".$frameid.", ".$cameraid.", ".$bbx.", ".$bby.", ".$bbw.", ".$bbh.", ".$bbVx.", ".$bbVy.", ".$bbVw.", ".$bbVh.", ".$gaf.", ".$gafz.", ".$gab.", ".$gabz.",'".$color."', ".$poiid.", ".$userid.", ".$groupid.");";
	}
	
	function updatePersonColor($color, $userid, $peopleid, $frameid, $cameraid){
		return 	"UPDATE `".$this->tables->people."` SET `color`='".$color."' WHERE  `peopleid`=".$peopleid." AND `cameraid` = ".$cameraid."";	
	}
	
	function updatePersonGroup($groupid, $userid, $peopleid, $frameid, $cameraid){
		return 	"UPDATE `".$this->tables->people."` SET `groupid`='".$groupid."' ,userid='".$userid."' WHERE  `peopleid`=".$peopleid." AND `frameid` = ".$frameid." AND `cameraid` = ".$cameraid."";
	}
	
	function updatePersonBB($bb, $userid, $peopleid, $frameid, $cameraid){
		return "UPDATE `".$this->tables->people."` SET `bb_x`=".$bb[0].",`bb_y`=".$bb[1].",`bb_width`=".$bb[2].",`bb_height`=".$bb[3].", userid=".$userid." WHERE  `peopleid`=".$peopleid." AND `frameid` = ".$frameid." AND `cameraid` = ".$cameraid."";
	}
	
	function updatePersonBBV($bb, $userid, $peopleid, $frameid, $cameraid){
		return "UPDATE `".$this->tables->people."` SET `bbV_x`=".$bb[0].",`bbV_y`=".$bb[1].",`bbV_width`=".$bb[2].",`bbV_height`=".$bb[3].", userid=".$userid." WHERE  `peopleid`=".$peopleid." AND `frameid` = ".$frameid." AND `cameraid` = ".$cameraid."";
	}
	
	function updatePersonAngleFace($angle, $z, $userid, $peopleid, $frameid, $cameraid){
		return "UPDATE `".$this->tables->people."` SET `gazeAngle_face`='".$angle."', `gazeAngle_face_z`='".$z."', `userid`='".$userid."'   WHERE  `peopleid` = '".$peopleid."' AND `frameid` = '".$frameid."' AND `cameraid` = ".$cameraid."";
	}
	
	function updatePersonAngleBody($angle, $z, $userid, $peopleid, $frameid, $cameraid){
		return "UPDATE `".$this->tables->people."` SET `gazeAngle_body`='".$angle."', `gazeAngle_body_z`='".$z."', `userid`='".$userid."'   WHERE  `peopleid` = '".$peopleid."' AND `frameid` = '".$frameid."' AND `cameraid` = ".$cameraid."";
	}
	
	function updatePersonPoi($poiid, $userid, $peopleid, $frameid, $cameraid){
		return "UPDATE `".$this->tables->people."` SET `poiid`='".$poiid."', `userid`='".$userid."'   WHERE  `peopleid` = '".$peopleid."' AND `frameid` = '".$frameid."' AND `cameraid` = ".$cameraid."";
	}
	
	function removePersonById($peopleid, $frameid, $cameraid){
		return "DELETE FROM `".$this->tables->people."` WHERE cameraid=".$cameraid." AND peopleid=".$peopleid." AND frameid=".$frameid."";
	}
	
	function getPersonInFrame($id, $cameraid, $frameid){
		return "SELECT * FROM `".$this->tables->people."` WHERE `peopleid`=".$id." AND cameraid = ".$cameraid." AND frameid = ".$frameid."";
	}
	
	function getColorListByCamera($cameraid){
		return "SELECT DISTINCT `color` FROM `".$this->tables->people."` WHERE `cameraid` = ".$cameraid;
	}
	
	function getPersonColor($peopleid, $cameraid){
		return "SELECT `color` FROM `".$this->tables->people."` WHERE `peopleid` = ".$peopleid." and `cameraid` = ".$cameraid;
	}
	
	
	/**
	 * ****************************
	 *  	  Avatar queries 
	 * ****************************
	 * */
	
	function getAvatar($id){
		return "SELECT * FROM `".$this->tables->avatars."` WHERE `peopleid` = ".$id;
	}
	
	function insertAvatar($face, $facez, $path){
		return "INSERT INTO `".$this->tables->avatars."` (`face`,`face_z`,`image`) VALUES (".$face.", ".$facez.",'".$path."')";
	}
	
	function getAvatarList($frameid, $cameraid){
		return "SELECT r.* FROM `".$this->tables->avatars."` as r WHERE (r.peopleid not in (SELECT p2.peopleid FROM `".$this->tables->people."` as p2 WHERE p2.frameid=".$frameid." AND p2.cameraid=".$cameraid."))";	
	}
	
	function setAvatarFace($peopleid, $face, $facez){
		return "UPDATE `".$this->tables->avatars."` SET face=".$face.", face_z=".$facez." WHERE peopleid=".$peopleid;
	}
	
	function setAvatarImage($id){
		return "UPDATE `".$this->tables->avatars."` SET `image`='../img/avatars/".$id.".jpg' WHERE peopleid=".$id;
	}
	
	function removeAvatar($id){
		return "DELETE FROM `".$this->tables->avatars."` WHERE peopleid=".$id;
	}
	
	/**
	 * ****************************
	 *  	  POI queries 
	 * ****************************
	 * */
	
	function insertPOI($id, $cameraid, $x, $y, $w, $h, $name){
		return "INSERT INTO `".$this->tables->poi."` (`poiid`, `cameraid`, `location_x`, `location_y`, `width`, `height`, `name`)
										VALUES (".$id.", '".$cameraid."', ".$x.", ".$y.", ".$w.", ".$h.", '".$name."')";
	}
	
	function getPois($cameraid){
		return "SELECT * FROM `".$this->tables->poi."` WHERE cameraid=".$cameraid."";
	}
	
	function getPoisByQuery($query, $cameraid){
		return "SELECT * FROM `".$this->tables->poi."` WHERE name LIKE '%".$query."%' AND cameraid=".$cameraid." ORDER BY poiid";
	}
	
	function getUsefulPoi(){
		return "SELECT * FROM ".$this->tables->poi." WHERE poiid != 0";
	}
	
	/**
	 * ****************************
	 *  	  Export queries 
	 * ****************************
	 * */
	
	function getAnnotationExportQueryBase(){
		return "SELECT p.peopleid,p.frameid,p.cameraid, p.bb_x, p.bb_y, p.bb_width, p.bb_height, p.bbV_x, p.bbV_y, p.bbV_width, p.bbV_height, 
				p.gazeAngle_face, p.gazeAngle_face_z, p.gazeAngle_body, p.gazeAngle_body_z,  v.path , ".$this->tables->poi.".name , g.groupid FROM ".$this->tables->people." as p  
				LEFT JOIN ".$this->tables->frames." as v ON p.frameid = v.frameid and p.cameraid=v.cameraid LEFT JOIN ".$this->tables->poi." ON p.poiid=".$this->tables->poi.".poiid and p.cameraid=".$this->tables->poi.".cameraid 
				LEFT JOIN ".$this->tables->groups." as g ON p.groupid=g.groupid;";
	}
	
	function getAnnotationExportQuery($exclude){	
		$sql = "SELECT ";
		$excluding = $exclude;
		if(!in_array("peopleid", $excluding)){
			$sql .= "p.peopleid,";
		}
		if(!in_array("frameid", $excluding)){
			$sql .= "p.frameid,";
		}
		if(!in_array("cameraid", $excluding)){
			$sql .= "p.cameraid,";
		}
		if(!in_array("bb", $excluding)){
			$sql .= "p.bb_x, p.bb_y, p.bb_width, p.bb_height,";
		}
		if(!in_array("bbV", $excluding)){
			$sql .= "p.bbV_x, p.bbV_y, p.bbV_width, p.bbV_height,";
		}
		if(!in_array("face", $excluding)){
			$sql .= "p.gazeAngle_face, p.gazeAngle_face_z,";
		}
		if(!in_array("body", $excluding)){
			$sql .= "p.gazeAngle_body, p.gazeAngle_body_z,";
		}
		if(!in_array("poiid", $excluding)){
			$sql .= $this->tables->poi.".name,";
		}
		if(!in_array("groupid", $excluding)){
			$sql .= "p.groupid,";
		}
		
		$sql .= "v.path FROM ".$this->tables->people." as p
				LEFT JOIN ".$this->tables->frames." as v ON p.frameid = v.frameid and p.cameraid=v.cameraid LEFT JOIN ".$this->tables->poi." ON p.poiid=".$this->tables->poi.".poiid and p.cameraid=".$this->tables->poi.".cameraid
				LEFT JOIN ".$this->tables->groups." as g ON p.groupid=g.groupid;";
		return $sql;
	}
	
	function getExportAvatars(){
		return 	"SELECT * FROM `".$this->tables->avatars."`";	
	}
	
	function getExportCameras(){
		return 	"SELECT * FROM `".$this->tables->cameras."`";
	}
	
	function getExportUsers(){
		return 	"SELECT * FROM `".$this->tables->users."`";
	}
	
	function getExportPeople(){
		return 	"SELECT * FROM `".$this->tables->people."`";
	}
	
	function getExportPoi(){
		return 	"SELECT * FROM `".$this->tables->poi."`";
	}
	
	function getExportGroups(){
		return 	"SELECT * FROM `".$this->tables->groups."`";
	}
	
	function getExportFrames(){
		return 	"SELECT * FROM `".$this->tables->frames."`";
	}
	
	function getExportAnnotatedFramesPath(){
		return "SELECT DISTINCT f.path FROM `".$this->tables->frames."` f, `".$this->tables->people."` p WHERE f.frameid = p.frameid";
	}
	
	function getExportFramesPath(){
		return "SELECT DISTINCT f.path FROM `".$this->tables->frames."` f";
	}
	
	function getTableSchema($table){
		return "SHOW CREATE TABLE ".$table;
	}
}

