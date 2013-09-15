<?php

/* * ******************************************************************
 Product    : MiniCalendar
 Date       : 31 March 2013
 Copyright  : Les Arbres Design 2010-2013
 Licence    : GNU General Public License
 Description: Displays a calendar in a module position
 * ******************************************************************* */

defined('_JEXEC') or die('Restricted access');
//class included for the finding the joomlurgy event according to a date
require_once(JPATH_BASE . DS . 'components' . DS . 'com_joomlurgy' . DS . 'helpers' . DS . 'litdate.class.php');
//-------------------------------------------------------------------------------
// Define cal_days_in_month() in case server doesn't support it
//
if (!function_exists('cal_days_in_month')) {

	function cal_days_in_month($calendar, $month, $year) {
		return date('t', mktime(0, 0, 0, $month + 1, 0, $year));
	}

}

//---------------------------------------------------------------------------------------------
// Get an array of day names in the current language
//
function get_day_names($start_day) {
	$j_days = array(JText::_('SUNDAY'), JText::_('MONDAY'), JText::_('TUESDAY'), JText::_('WEDNESDAY'), JText::_('THURSDAY'), JText::_('FRIDAY'), JText::_('SATURDAY'));
	for ($i = 0; $i < 7; $i++) {
		$day = ($i + $start_day) % 7;
		$days[] = $j_days[$day];
	}
	return $days;
}

//---------------------------------------------------------------------------------------------
// Get a month name in the current language
//
function get_month_name($month) {
	switch ($month) {
		case 1: return JText::_('JANUARY');
		case 2: return JText::_('FEBRUARY');
		case 3: return JText::_('MARCH');
		case 4: return JText::_('APRIL');
		case 5: return JText::_('MAY');
		case 6: return JText::_('JUNE');
		case 7: return JText::_('JULY');
		case 8: return JText::_('AUGUST');
		case 9: return JText::_('SEPTEMBER');
		case 10: return JText::_('OCTOBER');
		case 11: return JText::_('NOVEMBER');
		case 12: return JText::_('DECEMBER');
	}
}

//---------------------------------------------------------------------------------------------
// Draw a calendar for one month in any language
// $link is blank for no links, or a url ready to append 'p' for previous or 'n' for next
//
function make_calendar($year, $month, $link = '', $day_name_length, $start_day, $weekHdr, $debug = false) {
	$current_year = $year==''?date('Y'):$year;
	$current_month = $month==''?date('m'):$month;
	$current_day = date('d');
	$num_columns = 7;          // without week numbers, we have 7 columns
	if (($weekHdr != '') and ($start_day == 1) and (!stristr(PHP_OS, 'WIN')))
	$num_columns = 8;
	else
	$weekHdr = '';          // if start day not Monday, or we are on Windows, don't do week numbers

	echo "\n" . '<table class="mod_minical_table">' . "\n";
	echo "\n<tr>";

	// draw the month and year heading in the current language

	echo '<th colspan="' . $num_columns . '">';
	if ($link != '')
	echo '<a href="' . $link . 'p"><span class="mod_minical_left">&laquo;</span></a>&nbsp;&nbsp;';
	$month_string = get_month_name($month) . ' ' . $year;
	echo $month_string;
	if ($link != '')
	echo '&nbsp;&nbsp;<a href="' . $link . 'n"><span class="mod_minical_right">&raquo;</span></a>';
	echo '</th>';
	echo '</tr>';

	// draw the day names heading in the current language

	if ($day_name_length > 0) {
		echo "\n<tr>";
		if ($weekHdr != '')
		echo "<th>" . $weekHdr . "</th>";
		$days = get_day_names($start_day);
		for ($i = 0; $i < 7; $i++) {
			$day_name = $days[$i];
			if (function_exists('mb_substr'))
			$day_short_name = mb_substr($day_name, 0, $day_name_length, 'UTF-8'); // prefer this if available
			else
			$day_short_name = substr($day_name, 0, $day_name_length);  // use this if no mbstring library
			echo "<th>$day_short_name</th>";
		}
		echo '</tr>';
	}

	// draw the days

	$day_time = gmmktime(5, 0, 0, $month, 1, $year);   // GMT of first day of month
	if ($debug)
	mc_trace("\nStart:      " . gmstrftime("%Y-%m-%d %H:%M (wk %V)", $day_time));
	$first_weekday = gmstrftime("%w", $day_time);  // 0 = Sunday ... 6 = Saturday
	$first_column = ($first_weekday + 7 - $start_day) % 7;  // column for first day
	$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

	$saints = getSaints($month);
	echo '<tr>';
	if ($weekHdr != '') {
		$weeknumber = gmstrftime("%V", $day_time);   // first week number (doesn't work on Windows)
		echo '<td class="mod_minical_weekno">' . $weeknumber . '</td>';
	}
	if ($first_column > 0)
	echo '<td colspan="' . $first_column . '" class="mod_minical_nonday"></td>';  // days before the first of the month
	$column_count = $first_column;
	$key = null;

	$i = 0;
	$config =& JFactory::getConfig();
    $offset = $config->getValue('config.offset');
	for ($day = 1; $day <= $days_in_month; $day++) {

    	//code edit start for making the integration of the joomlury events and saints event
		$makeday = str_pad($day, 2, '0', STR_PAD_LEFT); //making month in two digits
		$time_requested = $current_year.$current_month.$makeday; 
		$timestamp = strtotime($time_requested) + $offset; 
		
		if(isSunday($time_requested)){
	
		$joomlury_event = litdate::getDate($timestamp); //function to be called of component class.
	    
		if($joomlury_event['id'] > 0 && $joomlury_event['id'] != '')
		{   
			//handing if there is not any saints event in the current month.
			if(!count($saints))
			$link = 'index.php?option=com_joomlurgy&view=joomlurgyevent&id='.$joomlury_event['id'];
			foreach($saints as $saintsarray)
			{
			
				if($day == $saintsarray->day)
				{
				
				
					if($joomlury_event['weight'] > $saintsarray->weight)//lower weight event will win
					$link = 'index.php?option=com_joomlurgy&view=joomlurgysaint&id='.$saintsarray->id;
					else
					$link = 'index.php?option=com_joomlurgy&view=joomlurgyevent&id='.$joomlury_event['id'];
					break;
				}
				else{
					$link = 'index.php?option=com_joomlurgy&view=joomlurgyevent&id='.$joomlury_event['id'];
				}
			}
		}
			}else{
			//handing if there is not any saints event in the current month.
			if(!count($saints))
			$link = 'index.php?option=com_joomlurgy&view=joomlurgyevent&id='.$joomlury_event['id'];
			foreach($saints as $saintsarray)
			{
				if($day == $saintsarray->day)
				{
					$link = 'index.php?option=com_joomlurgy&view=joomlurgysaint&id='.$saintsarray->id;
					break;
				}
				else{
					$link = '';
				}
			}
		}
		//code edit end for making the integration of the joomlury events and saints event

		if ($column_count == 7) {
			echo "</tr>\n<tr>";
			$column_count = 0;
			if ($weekHdr != '') {
				// $day_time = strtotime(strftime('%Y-%m-%d',$day_time).' + 1 week');
				$day_time += 604800;   // add exactly one week
				if ($debug)
				mc_trace(" next week: " . gmstrftime("%Y-%m-%d %H:%M (wk %V)", $day_time));
				$weeknumber = gmstrftime("%V", $day_time); // week number
				echo '<td class="mod_minical_weekno">' . $weeknumber . '</td>';
			}
		}

		if (($year == $current_year) and ($month == $current_month) and ($day == $current_day)){

			if($link){
				echo '<td id="mod_minical_today"><a href="'.$link.'" /> '. $day . '</a></td>';
			}  else {
				echo '<td id="mod_minical_today">' . $day . '</td>';
			}

		}

		else {
			if($link){
				echo '<td><a href="'.$link.'" /> '. $day . '</a></td>';
			}  else {
				echo '<td>' . $day . '</td>';
			}

		}
		$column_count++;
	}
	$end_cols = 7 - $column_count;
	if ($end_cols > 0)
	echo '<td colspan="' . $end_cols . '" class="mod_minical_nonday"></td>';    // days after the last day of the month
	echo "</tr></table>\n";
}

function mc_init_debug() {
	$locale = setlocale(LC_ALL, 0);
	$langObj = JFactory::getLanguage();
	$version = new JVersion();
	$xml_array = JApplicationHelper::parseXMLInstallFile(JPATH_ROOT . '/modules/mod_minicalendar/mod_minicalendar.xml');
	mc_trace("\nMiniCalendar ver : " . $xml_array['version']);
	mc_trace("PHP version      : " . phpversion());
	mc_trace("PHP Locale       : " . print_r($locale, true));
	mc_trace("Server           : " . PHP_OS);
	mc_trace("Joomla Version   : " . $version->RELEASE . "." . $version->DEV_LEVEL);
	mc_trace("Joomla Language  : " . $langObj->get('tag'));
}

function mc_trace($data) {
	@file_put_contents(JPATH_ROOT . '/modules/mod_minicalendar/trace.txt', $data . "\n", FILE_APPEND);
}
/*
 * function for finding the saints(from saints table) according to the month
 * @params month
 */
function getSaints($month = '2') {
	$db = & JFactory::getDBO();
	$query = $db->getQuery(true);
	$query->select('id,day,weight')
	      ->from('#__joomlurgy_saints as jb')
	      ->where('jb.month =' . $db->Quote($month));
	$db->setQuery($query); 
	$resultsaints = $db->loadObjectList(); 
	return $resultsaints;
}

function isSunday($date){

     	$date1 = strtotime($date);
        $date2 = date("l", $date1);
        $date3 = strtolower($date2);
        
        if($date3 == "sunday"){
           return true;
        } else {
           return false;
        }
}