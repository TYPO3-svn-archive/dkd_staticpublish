<?php
/*
 *  CVS Versioning: $Id$
 */

/**
 * Language labels for module "web_txdkdxmlimportM1"
 *
 * This file overwrites labels from EXT:dkd_xmlimport/mod1/locallang.php.
 */

$LOCAL_LANG = Array (
	'default' => Array (
		'title' => 'XML-Publish',
		'function1' => 'Import XML Data',
		'function2' => 'Export Files',
		'function3' => 'Publications Overview',
		'importXML_conf_header' => 'The import of the HTML pages to be exported was done using the following configuration.',
		'importXML_conf_hint' => '<em>Configuration:</em>',
		'importXML_backLink' => 'Reset configuration',
		'importXML_buttonSelect' => 'Select',
		'importXML_buttonImport' => 'Import',
		'importXML_currentConfig' => 'Current Settings for import',
		'importXML_pidMsg' => 'Export from page',
		'importXML_msg_noUrl' => 'No URL defined for import.',
		'importPics_header' => 'Export: File(DB) -> File',
		'importPics_conf_header' => 'Select the setting you want to use for the file export',
		'importPics_conf_hint' => '<em>Hint:</em>As the export of the HTML files may exceed the max. runtime of this script, the HTML files are exported step-by-step',
		'importPics_noPics' => 'There is no file-export defined for the record sets in this table!',
		'importPics_currentSettings' => 'Current settings for the export',
		'importPics_url' => '<p>This is the url where the files are stored for export:<br><em>%s</em></p>',
		'importPics_placeholder_fieldVal' => '&lt;DB-fieldcontent&gt;',
		'importPics_pids_head' => 'Export HTML files related to records on which page?',
		'importPics_pids_all' => 'all',
		'importPics_pic_count' => '%u HTML files have been exported in this step.',
		'importPics_step' => 'Begin / continue export at record ',
		'importPics_completed_msg' => 'Export of %2$u HTML files from %1$u records completed',
		'pubID_dateFormat' => '%m/%d/%y %H:%M:%S',
	),

	'de' => Array (
		'title' => 'XML-Publish',
		'function1' => 'Import XML Daten',
		'function2' => 'Export Dateien',
		'function3' => 'Veröffentlichungen Übersicht',
		'importXML_conf_header' => 'Der Import der zu exportierenden HTML Seiten wurde mit folgender Konfiguration durchgeführt.',
		'importXML_conf_hint' => '<em>Konfiguration:</em>',
		'importXML_backLink' => 'Auswahl zurücksetzen',
		'importXML_buttonSelect' => 'Auswählen',
		'importXML_buttonImport' => 'Importieren',
		'importXML_currentConfig' => 'Aktuelle Einstellungen für den Import',
		'importXML_pidMsg' => 'Exportieren ab Seite',
		'importXML_msg_noUrl' => 'Es ist keine URL für den Import angegeben.',
		'importPics_header' => 'Export: Datei(DB) -> HTML Datei',
		'importPics_conf_header' => 'Wählen Sie die Konfiguration aus, anhand derer die HTML Dateien exportiert werden sollen',
		'importPics_conf_hint' => '<em>Hinweis:</em>Da der Export der HTML Dateien einen längeren Zeitraum in Anspruch nehmen kann, als die Laufzeit des Skripts erlaubt, werden die Dateien Schritt-für-Schritt exportiert',
		'importPics_noPics' => 'Für die Datensätze in dieser Tabelle sind keine Datei-Exporte definiert!',
		'importPics_currentSettings' => 'Aktuelle Einstellungen für den Export',
		'importPics_url' => '<p>Die Dateien für den Export liegen unter der URL:<br><em>%s</em></p>',
		'importPics_placeholder_fieldVal' => '&lt;DB-Feldinhalt&gt;',
		'importPics_pids_head' => 'HTML Dateien zu Datensätzen auf welcher Seite exportieren?',
		'importPics_pids_all' => 'alle',
		'importPics_pic_count' => 'In diesem Schritt wurden %u HTML Dateien exportiert.',
		'importPics_step' => 'Export beginnen / fortführen bei Datensatz ',
		'importPics_completed_msg' => 'Der Export von %2$u HTML Dateien basierend auf %1$u Datensätzen ist abgeschlossen',
		'pubID_dateFormat' => '%d.%m.%Y %H:%M:%S',
	),
);
?>