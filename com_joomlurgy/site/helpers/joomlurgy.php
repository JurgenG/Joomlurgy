<?php

/**
 * @version     1.0.0
 * @package     com_joomlurgy
 * @copyright
 * @license
 * @author      nidhi <nidhi.gupta@daffodilsw.com> - http://
 */
defined('_JEXEC') or die;
$com_path = JPATH_SITE . '/components/com_content/';
require_once $com_path . 'helpers/route.php';
JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_content/models', 'ContentModel');
require_once(JPATH_BASE . DS . 'components' . DS . 'com_joomlurgy' . DS . 'helpers' . DS . 'litdate.class.php');
require_once(JPATH_BASE . DS . 'components' . DS . 'com_joomlurgy' . DS . 'helpers' . DS . 'simple_html_dom.php');
require_once (JPATH_BASE . DS . 'components' . DS . 'com_joomlurgy' . DS . 'tables' . DS . 'joomlurgy_bibleverses.php');
abstract class JoomlurgyHelper {

	public static function myFunction() {
		$result = 'Something';
		return $result;
	}

	/**
	 * @method to get the content for sermons from Joomla category and article table
	 * @param type $id
	 */
	public static function getList(&$catId) {
		// Get an instance of the generic articles model
		$model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

		// Set application parameters in model
		$app = JFactory::getApplication();

		$appParams = JFactory::getApplication()->getParams();
		$model->setState('params', $appParams);

		// Set the filters based on the module params
		$model->setState('list.start', 0);
		$model->setState('list.limit', 100);
		$model->setState('filter.published', 1);


		// Access filter

		$access = 1;

		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));


		// Category filter
		$model->setState('filter.category_id', $catId);

		// Filter by language
		//$model->setState('filter.language', $app->getLanguageFilter());
		// Ordering
		$model->setState('list.ordering', 'a.id');        // Ordering

		$model->setState('list.direction', 'DESC');

		$items = $model->getItems();
		$content = array();
		foreach ($items as &$item) {

			$item->slug = $item->id . ':' . $item->alias;
			$item->catslug = $item->catid . ':' . $item->category_alias;
			unset($item->introtext);
			if ($access || in_array($item->access, $authorised)) {
				// We know that user has the privilege to view the article
				$item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
			} else {
				$item->link = JRoute::_('index.php?option=com_users&view=login');
			}
		}

		return $items;
	}

	/**
	 * Returns the list of docman titles for this category with a link to the content items
	 *
	 */
	public static function getDocs($categorie) {
		$db = & JFactory::getDBO();
		$query = "SELECT c.*, u.name as author FROM #__docman as c, #__users as u WHERE c.dmsubmitedby=u.id AND catid='" . $categorie . "' ORDER BY c.dmdate_published DESC";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if (is_array($rows) && (count($rows) > 0)) {

			foreach ($rows as $row) {
				$row->link = JRoute::_('index.php?option=com_docman&task=doc_details&gid=' . $row->id);
				$row->category = $categorie;
			}
		} else {
			// $uitvoer = "<p>Geen documenten gevonden</p>\n";
		}
		return $rows;
	}

	/**
	 *
	 * @param type $scripture
	 * @return type
	 */
	public static function getVerses($scripture) {

		$result = array();
		$bible = array();
		$user = & JFactory::getUser();
		if (!$user->guest) {

			$bible = self::GetMyBible();
		} else {

			$bible['title'] = 'NBV';
		}
		// change scripture with . to validate scripture string to correct(remove the . from the scripture name)
		$pattern = '/(\w{2,})\./';
		$replacement = '${1}';
		$scripture1 = preg_replace($pattern, $replacement, $scripture, 1);
		$originalscripture_chapter  = self::_getscriptureschapter($scripture);
		//making the chapter and versus seperate $scriptureparts['versus'], $scriptureparts['chapter'].
		$scriptureparts = self::_getscripturesverses($scripture1);
		$db = & JFactory::getDBO(); 
		//now finding the data according to versus no and chapter.
		foreach($scriptureparts['versus'] as $mergedversus){
			$scripture_verses_range = self::_getscripturesversesrange($mergedversus);
			for($versus_no = $scripture_verses_range['start']; $versus_no <= $scripture_verses_range['end']; $versus_no++)
			if (!empty($scripture)) {
				$query = $db->getQuery(true);
				$query = 'select * from #__joomlurgy_bibleverses as jb where jb.bible = ' . $db->Quote($bible['title']) . ' and jb.chapter =' . $db->Quote($originalscripture_chapter). ' and jb.versus_number ='.$versus_no.' LIMIT 1';
				$db->setQuery($query);
				$query = '';
				$scripture_content = $db->loadObject();
				if (!empty($scripture_content->id)) {

					$result[] = $scripture_content;
				} else {
					$result[] = self::_getVersusFromBiblija($originalscripture_chapter, $scriptureparts['chapter'], $versus_no);
				}
			}
		} 
		return $result;
	}

	/**
	 *
	 * @param type $scripture
	 * @return type
	 */
	private static function _getVersusFromBiblija($scripture_chapter, $chapter_withoutperiod, $versus_no) {
		$result = array();

		// for language
		$lg = &JFactory::getLanguage();
		$defaultLang = $lg->getTag();
		$lang = explode('-', $defaultLang);

		if ($lang[0]) {
			$lng = $lang[0];
		} else {
			// Have set default language as nl
			$lng = 'nl';
		}
		// Getting bible version
		$bible = array();
		$bible = self::GetMyBible();
		if ($bible['id']) {
			$bid = "id" . $bible['id'];
		} else {
			$bid = 'id18';
		}
		//make scripture to be requested
		$scripture_request = $chapter_withoutperiod.','.$versus_no;
		// getting content from biblija site
		$html = file_get_html('http://www.biblija.net/biblija.cgi?m=' . urlencode($scripture_request) . '&' . $bid . '=1&l=' . $lng . '&set=10');
		$innerHtml = $html->find('td.text', 0)->outertext;
		if ($innerHtml) {
			$strhtml = str_get_html($innerHtml);
		    foreach ($strhtml->find('img') as $element) {
                $element->src = '';
                $element->name = '';
            } 
            foreach ($strhtml->find('a') as $element) {
                $element->href = '';
                $element->title = '';
                $element->innertext = '';
            } 
			//$plainText = utf8_decode($strhtml->find('td.text', 0)->plaintext); 
			$plainText =  $strhtml->find('td.text', 0)->plaintext; 
			$plainText= preg_replace("/<img[^>]+\>/i", " ", $plainText);
			$plainText = preg_replace("/<a[^>]+\>/i", "", $plainText);

			$plainText = htmlspecialchars($plainText); 
			$plainText = str_replace($versus_no,'',$plainText);
			$plainText = str_replace('Verwijzing(en)', '', $plainText);
			$plainText = str_replace('[]','',$plainText);

			if (!empty($plainText)) {
				$db = JFactory::getDBO();
				$bibleversus = &JTable::getInstance('Joomlurgy_bibleverses', 'JoomlurgyTable');//getting the table object,
				$data = array();
				$data['bible'] = $bible['title'];
				//$data['versus'] = mysql_real_escape_string($plainText);
				$data['versus'] = $plainText;
				$data['versus_number'] = $versus_no;
				$data['chapter'] = $scripture_chapter;
				if (!$bibleversus->save($data)) {
					$this->setError(JText::sprintf('COM_JOOMLURGY_VERSUS_SAVED_ERROR', $bibleversus->getError()));
					return false;
				}
				$bible = array();
				$user = & JFactory::getUser();
				if (!$user->guest) {

					$bible = self::GetMyBible();
				} else {

					$bible['title'] = 'NBV';
				}
				$query = '';
				$query = 'select * from #__joomlurgy_bibleverses as jb where jb.bible = ' . $db->Quote($bible['title']) . ' and jb.chapter =' . $db->Quote($scripture_chapter). ' and jb.versus_number ='.$versus_no.' LIMIT 1';
				$db->setQuery($query); 
				$scripture_content = $db->loadObject();
				if (!empty($scripture_content->id)) {

					$result = $scripture_content;
				}
			}
		} else {
			$result = false;
		}
		return $result;
	}

	/**
	 *
	 * @return string
	 */
	public static function GetMyBible() {

		$user = & JFactory::getUser();
		$query = "SELECT `cb_voorkeurbijbelvertaling` FROM `#__comprofiler` WHERE `user_id`=" . $user->id;
		$db = & JFactory::getDBO();
		$db->setQuery($query);
		$bijbelversie = $db->loadResult();

		$versie = array();
		switch ($bijbelversie) {
			case "Willibrord '95" :
				$versie['id'] = "35";
				$versie['title'] = "WV95";
				break;
			case "Nieuwe Bijbelvertaling" :
				$versie['id'] = "18";
				$versie['title'] = "NBV";
				break;
			case "Naardense bijbel" :  // One english
				$versie['id'] = "32";
				$versie['title'] = "CEV";
				break; // Site Naardense Vertaling !!
			case "Statenvertaling (Jongbloed)" :
				$versie['id'] = "37";
				$versie['title'] = "SV-J";
				break; // Vertrekken vanaf Biblija.net !!
			case "NBG-1951" :
				$versie['id'] = "16";
				$versie['title'] = "NBG51";
				break;
			case "Groot Nieuws Bijbel '96" :

				$versie['id'] = "17";
				$versie['title'] = "GNB96";
				break;
			case "Vulgaat, 1592" :

				$versie['id'] = "38";
				$versie['title'] = "VLC";
				break;

			default :

				$versie['id'] = "18";
				$versie['title'] = "NBV";
				break;
		}
		return $versie;
	}

	/**
	 *
	 * @param type $versie
	 * @return boolean
	 */
	public static function GetCopyright($versie = "wbv") {
		$query = "SELECT `copyright` FROM `#__joomlurgy_bibles` WHERE `kort`='" . $versie . "'";
		$db = & JFactory::getDBO();
		$db->setQuery($query);
		$result = $db->loadResult();
		if (!$result == NULL) {
			return $result;
		} else {
			return false;
		}
	}
	/*
	 * function for getting the scripture clean and divide it into parts
	 * @params original scripture
	 */
	private static function _getscripturesverses($scripturee)
	{
		//split a chapter
		$original_sripture = explode(',', $scripturee);
		$newscripture = array();
		$newscripture['chapter'] = $original_sripture[0];
		//split the versus by dot(.) for multiple cases
		$multiple_scripture_versus = explode('.', $original_sripture[1]);
		$newscripture['versus'] = $multiple_scripture_versus;
		return $newscripture;
	}
	/*
	 * function for finding the range and checking the character - is exists
	 * @params versus
	 */
	private static function _getscripturesversesrange($mergedversus)
	{
		$pos = strpos($mergedversus, '-');
		if($pos)
		{
			$explodeversus = explode('-', $mergedversus);//checking the last versus no. is greater or less than from frirst, do handling according that.
			if($explodeversus[0] > $explodeversus[1])
			{
				$versus_range_array['start'] = $explodeversus[1];
				$versus_range_array['end'] = $explodeversus[0];
			}else{
				$versus_range_array['start'] = $explodeversus[0];
				$versus_range_array['end'] = $explodeversus[1];
			}
			return $versus_range_array;
		}
		else{
			$versus_range_array['start'] = $mergedversus;
			$versus_range_array['end']   = $mergedversus;
		}
		return $versus_range_array;
	}
	/*
	 * function for finding the original name of the chapter fro scripture.
	 * @params scripture(original)
	 */
	private static function _getscriptureschapter($scripture_original)
	{
		//split a chapter
		$original_sripture = explode(',', $scripture_original);
		$scripturechapter = $original_sripture[0];
		return $scripturechapter;
	}
}


