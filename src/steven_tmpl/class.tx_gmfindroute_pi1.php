<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Tobias Schwandt <t.schwandt@zebresel.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Google Maps Find&Route' for the 'gmfindroute' extension.
 *
 * @author	Tobias Schwandt <t.schwandt@zebresel.de>
 * @package	TYPO3
 * @subpackage	tx_gmfindroute
 */
class tx_gmfindroute_pi1 extends tslib_pibase {
    var $prefixId      = 'tx_gmfindroute_pi1';		// Same as class name
    var $scriptRelPath = 'pi1/class.tx_gmfindroute_pi1.php';	// Path to this script relative to the extension dir.
    var $extKey        = 'gmfindroute';	// The extension key.
    var $pi_checkCHash = true;

    /**
     * Main method of your PlugIn
     *
     * @param	string		$content: The content of the PlugIn
     * @param	array		$conf: The PlugIn Configuration
     * @return	The content that should be displayed on the website
     */
    function main($content, $conf)	
    {
        switch((string)$conf['CMD'])	
        {			
            default:
                if (strstr($this->cObj->currentRecord,'tt_content'))	
                {
                    $conf['pidList'] = $this->cObj->data['pages'];
                    $conf['recursive'] = $this->cObj->data['recursive'];
                }
                return $this->pi_wrapInBaseClass($this->view($content, $conf));
            break;
        }
    }

    /**
     * Shows the standard view for google maps with all entries
     *
     * @param	string		$content: content of the PlugIn
     * @param	array		$conf: PlugIn Configuration
     * @return	HTML            Output for this plugin
     */
    function view($content, $conf) 
    {
        $this->conf = $conf;		// Setting the TypoScript passed to this function in $this->conf
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();		// Loading the LOCAL_LANG values

        $lConf = $this->conf['view.'];	// Local settings for the listView function

        // Get number of records:
        $res = $this->pi_exec_query('tx_gmfindroute_locations',1);
        list($this->internal['res_count']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

        // Make listing query, pass query to SQL database:
        $res = $this->pi_exec_query('tx_gmfindroute_locations');
        $this->internal['currentTable'] = 'tx_gmfindroute_locations';

        
        $content='';
        # $content.=t3lib_div::view_array($this->piVars);	// DEBUG: Output the content of $this->piVars for debug purposes. REMEMBER to comment out the IP-lock in the debug() function in t3lib/config_default.php if nothing happens when you un-comment this line!


        //Add JS
        //TODO: Add hook to add JS to header
        $content .= '<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=true"></script>';
		
		
		// Added by SL - 2011 - 03 - 25
		$content .= "<link href='http://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>";
        
		
		//TODO: JS dynamisch hinzuladen
        $content .= '<script type="text/javascript" src="typo3conf/ext/gmfindroute/pi1/js/google_maps_api.js"></script>';

        $content .= '
        <script type="text/javascript">window.onload = function () {
  			initialize()
		}</script>';
        
        //Add Content
        $content .= '
        
        <div class="ext_maps_google">
	    <div id="map_canvas" class="map_canvas"></div>
		
	    <div class="form_content">
		
			<div id="InformationBox"><img src="typo3conf/ext/gmfindroute/pi1/img/Edit_Location.png" alt="Stift-Icon"/><span>Bestimme deinen Standort selbst</span></div>
		
			<div class="box margin locations">
				<!--<form action="#">-->
				<img src="typo3conf/ext/gmfindroute/pi1/img/Start_Location.png" alt="Standpunkt" />
				<input type="text" id="city_name" class="city_name" onchange="OnChangeInput();" placeholder="Deine Stadt: Berlin, DE?" />
				<br />
				<img src="typo3conf/ext/gmfindroute/pi1/img/Destination_Location.png" alt="Zielort" />
				 <select onchange="OnChangeTarget();" id="target">
				  <option value="0">Campus, Altonaer Strasse</option>
				  <option value="1">Campus, Leipziger Strasse</option>
				  <option value="2">Campus, Steinplatz</option> 
				  <option value="3">Campus, Schl&uuml;terstrasse</option>
				</select>
		  	 </div>
		</div>
				 
			 <div class="box travelmode">
				 <label for="mode">Fortbewegungsmittel</label>
				 
				 <div class="modes">
					 <div id="DRIVING" class="active" onClick="OnChangeTravelMode(\'DRIVING\');">driving</div>
					 <div id="BICYCLING" onClick="OnChangeTravelMode(\'BICYCLING\');">bike</div>
					 <div id="WALKING" onClick="OnChangeTravelMode(\'WALKING\');">walk</div>
				 </div>
				 
				 <!--<select onchange="OnChangeTravelMode();" id="mode">
				  <option value="DRIVING">Auto</option>
				  <option value="WALKING">Laufen</option>
				  <option value="BICYCLING">Fahrrad</option> 
				</select>-->
			</div>
			<!--
			<input type="button" onclick="calcRoute();" value="Route berechnen" />
			-->
		  	<!--</form>-->
	  
	  	
		<div class="map_information" id="map_information">
			<div class="clear"></div>
			
			
			<div class="box routeinformation">
				<div id="title">Anreiseinformationen</div>
				<div id="distance"><img src="typo3conf/ext/gmfindroute/pi1/img/Distance.png" alt="Entfernung"/> <div class="length"><span>Strecke:</span> <nobr id="route_distance" class="route_distance">Unbekannt.</nobr></div></div>
				<div id="time"><img src="typo3conf/ext/gmfindroute/pi1/img/Time.png" alt="Dauer"/><span>Dauer:</span> <nobr id="route_time" class="route_time">Unbekannt.</nobr></div>
			</div>
			
			<div class="clear"></div>
			
			
				
			<div class="box link_to_google margin"><a id="google_link" target="_blank" href="http://maps.google.de/maps?saddr=Berlin&daddr=Erfurt">Details zu Route in Google Maps anzeigen.</a></div>
			<div class="box link_to_bahn"><a id="train_link" target="_blank" href="http://www.bahn.de">Strecke mit der Deutschen Bahn zur&uuml;cklegen</a></div>
	
		</div>
        
      ';
        

        return $content;
    }

    function getGoogleLink($start, $end)
    {
        return '<a href="http://maps.google.de/maps?saddr=' . $start . '&daddr=' . $end . '">LINK</a>';
    }

    function getDBLink($start, $end)
    {
        return '';
    }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gmfindroute/pi1/class.tx_gmfindroute_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gmfindroute/pi1/class.tx_gmfindroute_pi1.php']);
}

# $GLOBALS['TSFE']->pSetup['includeJS.'][$this->extKey] = 'typo3conf/ext/gmfindroute/pi1/js/google_maps_api.js';
$GLOBALS['TSFE']->pSetup['includeCSS.'][$this->extKey] = 'typo3conf/ext/gmfindroute/pi1/css/style.css';

?>