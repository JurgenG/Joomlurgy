<?php
defined('_JEXEC') or die('Restricted access');

global $option; 

require_once(JPATH_BASE.DS.'components'.DS.'com_joomlurgy'.DS.'helpers'.DS.'perikope.class.php');

class litdate {

/**
 * Converts the input into a year.
 * Options:
 * - timestamp - the year gets stripped
 * - default (0) - returns the current year
 * - dates prior to 1970 or after 2037 return year 1970
 *
 * @param int Year - as Unix Timestamp or 4-digit year
 * @return int Year - as 4-digit year
 **/
private static function putYear($theYear) {
    	if ($theYear==0) { return date("Y", mktime()); }
    	if ($theYear < 1970) { return 1970; }
    	if ($theYear > 2037) {
			return date("Y", $theYear);
    	} else {return floor($theYear); }
}

/**
 * Converts the input into a timestamp within range (time 00:00:00).
 * Options:
 * - timestamp - the time gets stripped
 * - default (0) - returns the current date
 * - dates prior to 1970 or after 2037 return the given date in current year
 *
 * @param longint/int Date - as Unix Timestamp or date of format YYYYMMDD
 * @return longint Date - as Unix Timestamp
 */
private static function putDate($theDate=0) {
		$arrDate = getdate($theDate);
		if($theDate==0) {
			$arrNow = getdate(mktime());
			$result =  mktime(0,0,0,$arrNow['mon'],$arrNow['mday'],$arrNow['year']);
		} elseif($theDate < 20380000) { //Datestamp format YYYYMMDD
			$theYear	= (int) substr($theDate, 0,4);
			$theMonth	= (int) substr($theDate,4,2);
			$theDay		= (int) substr($theDate, 6,2);
			$result = mktime(0,0,0,$theMonth, $theDay, $theYear);
		} elseif($arrDate['year'] < 1970) { $result = mktime(0,0,0,$arrDate['mon'],$arrDate['mday'],date('Y'));
		} elseif($arrDate['year'] > 2037) { $result = mktime(0,0,0,$arrDate['mon'],$arrDate['mday'],date('Y'));
		} else { $result = mktime(0,0,0,$arrDate['mon'],$arrDate['mday'],$arrDate['year']); }
	return $result;
}

/**
 * Returns the list of content titles for this category with a link to the content items
 *
 */
public static function getTeksten($categorie) {
	$db = JFactory::getDBO();
	$query = $db->getQuery(true);
	$query
		->select(array('c.*', 'u.name as author'))
		->from("#__content as c")
		->join("INNER","#__users as u ON (c.created_by=u.id)")
		->where("catid='$categorie' AND c.state=1")
		->order("c.created DESC");
		$db->setQuery($query);
	$rows = $db->loadObjectList();
	if(is_array($rows) && (count($rows)>0)) {
		$uitvoer = "<ul>\n";
		foreach($rows as $row) {
			$Authorname = ($row->created_by_alias == "") ? $row->author : $row->created_by_alias . " (" . $row->author . ")";
			$link = JRoute::_('index.php?option=com_content&view=article&id='.$row->id.":".$row->alias);
			$uitvoer .= '	<li><em>' . $Authorname . "</em>: " . JHTML::_('link',$link,$row->title) . "</li>\n"; 
		}
		$uitvoer .="</ul>\n";
	} else {
		$uitvoer = "<p>Geen teksten gevonden</p>\n";
	}
	return $uitvoer;
}




/**
 * Returns the date of the next Sunday - time(00:00:00) - in the requested form
 * @param longint/int theDate - as Unix Timestamp or date of format YYYYMMDD
 * @return longint Date - Date of next Sunday - as Unix Timestamp
 */
public static function getSunday($theDate=0) {

		$theDate	= litdate::putDate($theDate);
		$theYear	= date("Y", $theDate);
		$thisDay	= date("z", $theDate); 
		$shift		= date("w", $theDate)?7-date("w",$theDate):0; // if Sunday: current is now
		$theSunday	= mktime(0,0,0,date('n',$theDate),date('j',$theDate) + $shift,$theYear);
		return $theSunday;
}

/**
 * Calculates the date of Easter for the given year
 *
 * @param int Year (default = current)
 * @param int Order (weeks in Easter Time)
 * @return longint Unix Timestamp for Easter of the given year
 */
public static function Easter($theYear = "0", $Order=1) {
    	$theYear = litdate::putYear($theYear);
    	if ($Order > 7) { $Order = 7; }
    	if ($Order < 2) { $Order = 1; }
    	if ($Order == 1) {return easter_date($theYear); }
    	else {
    		$arrEaster = getDate(litdate::Easter($theYear));
    		return mktime(0,0,0,1,$arrEaster['yday']-6+$Order*7, $arrEaster['year']);
    	}
}

/**
 * Calculates the date of Ashwednesday for the given year
 *
 * @param int Year (default = current)
 * @return longint Unix Timestamp for Ashwednesday of the given year
 */
public static function AshDay($theYear = "0") {
    	$theYear = litdate::putYear($theYear);
		$arrEaster = getDate(litdate::Easter($theYear));
		return mktime(0,0,0,1,$arrEaster['yday']-45,$arrEaster['year']);
}

/**
 * Calculates the date of Trinity Sunday for the given year
 *
 * @param int Year (default = current)
 * @return longint Unix Timestamp for Pentecost of the given year
 */
public static function Trinity($theYear=0) {
    	$theYear = litdate::putYear($theYear);
		$arrEaster = getDate(litdate::Easter($theYear));
		return mktime(0,0,0,1,$arrEaster['yday']+57,$arrEaster['year']);
}

/**
 * Calculates the date of Pentecost for the given year
 *
 * @param int Year (default = current)
 * @param int Sunday (1 = (default)Pentecost Sunday - 0 = Pentecost Monday)
 * @return longint Unix Timestamp for Pentecost of the given year
 */
public static function Pentecost($theYear=0,$Sunday=1) {
    	$theYear = litdate::putYear($theYear);
		$arrEaster = getDate(litdate::Easter($theYear));
		if($Sunday=1) {
			return mktime(0,0,0,1,$arrEaster['yday']+50,$arrEaster['year']);
		} else {
			return mktime(0,0,0,1,$arrEaster['yday']+51,$arrEaster['year']);
		}
}

/**
 * Calculates the date of Christmas for the given year
 *
 * @param int Year (default = current)
 * @param int Order (default = 0 - Christmas day)
 * @return longint Unix Timestamp for Christmas of the given year
 */
public static function Christmas($theYear=0, $Order=0) {
    	$theYear = litdate::putYear($theYear);
    	if ($Order < 1) { $Order = 0; }
    	if ($Order > 3) { $Order = 3; }
    	if ($Order == 0) { return mktime(0,0,0,12,25,$theYear); }
    	else {
    		$arrChristmas = getdate(mktime(0,0,0,12,25,$theYear));
    		return mktime(0,0,0,12,25 - $arrChristmas['wday']+$Order*7,$theYear);
    	}
   }

/**
 * Calculates the date of a certain Advent Sunday for the given year
 *
 * @param int Year (default = 0 -  current)
 * @param int Order (default = 1  - 1st Sunday of Advent)
 * @return longint Unix Timestamp for the requested date
 */
public static function Advent($theYear=0,$Order=1){
    	$theYear = litdate::putYear($theYear);
    	if ($Order > 4)  { $Order = 4; }
    	if ($Order < 1)  { $Order = 1; }
		$arrChristmas = getdate(litdate::Christmas($theYear));
		$shift = $arrChristmas["wday"] ? 27 + $arrChristmas['wday'] : 34 ;
		$MyDate = mktime(0,0,0,1,$arrChristmas['yday']- $shift +$Order*7,$arrChristmas['year']);
		return $MyDate;
}

/**
 * Calculates the date of a certain Lenten Sunday for the given year
 *
 * @param int Year (default = 0 - current)
 * @param int Order (default = 0 - AshWednesday)
 * @return longint Unix Timestamp for the requested date
 */
public static function Lent($theYear=0, $Order = 0){
		$theYear = litdate::putYear($theYear);
    	if ($Order > 6)  { $Order = 6; }
    	if ($Order==0) {
    		return litdate::AshDay($theYear);
    	} else {
    		$arrEaster = getDate(litdate::Easter($theYear));
			return mktime(0,0,0,1,$arrEaster['yday']-48+$Order*7,$arrEaster['year']);
    	}

	}

/**
 * Calculates the date of a certain common Sunday for the given year
 *
 * @param int Year (default = current)
 * @param int Order (default = 2)
 * @return Timestamp/Boolean If not available returns "FALSE" - else timestamp of date
 */
public static function Common($theYear=0, $Order = 2){
		$theYear = litdate::putYear($theYear);
		if($Order < 1) { $Order = 2; }
		if($Order > 34) { $Order = 34; }
		//1st Sunday = Baptism of Christ - 2nd etc... up to Ash Wednesday
		//After Pentecost calculate using countdown from Christmas-time
		$AshDate = date("z", litdate::AshDay($theYear))+1;
		$XMasEnd = date("z", litdate::Christmas($theYear-1, 3))+1;
		$Begin = floor(($AshDate - $XMasEnd)/7) + 1;
		$AdvStart = date("z", litdate::Advent($theYear))+1;
		$Trinity = date("z", litdate::Trinity($theYear))+1;
		$End = 35 - floor(($AdvStart - $Trinity)/7);
		if ($Order <= $Begin) {
			return mktime(0,0,0,1,$XMasEnd - 7 + $Order*7,$theYear);
		} elseif($Order > $End) {
			$Difference = 35 - $Order;
			return mktime(0,0,0,1,$AdvStart - $Difference*7, $theYear);
		} else { return FALSE; }
	}

/**
 * Returns the liturgical cycle for a given date (A-B-C)
 * @param longint Unix Timestamp
 * @return string Liturgical cycle (1 char)
 */
public static function Cycle($theDate=0) {
	$theDate = litdate::putDate($theDate);
	$theYear = date("Y", $theDate);
	$AdvStart = litdate::Advent($theYear);
	// The liturgical year starts with the beginning of the Advent
	if($theDate >= $AdvStart) { $theYear = $theYear +1; }
	$cycle = $theYear % 3;
	switch($cycle) {
		case "1" :	return "A"; break;
		case "2" :	return "B"; break;
		case "0" :	return "C"; break;
		default	 :	return "0"; break; // This should never occur!
	}
}

/**
 * Returns an array with all the necessary information if this is a feast day with fixed date.
 * Returns "false" if the given date is not a feast with fixed date.
 * @param longint Unix Timestamp
 * @return array of required information
 *     fields: period - cycle - timestamp - reading1 - reading2 - gospel
 */
 public static function getSaint($theDate=0) {
	// TODO Heiligenkalender verwerken in gewone kalender
 	global $database;
 	$theDate = litDate::getSunday($theDate, $forsunday=true, $item="*");
 	// $Query = "SELECT * FROM #__joomlurgy_saints WHERE month=" . date("n",$theDate) . "AND day=" . $theDate['day'];
 	$Query = "SELECT * FROM #__joomlurgy_saints WHERE month=7 AND day=20";
	$database->SetQuery($Query);
	$row = NULL;
	$database->loadObject($row);
	if ($row->name)
	{
		$arrDate["period"]   = "-";
		$arrDate["gospel"]	 = $row->gospel;
		$arrDate["reading1"] = $row->scripture1;
		$arrDate["reading2"] = $row->scripture2;
		$arrDate["sermons"]  = $row->category;
		$arrDate["name"]	 = $row->name;
		return $arrDate;
	}else {
		return FALSE;
	}

 }


/**
 * Returns an array with all the necessary information about a given date
 *
 * @param longint Unix Timestamp
 * @return array of required information
 *     fields: period - detail - cycle - timestamp - scripture1 - scripture2 - gospel
 */
public static function getDate($theDate=0, $forsunday=true, $item="*") {

	// Initiate database connection
	$db =& JFactory::getDBO();
	//legacy bug fix - TODO : remove all occurances of reading1 and reading2
	$item = str_replace("reading","scripture",$item);
	
	// Setting Date to current if zero (none given)
	$theDate    = ($theDate == 0 ? time() : $theDate);
	$theYear	= date("Y", $theDate); 
	$sundayTS 	= litdate::getSunday($theDate); //echo $sundayTS; 
	$sunday 	= date("z", $sundayTS);
	$theDate	= ($forsunday ? litdate::putDate($sundayTS) : litdate::putDate($theDate));
	$checkingdate = date("z",$theDate);

	// Dates calculated based on day of the year
	$XMasEnd	= date("z",litdate::Christmas($theYear-1, 3));
	$Ashday		= date("z",litdate::AshDay($theYear));
	$Easter		= date("z",litdate::Easter($theYear));
	$Pentecost	= date("z",litdate::Pentecost($theYear,0));
	$Trinity	= date("z",litdate::Trinity($theYear));
	$AdvStart	= date("z",litdate::Advent($theYear));
	$AdvEnd 	= date("z",litdate::Advent($theYear, 4));
	$XMas		= date("z",litdate::Christmas($theYear));

	if(!defined(_XMAS)) 	{ define(_XMAS,'Kersttijd'); }; 
	if(!defined(_COMMON)) 	{ define(_COMMON,'Tijd door het jaar'); }; 
	if(!defined(_LENT)) 	{ define(_LENT,'Veertigdagentijd'); }; 
	if(!defined(_EASTER)) 	{ define(_EASTER,'Paastijd'); }; 
	if(!defined(_ADVENT)) 	{ define(_ADVENT,'Advent'); }; 
	
	if($checkingdate <= $XMasEnd) {
		$arrDate["period"]= _XMAS;
		$period = "X";
		$advEnd = date("z", litdate::Advent($theYear-1, 4)) - date("z", mktime(0,0,0,12,31,$theYear)) - 2;
		$arrDate["detail"]= ($sunday - $advEnd)/7;
	} elseif ($checkingdate < $Ashday) {
		$arrDate["period"]= _COMMON;
		$period = "C";
		$arrDate["detail"]="";
		$XMasEnd = date("z",litdate::Christmas($theYear-1, 3));
		$arrDate["detail"]= 1+ ($sunday - $XMasEnd)/7;
	} elseif ($checkingdate < $Easter) {
		$arrDate["period"]= _LENT;
		$period = "L";
		$Common1End = $Ashday - 3;
		$arrDate["detail"] = ($sunday - $Common1End)/7;
	} elseif ($checkingdate <= $Pentecost) {
		$arrDate["period"]= _EASTER;
		$period = "E";
		$delta = $sunday - $Easter;
		if ($checkingdate ==$Pentecost) { $arrDate["detail"]="08"; }
		else { $arrDate["detail"]= 1+ $delta/7; }
	} elseif ($checkingdate <= $Trinity){
		$arrDate["period"]= _COMMON;
		$period = "C";
		$arrDate["detail"]= "TRI";
	} elseif ($checkingdate < $AdvStart) {
		$arrDate["period"]= _COMMON;
		$period = "C";
		$arrDate["detail"]= 35 - ($AdvStart - $sunday) / 7;
	} elseif ($checkingdate < $XMas) {
		$arrDate["period"]= _ADVENT;
		$period = "A";
		$arrDate["detail"]=1+($sunday - $AdvStart)/7;
	} else {
		$arrDate["period"]= _XMAS;
		$period = "X";
		$arrDate["detail"]=($sunday > $AdvEnd ?($sunday - $AdvEnd)/7 : 1);
		
	}
	$arrDate["periodAbbr"] = $period;
	$arrDate["cycle"]=litdate::Cycle($theDate);
	$arrDate["timestamp"]= mktime(0,0,0,1,1+$checkingdate, $theYear);
    //round to the detail because we are getting in decimal.
   // $arrDate["detail"] = round($arrDate["detail"]);
    
	$QueryValues = Array("*","id","gospel","scripture1","scripture2","sermons","celebrations","documents","name");
	if(in_array($item, $QueryValues)){
		$query = $db->getQuery(true);
		$query
			->select('*')
			->from('#__joomlurgy_')
			->where("period LIKE " . $db->quote($arrDate["periodAbbr"]))
			->where("cycle LIKE "  . $db->quote($arrDate["cycle"]))
			->where("detail LIKE " . $db->quote($arrDate["detail"]));
		$db->setQuery($query);
		$row = $db->loadObject();
		if($item=="*") {
			$arrDate["id"]			= $row->id;
			$arrDate["gospel"]	 	= $row->gospel;
			$arrDate["reading1"] 	= $row->scripture1;
			$arrDate["scripture1"]	= $row->scripture1;
			$arrDate["reading2"] 	= $row->scripture2;
			$arrDate["scripture2"] 	= $row->scripture2;
			$arrDate["sermons"]  	= $row->category;
			$arrDate["celebrations"]= $row->cat_celeb;
			$arrDate["documents"] 	= $row->cat_doc;
			$arrDate["name"]	 	= $row->name;
			$arrDate["weight"]	 	= $row->weight;
		} else {
			$arrDate[$item]			= $row->{$item};
		}
	} 
	if($item=="*") {return $arrDate;}
	elseif(isset($arrDate[$item])) { return $arrDate[$item];}
	else { return false; }
}

} // End of class
?>
