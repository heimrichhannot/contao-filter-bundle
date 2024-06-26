<?php

$lang = &$GLOBALS['TL_LANG']['tl_filter_config'];

/**
 * Fields
 */
$lang['tstamp']             = ['Änderungsdatum', ''];
$lang['title']              = ['Titel', 'Geben Sie hier bitte den Titel ein.'];
$lang['dataContainer']      = ['Data-Container', 'Wählen Sie hier den gewünschten Data-Container aus.'];
$lang['method']             = ['HTTP-Methode', 'Wählen Sie die HTTP-Methode des Formulars aus (GET oder POST).'];
$lang['filterFormAction']   = ['Action', 'Geben Sie eine URL an, zu der die Formulardaten gesendet werden sollen.'];
$lang['published']          = ['Veröffentlichen', 'Wählen Sie diese Option zum Veröffentlichen.'];
$lang['start']              = ['Anzeigen ab', 'Filter erst ab diesem Tag auf der Webseite anzeigen.'];
$lang['stop']               = ['Anzeigen bis', 'Filter nur bis zu diesem Tag auf der Webseite anzeigen.'];
$lang['dateTimeFormat']     = ['Zeitformat', 'Geben Sie hier die zu verwendende Formatierung aus.'];
$lang['mergeData']          = ['Formulardaten mergen', 'Wählen Sie diese Option um die abgesendeten Formulardaten mit anderen Formulardaten zu mergen.'];
$lang['type']               = ['Typ', 'Wählen Sie den Typen aus.'];
$lang['parentFilter']       = ['Eltern-Filterkonfiguration', 'Wählen Sie hier eine Filterkonfiguration aus, von der geerbt werden soll.'];
$lang['asyncFormSubmit']    = ['Filter asynchron absenden', 'Wählen Sie diese Option, wenn der Filter asynchron abgeschickt werden soll.'];
$lang['resetFilterInitial'] = ['Filter bei Seitenwechsel zurücksetzen', 'Wählen Sie diese Option, wenn der Filter zurückgesetzt werden soll, wenn Referrer und die gefilterte Seite verschieden sind.'];


/**
 * Legends
 */
$lang['general_legend'] = 'Allgemeine Einstellungen';
$lang['config_legend']  = 'Konfiguration';
$lang['publish_legend'] = 'Veröffentlichung';

/**
 * Buttons
 */
$lang['new']    = ['Neuer Filter', 'Filter erstellen'];
$lang['edit']   = ['Filter bearbeiten', 'Filter ID %s bearbeiten'];
$lang['copy']   = ['Filter duplizieren', 'Filter ID %s duplizieren'];
$lang['delete'] = ['Filter löschen', 'Filter ID %s löschen'];
$lang['toggle'] = ['Filter veröffentlichen', 'Filter ID %s veröffentlichen/verstecken'];
$lang['show']   = ['Filter Details', 'Filter-Details ID %s anzeigen'];