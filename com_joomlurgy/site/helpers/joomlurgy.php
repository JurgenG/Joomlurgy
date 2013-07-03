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

        if ($scripture && $scripture != '-') {
            $db = & JFactory::getDBO();

            $db->setQuery('select * from #__joomlurgy_bibleverses as jb where jb.bible = ' . $db->Quote($bible['title']) . ' and jb.perikope =' . $db->Quote($scripture));
            $scripture_content = $db->loadObjectList();
            if (!empty($scripture_content)) {

                $result = $scripture_content;
            } else {

                $result = self::_getVersusFromBiblija($scripture);
            }

            return $result;
        }
    }

    /**
     * 
     * @param type $scripture
     * @return type
     */
    private static function _getVersusFromBiblija($scripture) {
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

        // change scripture with . to validate scripture string to correct
        $pattern = '/(\w{2,})\./';
        $replacement = '${1}';
        $scripture1 = preg_replace($pattern, $replacement, $scripture, 1);

        // Getting bible version
        $bible = array();
        $bible = self::GetMyBible();
        if ($bible['id']) {
            $bid = "id" . $bible['id'];
        } else {
            $bid = 'id18';
        }

        // getting content from biblija site
        $html = file_get_html('http://www.biblija.net/biblija.cgi?m=' . urlencode($scripture1) . '&' . $bid . '=1&l=' . $lng . '&set=10');
       // echo 'http://www.biblija.net/biblija.cgi?m=' . urlencode($scripture1) . '&'.$bid.'=1&l='.$lng.'&set=10';
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
            //echo $strhtml; die;
            // $innerHtml = $strhtml->find('td.text', 0)->innertext;
            $plainText = utf8_decode($strhtml->find('td.text', 0)->plaintext);
            // echo $plainText = preg_replace("/[^\p{Latin} ]/u", "", $plainText);
            $plainText = preg_replace('/[^\w\d_ -]/si', '', $plainText);


            $verses = preg_split("/\s\d+\s/", $plainText);

            $versesNumbers = array();
            $i = 0;
            foreach ($html->find('span.v') as $element) {
                $versesNumbers[$i] = $element->innertext;
                $i++;
            }
//        $versesNumberRegEx = '/<span \b[^>]*>(.*?)<\/span>/s';
//        preg_match_all($versesNumberRegEx, $innerHtml, $versesNumbers);


            for ($i = 0; $i < count($verses); $i++) {
                if (!preg_match('/[a-zA-Z]+/', $verses[$i])) {

                    unset($verses[$i]);
                } else {
                    $verses[$i] = str_replace(chr(160), '', trim($verses[$i]));
                }
            }

            $versesNumbers = array_map(function($a) {
                        if (preg_match('/\d+/', $a, $match)) {
                            return $match[0];
                        } else {
                            return trim($a);
                        }
                    }, $versesNumbers);


//            var_dump($verses);
//            var_dump($versesNumbers);
            if (!empty($verses) && !empty($versesNumbers)) {
                if (count($verses) == count($versesNumbers) || (count($verses) > count($versesNumbers)) || (count($versesNumbers) > count($verses))) {
                    if (count($verses) > count($versesNumbers)) {
                        $diff = count($verses) - count($versesNumbers);
                        for ($i = 1; $i <= $diff; $i++) {
                            $first_key = key($verses);
                            unset($verses[$first_key]);
                        }
                    }
                    if (count($versesNumbers) > count($verses)) {
                        $diff = count($versesNumbers) - count($verses);
                        for ($i = 1; $i <= $diff; $i++) {
                            $first_key = key($versesNumbers);
                            unset($versesNumbers[$first_key]);
                        }
                    }
                    $result = array_combine($versesNumbers, $verses);
                }
            }

            if ($result) {
                $db = & JFactory::getDBO();
                $chapter = explode(',', $scripture);


                foreach ($result as $verse_number => $verses) {


                    $query = "INSERT INTO " . $db->nameQuote('#__joomlurgy_bibleverses')
                            . " (" . $db->nameQuote('id') . "," . $db->nameQuote('bible')
                            . "," . $db->nameQuote('perikope') . "," . $db->nameQuote('content')
                            . "," . $db->nameQuote('versus') . "," . $db->nameQuote('versus_number') . "," . $db->nameQuote('chapter') . ") VALUES (NULL," . $db->Quote($bible['title'])
                            . "," . $db->Quote($scripture) . "," . $db->Quote('') . "," . $db->Quote($verses) . "," . $db->Quote($verse_number) . "," . $db->Quote($chapter[0]) . ")";

                    $db->setQuery($query);
                    $db->query();
                }

                $bible = array();
                $user = & JFactory::getUser();
                if (!$user->guest) {

                    $bible = self::GetMyBible();
                } else {

                    $bible['title'] = 'NBV';
                }
                $db->setQuery('select * from #__joomlurgy_bibleverses as jb where jb.bible = ' . $db->Quote($bible['title']) . ' and jb.perikope =' . $db->Quote($scripture));
                $scripture_content = $db->loadObjectList();
                if (!empty($scripture_content)) {

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

}

