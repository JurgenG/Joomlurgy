<?php
/**
 * @version     1.0.0
 * @package     com_joomlurgy
 * @copyright
 * @license
 * @author      nidhi <nidhi.gupta@daffodilsw.com> - http://
 */

// No direct access
defined('_JEXEC') or die;

/**
 * @param	array	A named array
 * @return	array
 */
function JoomlurgyBuildRoute(&$query)
{
	$segments = array();
	if (isset($query['task'])) {
		$segments[] = implode('/',explode('.',$query['task']));
		unset($query['task']);
	}
	if (isset($query['view'])) {
		$segments[] = $query['view'];
		$view = $query['view'];
		unset($query['view']);
		if($view == 'joomlurgyevent'){
				$db = JFactory::getDbo();
				$aquery = $db->setQuery($db->getQuery(true)
						->select('name,cycle,period,detail')
						->from('#__joomlurgy_')
						->where('id='.(int)$query['id'])
					);
					$eventdata = $db->loadObject();
					$query['id'] = $eventdata->cycle.'/'.$eventdata->period.'/'.$eventdata->detail;
	 }
	if($view == 'joomlurgysaint'){
				$db = JFactory::getDbo();
				$aquery = $db->setQuery($db->getQuery(true)
						->select('name,month,day')
						->from('#__joomlurgy_saints')
						->where('id='.(int)$query['id'])
					);
					$alias = $db->loadObject();
					$query['id'] = $query['id'].':'.$alias->name;
	 }
	}
	if (isset($query['id'])) {
		$segments[] = $query['id'];
		unset($query['id']);
	}
        unset($segments[0]);
	return $segments;
}

/**
 * @param	array	A named array
 * @param	array
 *
 * Formats:
 *
 * index.php?/joomlurgy/task/id/Itemid
 *
 * index.php?/joomlurgy/id/Itemid
 */
function JoomlurgyParseRoute($segments)
{
        $count =  count($segments); 
	$vars = array(); 
        if($count == 3){
            array_unshift($segments,'joomlurgyevent');
            $view = 'joomlurgyevent';
        }else {
            array_unshift($segments,'joomlurgysaint');
            $view = 'joomlurgysaint';
        }
        $segments2 = array();
	$view = $segments[0];
	// view is always the first element of the array
	$count = count($segments); 
	if($segments[0] == 'joomlurgysaint'){
		$segments1 = $segments;
	}else {
            $segments2 = $segments;
        }
	
	if ($count)
	{
		$count--;
		$segment = array_pop($segments) ;  
		//$segment = str_replace(':', '-', $segments);
		if($segments[0] == 'joomlurgysaint'){ 
			list($id, $alias) = explode(':', $segments1[1], 2); 
		} 
		if (is_numeric($id)) {
                 
		 $vars['id'] = $id;
                
		}
		else{
                    
			$count--;
                       
			$vars['task'] = array_pop($segments) . '.' . $segment;
		}
	}

	if ($count)
	{
		$vars['task'] = implode('.',$segments);
	}
	
	switch($segments[0])
	{
		case 'joomlurgyevent':
			$vars['view'] = 'joomlurgyevent';
			$id = $segments[1];
                        
                        $vars['cycle']= $segments2[1];
		        $vars['period']= $segments2[2];
		        $vars['detail']= $segments2[3];
			break;
		case 'joomlurgysaint':
			$vars['view'] = 'joomlurgysaint';
			$id = $segments[1];
			break;
	} 
       
	return $vars;
}
