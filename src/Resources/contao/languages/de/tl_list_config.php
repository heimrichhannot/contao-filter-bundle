<?php

$lang = &$GLOBALS['TL_LANG']['tl_list_config'];

/**
 * Fields
 */
$lang['tstamp'][0] = 'Änderungsdatum';

// general
$lang['title'][0] = 'Titel';
$lang['title'][1] = 'Geben Sie hier bitte den Titel ein.';

// filter
$lang['filter'][0] = 'Filter';
$lang['filter'][1] = 'Bitte wählen Sie hier bei Bedarf einen Filter aus.';

// config
$lang['limitFields'][0]        = 'Verarbeitete Felder einschränken';
$lang['limitFields'][1]        = 'Wählen Sie diese Option, wenn nicht alle Felder des Data-Containers verarbeitet werden sollen.';
$lang['fields'][0]             = 'Felder';
$lang['fields'][1]             = 'Wählen Sie hier die zu verarbeitenden Felder aus.';
$lang['showItemCount'][0]      = 'Ergebnisanzahl anzeigen';
$lang['showItemCount'][1]      = 'Klicken Sie hier, um die Anzahl der gefundenen Instanzen anzuzeigen.';
$lang['itemCountText'][0]      = 'Individueller Ergebnisanzahl-Text';
$lang['itemCountText'][1]      = 'Wählen Sie hier eine Symfony-Message aus.';
$lang['showNoItemsText'][0]    = '"Keine Ergebnisse"-Meldung anzeigen';
$lang['showNoItemsText'][1]    = 'Klicken Sie hier, um eine Meldung anzuzeigen, wenn keine Instanzen gefunden wurden.';
$lang['noItemsText'][0]        = 'Individueller "Keine Ergebnisse"-Text';
$lang['noItemsText'][1]        = 'Wählen Sie hier eine Symfony-Message aus.';
$lang['showInitialResults'][0] = 'Initial Ergebnisse anzeigen';
$lang['showInitialResults'][1] = 'Wählen Sie diese Option, wenn initial eine Ergebnisliste angezeigt werden soll.';
$lang['isTableList'][0]        = 'Als Tabelle ausgeben';
$lang['isTableList'][1]        = 'Wählen Sie diese Option, die Liste in Form einer Tabelle ausgegeben werden soll.';
$lang['hasHeader'][0]          = 'Kopfzeile ausgeben';
$lang['hasHeader'][1]          = 'Wählen Sie diese Option, wenn die Tabelle eine Kopfzeile haben soll.';
$lang['sortingHeader'][0]      = 'Sortierende Kopfzeile';
$lang['sortingHeader'][1]      = 'Wählen Sie diese Option, wenn die Tabelle eine Kopfzeile haben soll, die Links zum Sortieren enthält.';
$lang['tableFields'][0]        = 'Tabellenfelder';
$lang['tableFields'][1]        = 'Wählen Sie die Felder aus, die in der Tabelle ausgegeben werden sollen.';

// sorting
$lang['sortingMode'][0]      = 'Sortiermodus';
$lang['sortingMode'][1]      = 'Wählen Sie hier aus, ob Sie zur Sortierung ein Feld auswählen oder über eine Freitexteingabe sortieren möchten.';
$lang['sortingField'][0]     = 'Sortierfeld';
$lang['sortingField'][1]     = 'Wählen Sie hier ein Sortierfeld aus.';
$lang['sortingDirection'][0] = 'Sortierreihenfolge';
$lang['sortingDirection'][1] = 'Wählen Sie eine Reihenfolge für die Sortierung aus.';
$lang['sortingText'][0]      = 'Sortierung';
$lang['sortingText'][1]      = 'Geben Sie hier eine Sortierung ein (Beispiel: "myField1 ASC, myField2 DESC").';

// jump to
$lang['useAlias'][0]   = 'Alias-Feld verwenden';
$lang['useAlias'][1]   = 'Wählen Sie diese Option, wenn erzeugte URLs statt der ID der ausgegebenen Instanz deren Alias enthalten sollen.';
$lang['aliasField'][0] = 'Alias-Feld';
$lang['aliasField'][1] = 'Wählen Sie hier das zu verwendende Alias-Feld aus (Hinweis: Nur Felder mit inputType="text" sind erlaubt).';

$lang['addDetails'][0]    = 'Details-Weiterleitung hinzufügen';
$lang['addDetails'][1]    = 'Klicken Sie hier, um jedem Eintrag der Liste eine Weiterleitung zum Anzeigen von Details hinzuzufügen.';
$lang['jumpToDetails'][0] = 'Weiterleitungsseite (Details)';
$lang['jumpToDetails'][1] = 'Wählen Sie hier die Seite aus, zu der weitergeleitet wird, wenn es eine Detailseite gibt.';
$lang['addShare'][0]      = 'Teilen-Weiterleitung hinzufügen';
$lang['addShare'][1]      = 'Klicken Sie hier, um jedem Eintrag der Liste eine Weiterleitung zum Teilen des aktuellen Listeneintrags hinzuzufügen.';
$lang['jumpToShare'][0]   = 'Weiterleitungsseite (Teilen)';
$lang['jumpToShare'][1]   = 'Wählen Sie hier die Seite aus, zu der weitergeleitet wird, wenn ein Inhalt geteilt wurde.';
$lang['shareAutoItem'][0] = 'Auto-Item für den Teilen-Link verwenden';
$lang['shareAutoItem'][1] = 'Wählen Sie diese Option aus, um das Share Token als auto_item auszugeben.';

// misc
$lang['addAjaxPagination'][0]           = 'Ajax-Paginierung hinzufügen';
$lang['addAjaxPagination'][1]           =
    'Wählen Sie diese Option, wenn eine Ajax-Paginierung genutzt werden soll. Dafür muss ein Wert > 0 in "Elemente pro Seite" gesetzt sein. Die Seitenzahlen werden durch einen einzelnen "Weiter"-Button ersetzt.';
$lang['addInfiniteScroll'][0]           = 'Infinite Scroll hinzufügen';
$lang['addInfiniteScroll'][1]           = 'Wählen Sie diese Option, um die Ajax-Paginierung im UI-Muster "Infinite Scroll" umzusetzen.';
$lang['addMasonry'][0]                  = 'Masonry hinzufügen';
$lang['addMasonry'][1]                  = 'Wählen Sie diese Option, wenn das Masonry-JavaScript-Plugin auf die Liste angewendet werden soll.';
$lang['masonryStampContentElements'][0] = 'Fixierte Blöcke festlegen';
$lang['masonryStampContentElements'][1] =
    'Hier können Sie Blöcke festlegen, die immer gerendert werden sollen. Die Position muss anschließend per CSS festgelegt werden (-> Responsive).';
$lang['stampBlock'][0]                  = 'Block';
$lang['stampBlock'][1]                  = 'Wählen Sie hier einen Block aus.';

// template
$lang['itemTemplate'][0] = 'Listen-Template';
$lang['itemTemplate'][1] = 'Wählen Sie hier das Template aus, mit dem Liste gerendert werden sollen.';
$lang['itemTemplate'][0] = 'Instanz-Template';
$lang['itemTemplate'][1] = 'Wählen Sie hier das Template aus, mit dem die einzelnen Instanzen gerendert werden sollen.';

/**
 * Legends
 */
$lang['general_legend']  = 'Allgemeine Einstellungen';
$lang['entity_legend']   = 'Entität';
$lang['config_legend']   = 'Konfiguration';
$lang['filter_legend']   = 'Filter';
$lang['sorting_legend']  = 'Sortierung';
$lang['jumpto_legend']   = 'Weiterleitung';
$lang['misc_legend']     = 'Verschiedenes';
$lang['template_legend'] = 'Template';

/**
 * Reference
 */
$lang['reference'] = [
    \HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_MODE_FIELD     => 'Feld',
    \HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_MODE_TEXT      => 'Freitext',
    \HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_MODE_RANDOM    => 'Zufällig',
    \HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_DIRECTION_ASC  => 'Aufsteigend',
    \HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_DIRECTION_DESC => 'Absteigend',
];

/**
 * Buttons
 */
$lang['new']        = ['Neue Listenkonfiguration', 'Listenkonfiguration erstellen'];
$lang['edit']       = ['Listenkonfiguration bearbeiten', 'Listenkonfiguration ID %s bearbeiten'];
$lang['editheader'] = ['Listenkonfiguration-Einstellungen bearbeiten', 'Listenkonfiguration-Einstellungen ID %s bearbeiten'];
$lang['copy']       = ['Listenkonfiguration duplizieren', 'Listenkonfiguration ID %s duplizieren'];
$lang['delete']     = ['Listenkonfiguration löschen', 'Listenkonfiguration ID %s löschen'];
$lang['toggle']     = ['Listenkonfiguration veröffentlichen', 'Listenkonfiguration ID %s veröffentlichen/verstecken'];
$lang['show']       = ['Listenkonfiguration Details', 'Listenkonfiguration-Details ID %s anzeigen'];