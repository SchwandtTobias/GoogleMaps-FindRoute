<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_gmfindroute_locations'] = array (
	'ctrl' => $TCA['tx_gmfindroute_locations']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'sys_language_uid,l10n_parent,l10n_diffsource,hidden,locationname,targetlat,targetlng,stationname,imgmarker'
	),
	'feInterface' => $TCA['tx_gmfindroute_locations']['feInterface'],
	'columns' => array (
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l10n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_gmfindroute_locations',
				'foreign_table_where' => 'AND tx_gmfindroute_locations.pid=###CURRENT_PID### AND tx_gmfindroute_locations.sys_language_uid IN (-1,0)',
			)
		),
		'l10n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'locationname' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:gmfindroute/locallang_db.xml:tx_gmfindroute_locations.locationname',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required',
			)
		),
		'targetlat' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:gmfindroute/locallang_db.xml:tx_gmfindroute_locations.targetlat',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required',
			)
		),
		'targetlng' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:gmfindroute/locallang_db.xml:tx_gmfindroute_locations.targetlng',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required',
			)
		),
		'stationname' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:gmfindroute/locallang_db.xml:tx_gmfindroute_locations.stationname',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',
			)
		),
		'imgmarker' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:gmfindroute/locallang_db.xml:tx_gmfindroute_locations.imgmarker',		
			'config' => array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],	
				'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],	
				'uploadfolder' => 'uploads/tx_gmfindroute',
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, locationname, targetlat, targetlng, stationname, imgmarker')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);
?>