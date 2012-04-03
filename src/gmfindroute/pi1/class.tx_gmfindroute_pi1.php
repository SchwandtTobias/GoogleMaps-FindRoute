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
        $this->conf = $conf;			// Setting the TypoScript passed to this function in $this->conf
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();				// Loading the LOCAL_LANG values
		$this->pi_initPIflexForm(); 	// Init flexform

        $lConf = $this->conf['view.'];	// Local settings for the listView function

        // Get number of records:
        $res = $this->pi_exec_query('tx_gmfindroute_locations',1);
        list($this->internal['res_count']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

        // Make listing query, pass query to SQL database:
        $res = $this->pi_exec_query('tx_gmfindroute_locations');
        $this->internal['currentTable'] = 'tx_gmfindroute_locations';

        
        $content='';
        # $content.=t3lib_div::view_array($this->piVars);	// DEBUG: Output the content of $this->piVars for debug purposes. REMEMBER to comment out the IP-lock in the debug() function in t3lib/config_default.php if nothing happens when you un-comment this line!

		//Add CSS
		$GLOBALS['TSFE']->pSetup['includeCSS.'][$this->extKey] = $this->conf['includeCSS'];

        //Add JS
		//$GLOBALS['TSFE']->pSetup['includeJS.'][$this->extKey] = 'typo3conf/ext/gmfindroute/pi1/js/google_maps_api.js';
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] = '<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=true"></script>
<script type="text/javascript" src="typo3conf/ext/gmfindroute/pi1/js/google_maps_api.js"></script>';
		
 
		
		//Add placements
		$content .= '<script type="text/javascript">';
		$content .= '
			//Need Geocodes Static
			var TargetLocArray = new Array();
			var TargetStationArray = new Array();
			var TargetMarkerArray = new Array();
			var TargetTitleArray = new Array();
			';
		
		$iPos = 0;

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                'locationname, targetlat, targetlng, stationname, imgmarker',         // SELECT ...
                'tx_gmfindroute_locations',     // FROM ...
                'hidden = 0 AND deleted = 0',    // WHERE...
                '',            // GROUP BY...
                'sorting ASC'    // ORDER BY...
            );
		
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$content .= 'TargetLocArray[' . $iPos . '] = new google.maps.LatLng(' . $row['targetlat'] . ',' . $row['targetlng'] . ');';
			$content .= 'TargetStationArray[' . $iPos . '] = "' . $row['stationname'] . '";';
			$content .= 'TargetTitleArray[' . $iPos . '] = "' . $row['locationname'] . '";';
			
			if($row['imgmarker'] != NULL)
				$content .= 'TargetMarkerArray[' . $iPos . '] = "uploads/tx_gmfindroute/' . $row['imgmarker'] . '";';
			else
				$content .= 'TargetMarkerArray[' . $iPos . '] = "' . $this->conf['pathDefImgMarker'] . '";';
			
			++$iPos;
		}
		
		$countTargetElements = $iPos;
		
		$navigationControl = 0;
		if($this->getConfValue('sDEF', 'map_navigationControl', 0) == 0)
		{
			$navigationControl = 1;
		}
		
		//Standard configuration
		$content .= '
			//Set this for initialLocation
			TargetLoc = TargetLocArray[0];
			
			//Set standard configuration for Google Maps
			_mapZoom = ' . $this->getConfValue('sDEF', 'map_zoom', $this->conf['map.']['zoom']) . ';
			_navigationControl = ' . $navigationControl . ';
			
			//Standard output if no target is inside input field
			txtAlertPlace = "' . $this->conf['language.']['txt_alertNoStartingPoint'] . '";
		';
		$content .= '</script>';

		//Start extension on load
        $content .= '
        <script type="text/javascript">
		window.onload = function () {
  			initialize();
			';
		for($iPos = 0; $iPos < $countTargetElements; ++$iPos) {
			$content .= 'google.maps.event.addListener(EventClickArray[' . $iPos . '], "click", function() {
							 ClickMarkerToSetTarget("' . $iPos . '");
;						 });
		 				';
		}	
		
		$content .= '}
		</script>';
	
		
        //Add Content
        $content .= '
        
        <div class="ext_maps_google">
	    <div id="map_canvas" class="map_canvas" style="width: ' . $this->getConfValue('sDEF', 'map_size_width', $this->conf['map.']['width']) . 'px; height: ' . $this->getConfValue('sDEF', 'map_size_height', $this->conf['map.']['height']) . 'px"></div>
	    
	    <div class="form_content">
		    <form action="#">
		    <label for="city_name">' . $this->conf['language.']['label_start'] . '</label>
		  	<input type="text" id="city_name" class="text" onchange="OnChangeInput();" placeholder="' . $this->conf['language.']['placeholder_start'] . '" />
		  	
		  	<label for="target">' . $this->conf['language.']['label_target'] . '</label>
		  	 <select onchange="OnChangeTarget();" id="target" class="select">';
			 
			 $iPos = 0;
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                'locationname',         // SELECT ...
                'tx_gmfindroute_locations',     // FROM ...
                'hidden = 0 AND deleted = 0',    // WHERE...
                '',            // GROUP BY...
                'sorting ASC'    // ORDER BY...
            );
		
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$content .= '<option value="' . $iPos . '">' . $row['locationname'] . '</option>';
				++$iPos;
			}	
			 
			 $content .= '
			</select>
		  	 
		  	 <label for="mode">' . $this->conf['language.']['label_mode'] . '</label>
		  	 <select onchange="OnChangeTravelMode();" id="mode" class="select">
			  <option value="DRIVING">' . $this->conf['language.']['label_mode1'] . '</option>
			  <option value="WALKING">' . $this->conf['language.']['label_mode2'] . '</option>
			  <option value="BICYCLING">' . $this->conf['language.']['label_mode3'] . '</option> 
			</select>
			
			<input class="btn" type="button" onclick="calcRoute();" value="' . $this->conf['language.']['value_button'] . '" />
		  	</form>
	  	</div>
	  	
	  	<div class="more_information">
		  	<div class="map_information" id="map_information" style="display: none;">';
		  	//if($this->conf['showMoreRouteLength'] == 1)
			{
				$content .= '<div class="length">' . $this->conf['language.']['label_length'] . ' <nobr id="route_distance" class="route_distance">' . $this->conf['language.']['txt_unknown'] . '</nobr></div>';
			}
			
			if($this->getConfValue('sDEF', 'google_link', 0) == 0)
			{
				$content .= '<div class="link_to_google">' . $this->conf['language.']['label_google'] . ' <a class="external-link" id="google_link" target="_blank" href="http://maps.google.de/">' . $this->conf['language.']['link_google'] . '</a></div>';
			}
			
			if($this->getConfValue('sDEF', 'db_link', 0) == 0)
		  	{
				$content .= '<div class="link_to_bahn">' . $this->conf['language.']['label_db'] . ' <a class="external-link" id="train_link" target="_blank" href="http://www.bahn.de">' . $this->conf['language.']['link_db'] . '</a></div>';
			}
			
			
			$content .= '</div>
	  	</div>
	  </div>
        
      ';
        

        return $content;
    }
	
	
	function getConfValue($area, $field, $defValue) 
	{
		$ret = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], $field, $area); 
		
		if (!$ret) 
		{ 
			$ret = $defValue; 
		}
		
		return $ret;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gmfindroute/pi1/class.tx_gmfindroute_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/gmfindroute/pi1/class.tx_gmfindroute_pi1.php']);
}

# $GLOBALS['TSFE']->pSetup['includeJS.'][$this->extKey] = 'typo3conf/ext/gmfindroute/pi1/js/google_maps_api.js';

?>