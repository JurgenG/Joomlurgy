<?php
defined('_JEXEC') or die('Restricted access');

class perikope {
	
/** Deze functie splitst een complexe perikope op in meerdere aaneengesloten perikopes
 *
 * @param string $perikope De perikope van keuze
 * @return array/boolean Een array van perikopes, bij ongeldige input een FALSE 
 **/
public static function Explode($perikope) {

	$perikope = preg_replace('/\s/','',$perikope); //spaties verwijderen
	$regex = '/(?<book>\w+)\.(?<verses>[0-9a-f,:\.-]+)/i';
	if ($test = preg_match($regex, $perikope,$regs)) {
		$book = $regs['book'];
		$verses = trim($regs['verses']);
	}else{
		// Niet verder zoeken - ongeldige perikope.
		return false;
	}

	$split = preg_match_all('/(?:([1-9][0-9a-z]*)([,\.-]|$))/',$verses,$matches);
	if($split > 0) {
		// Geldig resultaat
		$kommas=0;
		$minus =0;
		$dot=0;
		foreach($matches[2] as $key=>$match) {
			if($match==',') { $kommas = $kommas+1 ; }
			if($match=='-') { $minus = $minus+1 ; }
			if($match=='.') { $dot = $dot+1 ; }
			$matches[1][$key] = (int) $matches[1][$key];  // deelverzen verwijderen
		}
		if($dot==0) {
			/* 
		 	*    1e optie: één aaneensluitend geheel.
		 	* 				dit kan zo verwerkt worden door de site.
		 	*/
				$perikopes[] = $perikope;
		} else {
			if($kommas > 0) { 
			/* 
		 	*    2e optie: de perikope is gespreid over meerdere delen.
		 	* 				dit moet opgesplitst worden in aparte aansluitende perikopes
		 	*/
			$chapter  = $matches[1][0];
			$passage = $book . "." . $chapter;
			foreach($matches[2] as $key=>$match) {
			      if($match==",") {
			    	$passage .= "," . $matches[1][$key+1];
			    	$chapter  = $matches[1][$key];
			} elseif($match=="-") {
					$passage .= "-" . $matches[1][$key+1];
				
			} elseif($match==".") {
					$perikopes[] = $passage;
					$passage = $book . "." . $chapter . "," . $matches[1][$key+1];
			} elseif($match=="") {
					$perikopes[] = $passage;
			}
		} // end foreach
			
		} else { $perikopes= false;
		} // end if($kommas) else...
	} // end if($dot) else ...
} // end if($split)
return $perikopes;
} // end function Explode()

/** De tekst van de gekozen perikope opvragen (lokaal of online)
 *
 * @param string $perikope De op te zoeken perikope
 * @param string $versie De gebruikte bijbelvertaling: wbv = Willibrord - nbv = Nieuwe Bijbelvertaling
 * @param boolean $limiet De wettelijke limiet van max. 50 verzen respecteren
 * @return array Elk vers wordt in een aparte cel geplaatst met het de referentie (hoofdstuk,vers) als key
 **/
public static function GetText($referentie,$versie="wbv",$limiet=true) {
	//TODO Explode functie integreren...
	//TODO Enkele perikopes controleren op juist bereik (niet tot einde hoofdstuk!)
	//TODO Scheiden verschillende bijbelvertalingen via externe documenten (meer dan 2 mogelijk)
	// Validatie geldige invoer voor $versie - standaard wbv
	// Bijhorende copyright weergeven toekennen
	if(strtolower($versie)=='nbv') {
		$tekst['versie']='nbv';
		$tekst['copyright']='&copy; Nederlands Bijbelgenootschap, Haarlem, 2004.';
		$auteur='<a href="mailto:info@bijbelgenootschap.nl">Nederlands Bijbelgenootschap</a><br />Postbus 620<br />2003 RP Haarlem<br />T +31 (0)23 514 61 46<br />F +31 (0)23 534 20 95<br />';
	} else {
		$tekst['versie']='wbv';
		$tekst['copyright'] = "&copy; Katholieke Bijbelstichting, 's-Hertogenbosch, 1995."; 
		$auteur='<a href="mailto:info@rkbijbel.nl">Katholieke Bijbelstichting</a><br />Postbus 1274<br />5200 BH \'s-Hertogenbosch<br />T +31 (0)73 613 32 20<br />F +31 (0)73 691 01 40';
	}
	
	// Waarschuwingstekst limiet weergeven
	$tekst['waarschuwing'] = "Er mogen niet meer dan 50 verzen geciteerd worden zonder schriftelijke toestemming!<br />";
	$tekst['waarschuwing'] .= "Indien u langere teksten wil overnemen op een document, gelieve contact op te nemen met:<br />";
	$tekst['waarschuwing'] .= $auteur; 
	if($referentie=="")
	{
		return "Blanco Referentie";
	} else {
		$tekst['passage'] = $referentie;
		$tekst['inhoud'] = array();
		$tekst['fout'] = array();
	
		$query = "SELECT * FROM #__joomlurgy_bibleverses WHERE bible='" . $tekst['versie'] . "' AND perikope='" . $tekst['passage']. "'";
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$row = $db->loadObject();
		if(is_object($row)) {
			$tekst['inhoud'] = unserialize($row->content);
		} else {
			if($perikope = perikope::Explode($referentie))
			{   // Explode geeft "false" bij ongeldige perikope
				// per apart deel wordt de tekst toegevoegd.
				foreach($perikope as $perikopedeel) {
					if($perikopedeel <> "") {
							$tekst['URI']= "http://www.willibrordbijbel.nl/?j=". $perikopedeel ."&".$tekst['versie']."=on";			
						// Use curl to validate existance of site
						$ch = curl_init($tekst['URI']);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				
						if(curl_exec($ch) === false)
						{
						    $tekst['fout'] = 'Curl error: ' . curl_error($ch);
							$tekst['inhoud'] = "Ophalen tekst mislukt";
						} else {
							$inhoud = file_get_contents($tekst['URI']);
							$verwerking = perikope::_stripTekst($inhoud, $perikopedeel,$limiet);
							if(is_array($verwerking)){
								$tekst = array_merge_recursive($tekst, $verwerking);
							}
					}
				}
				// Close handle
				curl_close($ch);
				} // einde van foreach(perikope as perikopedeel)
				if(!$tekst['passage']==""  && !$tekst['versie']=="" && !$tekst['inhoud']=="a:0:{}") {
					$querydump = (serialize($tekst['inhoud']));
					$query = "INSERT INTO " . $db->nameQuote('#__joomlurgy_bibleverses') . " (" . $db->nameQuote('id') . "," . $db->nameQuote('bible') . "," . $db->nameQuote('perikope') . "," . $db->nameQuote('content') . ") VALUES (NULL,". $db->Quote($tekst['versie']) . "," . $db->Quote($tekst['passage']) . "," . $db->Quote($querydump) . ")";
					$db =& JFactory::getDBO();
					$db->setQuery($query);
					$db->query();
				}
	
			} else { // Een ongeldige perikope werd ingegeven
				$tekst['fout'] = 3;
				$tekst['inhoud'] = "Ongeldige perikope";
			}
		}
		return $tekst;
	}
}// end function GetText()

/** HTML-Ingedeelde tekst uitvoeren
 * 
 * Een HTML-uitvoer van de gevraagde perikope waarbij versnummer en tekst in
 * eigen span-containers bewaard worden.
 * @param string $referentie De perikope die gezocht wordt
 * @param boolean $toonverzen Standaard worden de verzen niet weergegeven
 * @param string $versie Standaard wordt de willibrordvertaling (wbv) gegeven.
 *                       Dit kan ook de Nieuwe bijbelvertaling (nbv) zijn.
 * @param boolean $limiet De wettelijke beperking van max. 50 verzen wordt
 *                        standaard gerespecteerd
 * @return string HTML-code voor weergave van de bijbelperikope 
 *
 */
public static function GetVerses($referentie, $toonverzen=false, $versie="wbv", $newline=false, $limiet=true)
{
	$gospel = perikope::GetText($referentie, $versie, $limiet);
	$gospel = $gospel['inhoud'];
	$gospeltext = '<div>';
	if(!is_array($gospel)) {
		$gospeltext .= "Foutieve verwijzing naar bijbelperikope: " . $referentie . ". Gelieve dit te melden aan de webmaster!";
	} else {
	foreach($gospel as $key=>$verse) {
		$gospeltext .= $newline ? "<p>\n" : "";
		$gospeltext .= ($toonverzen ? '<span class="verse">['.$key . ']</span>' : '');
		$gospeltext .= '<span class="text">' . $verse . '</span>';
		$gospeltext .= $newline ? "</p>\n" : "";
	}
	$gospeltext .= '</div>';
	return $gospeltext;
	}
	
}

public static function GetCopyright($versie="wbv")
{
	$query = "SELECT `copyright` FROM `#__joomlurgy_bibles` WHERE `kort`='" . $versie . "'";
	$db =& JFactory::getDBO();
	$db->setQuery($query);
	$result = $db->loadResult();
	if(!$result==NULL) {return $result; } else { return false; }
}

public static function GetMyBible()
{
	// TODO: if not logged in or no CB found - select default bible (wbv)

		$user =& JFactory::getUser();
		$query = "SELECT `cb_voorkeurbijbelvertaling` FROM `#__comprofiler` WHERE `user_id`=" . $user->id ;
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$bijbelversie = $db->loadResult();
		switch($bijbelversie) {
			case "Willibrord '95"				:	$versie = "wbv"; break;
			case "Nieuwe Bijbelvertaling"		:	$versie = "nbv"; break; // TODO: implementeren andere vertalingen
			case "Naardense bijbel"				:	$versie = "nav"; break; // Site Naardense Vertaling !!
			case "Statenvertaling (Jongbloed)"	:	$versie = "svj"; break; // Vertrekken vanaf Biblija.net !!
			case "NBG-1951"						: 	$versie = "nbg51"; break;
			case "Groot Nieuws Bijbel '96"		:	$versie = "gnb"; break;
			case "Vulgaat, 1592"				:	$versie = "vul"; break;
			
			default								:	$versie = "wbv"; break;
		}
		return $versie;
}

/** Hoofdstuk en vers van eerste en laatste vers van een perikope worden teruggekeerd
 * 
 * @param string $perikope De af te bakenen perikope (aansluitend!)
 * @return array
 *  
 **/
private static function _geefGrenzen($perikope) {
	$perikope = preg_replace('/\s/','',$perikope); //spaties verwijderen
	$regex = '/(?<book>\w+)\.(?<verses>[0-9a-f,:\.-]+)/i';
	if ($test = preg_match($regex, $perikope,$regs)) {
		$passage['boek']	= $regs['book'];
		$passage['verzen']	= trim($regs['verses']);
	}else{
		$passage = false;
	}
$split = preg_match_all('/(?:([1-9][0-9a-z]*)([,\.-]|$))/',$passage['verzen'],$matches);
if($split > 0) {
	// Geldig resultaat
	$kommas=0;
	$minus =0;
	$dot=0;
	foreach($matches[2] as $key=>$match) {
		if($match==',') { $kommas = $kommas+1 ; }
		if($match=='-') { $minus = $minus+1 ; }
		$matches[1][$key] = (int) $matches[1][$key];  // deelverzen verwijderen
		}
	}
	switch($kommas) {
		case 0:	// allemaal volledige hoofdstukken
			$beginvers	= 1;
			$eindvers	= 9999;
			$beginhoofdstuk = $matches[1][0];
			$eindhoofdstuk	= ($minus == 1 ? $matches[1][1] : $matches[1][0]);
			break;

		case 1:	// allemaal binnen hetzelfde hoofstuk
			$beginvers	= $matches[1][1];
			$eindvers	= ($minus==1 ? $matches[1][2] : $matches[1][1]);
			$beginhoofdstuk	= $matches[1][0];
			$eindhoofdstuk	= $matches[1][0];
			break;
			
		case 2: // gespreid over meerdere hoofdstukken
			$beginvers	= $matches[1][1];
			$eindvers	= $matches[1][3];
			$beginhoofdstuk	= $matches[1][0];
			$eindhoofdstuk	= $matches[1][2];
			break;
			
		default: // is in principe onmogelijk
			$beginvers	= 9999;
			$eindvers	= -1;
			$beginhoofdstuk	= 9999;
			$eindhoofdstuk	= -1;
	}
	$passage['start']['vers'] 	= $beginvers;
	$passage['start']['hoofdstuk']	= $beginhoofdstuk;
	$passage['einde']['vers']	= $eindvers;
	$passage['einde']['hoofdstuk']	= $eindhoofdstuk;
	return $passage;
}

/** Alle overtollige tekst verwijderen
 * 
 * Een webpagina van www.willibrordbijbel.nl wordt ontdaan van alle pagina-opmaak van de website.
 * De tekst wordt uitgevoerd in een array
 * 
 * @param $perikope string De HTML-tekst van de willibrord site
 * @param $passage string De (aaneengesloten) perikope beschrijving (bijv. "Lc. 15,1-5)
 * @param $limiet boolean Bij "true" worden slechts de 50 maximaal toegelaten verzen vrij getoond (c)
 * @return $array key=hoofdstuk,vers - value = vers-inhoud
 * 
 **/
private static function _stripTekst($perikope, $passage, $limiet=true) {

	$Grenzen = perikope::_geefGrenzen($passage);
		
	$changes = "";  // reset $elements
	// HTML-elementen die aangepast moeten worden
	//===========================================
	// Zoeken naar een meer uniforme oplossing om teksten in deze span in uppercase te plaatsen
	$changes['heer']['S']		='/\<span class="gn"\>heer\<\/span\>/Ui';
	$changes['heer']['T']		='HEER';
	$changes['god']['S']		='/\<span class="gn"\>god\<\/span\>/Ui';
	$changes['god']['T']		='GOD';
	$changes['opener']['S']		='/\<span class="gn"\>/U';
	$changes['opener']['T']		='!!--';
	// afsluitende span elementen worden verwijderd in volgende filterstap
		
	// code opruimen
	foreach($changes as $change) {
		$perikope = preg_replace($change['S'], $change['T'], $perikope);
	}	

	$elements = "";  // reset $elements
	// HTML-elementen die volledig geschrapt moeten worden uit de code
	//================================================================
	$elements['header1']	= '/\<!DOCTYPE.*\>/';					// <!DOCTYPE ...
	$elements['header2']	= '/\<html\>\<head\>.*\<\/head\>/s'; 			// <html><head>...</head>
	$elements['body']      	= '/\<body.*\>/'; 					// strip <body ...>
	$elements['scripts']   	= '/\<script.*\<\/script\>/sU';				// strip <script...>...</script>
	$elements['right']	= '/\<div class="rightBox"\>.*\<\/div\>/sU';		// strip <div class="rightbox">...</div>
	$elements['navigation']	= '/\<div id="navlyr".*\<\/div\>/sU';			// strip <div id="navvlyr"...>...</div>
	$elements['links']	='/\<\/?a.*\>/sU';					// strip <a...> en </a>
	$elements['footer']	='/\<\/body\>.*\<\/html\>/s';				// strip </body>...</html>
	$elements['tables']	='/\<\/?t.*\>/U';					// strip <t...> en </t...>
	$elements['images']	='/\<img.*\>/U';					// strip <img...>
	$elements['asterisks']	='/\*/U';						// strip "*"
	$elements['stylespans']	='/\<span style=.*\>/U';				// strip <span syle=...>
	$elements['spanclose']	='/\<\/span\>/U';					// strip </span>
	$elements['divs']	='/<\/?div.*>/U';					// strip <div...>
	$elements['forms']	='/\<form.*\<\/form\>/sU';				// strip <form...>...</form>
	$elements['copyright']	='/Klik hier voor de richtlijnen.*\d\d\d\d\./'; 	// strip copyright link
	$elements['linebreaks'] ='/\<br.*>/U';						// strip <br /> en <br>
	$elements['redtext']	='/\<(font color="red"|\/font)\>/U';			// strip <font color="red"> en </font>
	$elements['beginning']	='/.*\<\/nobr\>/sU';					// strip begin tekst tot </nobr>
	$elements['newline']	='/(\r|\n)/U';						// strip <CR> en <LF> (apart voor Linux/Windows/Mac)
	$elements['spaces']	='/&nbsp;/';						// strip hard spaces
	$elements['bold']	='/\<\/?b\>/i';						// strip <b> en </b>
	$elements['perknaam']	='/\<span class="perknaam"\>.+(?=\<span)/U';		// strip <span class="perknaam">...
	$elements['disclaimer'] ='/Disclaimer/';					// strip 'Disclaimer'
	
	// code opruimen
	foreach($elements as $key=>$element) {
		$perikope = preg_replace($element, '', $perikope);
	}
	$perikope .= "<span>";  // extra <span> om laatste vers ook te vinden in tekst
	
	// tekst splitsen in een array van verzen
	$regex = '/\[(?<nr>\d+)\]\s*(?<vers>.+)(?=\s?\<span)/U';
	$output = preg_match_all($regex, $perikope, $matches);

	// array van gewenste indeling voorzien
	$versnummer = 0;								// versnummer initialiseren
	// Beginwaarden instellen
	$hoofdstuk = $Grenzen['start']['hoofdstuk'];
	if (($Grenzen['start']['hoofdstuk']==$Grenzen['einde']['hoofdstuk']) AND ($Grenzen['start']['vers']==$Grenzen['einde']['vers'])) {
		foreach($matches['vers'] as $key=>$value) {
			if($matches['nr'][$key]== $Grenzen['start']['vers'])
			{ $arrVerzen['inhoud'][$Grenzen['start']['hoofdstuk'].",".$Grenzen['start']['vers']] = $value; }
		}
	} else {
		foreach($matches['vers'] as $key=>$value) {
			if ($matches['nr'][$key] < $versnummer) { //we zitten in het volgende hoofdstuk
				$hoofdstuk = $hoofdstuk + 1;
			}
			$versnummer = $matches['nr'][$key];
			if ($hoofdstuk == $Grenzen['start']['hoofdstuk']) { // beginnen vanaf $startvers
				if(($versnummer >= $Grenzen['start']['vers']) AND ($versnummer <= $Grenzen['einde']['vers'] OR $hoofdstuk < $Grenzen['einde']['hoofdstuk'])) {
					$arrVerzen['inhoud'][$hoofdstuk.",".$versnummer] = $value;
				}
			} elseif ($hoofdstuk < $Grenzen['einde']['hoofdstuk']) { // gewoon alle verzen opnemen
					$arrVerzen['inhoud'][$hoofdstuk.",".$versnummer] = $value;
			} elseif ($hoofdstuk == $Grenzen['einde']['hoofdstuk']) {
				if($versnummer <= $Grenzen['einde']['vers']) {
					$arrVerzen['inhoud'][$hoofdstuk.",".$versnummer] = $value;
				} // niet verder gaan dan laatste vers
			} else { // dit is in principe onmogelijk
				$arrVerzen['fout']	= 1;
			}
		}
	}
	//TODO Controleren $limiet functioneren...
	if($limiet) {
		if(count($arrVerzen['inhoud'])>50) {
			do { // laatste element van het array verwijderen...
				array_pop($arrVerzen['inhoud']);
			} while(count($arrVerzen['inhoud'])>50); // ... tot er maar 50 resteren.
			$arrVerzen['inhoud']['...'] .="<b>Er werden verzen verwijderd.</b><br />\n";
			$arrVerzen['fout']	= 2;
		}
	}
	return $arrVerzen;
} // end function _stripTekst()

} // end class
?>