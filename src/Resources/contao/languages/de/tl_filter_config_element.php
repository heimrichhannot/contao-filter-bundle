<?php

$lang = &$GLOBALS['TL_LANG']['tl_filter_config_element'];

/**
 * Fields
 */
$lang['tstamp']            = ['Änderungsdatum', ''];
$lang['title']             = ['Titel', 'Geben Sie hier bitte den Titel ein.'];
$lang['type']              = ['Typ', 'Wählen Sie hier bitte einen Typ aus.'];
$lang['field']             = ['DCA-Feld', 'Wählen Sie hier das verknüpfte DCA-Feld aus.'];
$lang['fields']            = ['Felder', 'Wählen Sie hier mehrere Felder aus, die in der gewählten Reihenfolge verknüpft werden sollen.'];
$lang['customOptions']     = ['Optionen anpassen', 'Wählen Sie diese Option, um benutzerdefinierte Optionswerte festzulegen.'];
$lang['options']           = ['Optionen', 'Wenn JavaScript deaktiviert ist, speichern Sie Ihre Änderungen, bevor Sie die Reihenfolge verändern.'];
$lang['customName']        = ['Name anpassen', 'Setzen Sie hier einen benutzerdefinierten Namen.'];
$lang['name']              = ['Name', 'Geben Sie hier den gewünschten Namen ein.'];
$lang['addPlaceholder']    = ['Platzhalter hinzufügen', 'Wählen Sie diese Option, um dem Filter einen Platzhaltertext hinzuzufügen.'];
$lang['placeholder']       = ['Platzhalter', 'Wählen Sie hier einen Platzhalter aus.'];
$lang['hideLabel']         = ['Label verstecken', 'Wählen Sie diese Option, um das Label des Filters zu verstecken.'];
$lang['customLabel']       = ['Label anpassen', 'Wählen ie diese Option, um einen benutzerdefinierten Label-Text festzulegen.'];
$lang['label']             = ['Label', 'Wählen Sie hier ein Label aus.'];
$lang['expanded']          = ['Expanded (Radio/Checkboxes)', 'Wählen Sie diese Option, um Optionen als "radio"- oder "checkbox"-Elemente auszugeben.'];
$lang['multiple']          = ['Multiple', 'Wählen Sie diese Option, wenn der Nutzer mehrere Optionen auswählen können soll.'];
$lang['currency']          = ['Währung', 'Wählen Sie die Währung aus, in der der eingegebene Wert vorliegen soll.'];
$lang['divisor']           = ['Divisor', 'Geben Sie hier einen ganzzahligen Divisor ein, wenn aus Gründen der Anwendungslogik, der auszugebende Wert vorab durch einen Divisor geteilt werden soll.'];
$lang['grouping']          = ['Zahlengruppierung', 'Aktivieren Sie diese Option, wenn auf der aktuellen Region ("locale") basierend Zahlen durch Interpunktion getrennt werden sollen (z.B. 12345.123 -> 12,345.123)'];
$lang['scale']             = ['Erlaubte Dezimalstellen', 'Geben Sie hier ein, wie viele Dezimalstellen nach dem Runden bestehen bleiben sollen.'];
$lang['roundingMode']      = ['Rundungsmodus (Standard: Abrunden)', 'Wählen Sie hier aus, ob auf- oder abgerundet werden soll.'];
$lang['alwaysEmpty']       = ['Immer leer ausgeben', 'Aktivieren Sie diese Option, wenn das Feld immer mit leerem Wert ausgegeben werden soll (auch dann, wenn durch ein Abschicken des Filters ein Wert vorhanden ist).'];
$lang['percentType']       = ['Prozent-Typ', 'Spezifizieren Sie hier, wie der Wert des Felds gespeichert werden soll (0.55 vs. 55).'];
$lang['defaultProtocol']   = ['Standardprotokoll', 'Wenn ein Wert abgeschickt wird, der nicht mit einem Protokoll beginnt (bspw. http://, ftp://, ...), wird das hier festgelegte Protokoll dem String vorangestellt.'];
$lang['customCountries']   = ['Länder anpassen', 'Wählen Sie diese Option, um die Länderauswahl anzupassen.'];
$lang['countries']         = ['Länder', 'Wählen Sie hier die gewünschten Länder aus.'];
$lang['customLanguages']   = ['Sprachen anpassen', 'Wählen Sie diese Option, um die Sprachauswahl anzupassen.'];
$lang['languages']         = ['Sprachen', 'Wählen Sie hier die gewünschten Sprachen aus.'];
$lang['customLocales']     = ['Region ("locale") anpassen', 'Wählen Sie diese Option, um die Regionsauswahl anzupassen.'];
$lang['locales']           = ['Regionen', 'Wählen Sie hier die gewünschten Regionen aus.'];
$lang['customValue']       = ['Wert anpassen', 'Wählen Sie diese Option, um den Wert anzupassen.'];
$lang['value']             = ['Wert', 'Geben Sie hier den gewünschten Wert ein.'];
$lang['startElement']      = ['Startfeld', 'Wählen sie hier das Feld was als Anfangsdatum im Filter genutzt werden soll.'];
$lang['stopElement']       = ['Stopfeld', 'Wählen sie hier das Feld was als Enddatum im Filter genutzt werden soll.'];
$lang['timeFormat']        = ['Zeitformat', 'Geben Sie hier ein valides Zeitformat ein.'];
$lang['dateFormat']        = ['Datumsformat', 'Geben Sie hier ein valides Datumsformat ein.'];
$lang['dateTimeFormat']    = ['Datum-/Zeitformat', 'Geben Sie hier ein valides Datum-/Zeitformat ein.'];
$lang['minDate']           = ['Minimales Datum', 'Geben Sie hier das minimale Datum ein.'];
$lang['maxDate']           = ['Maximales Datum', 'Geben Sie hier das maximale Datum ein.'];
$lang['minTime']           = ['Minimale Zeit', 'Geben Sie hier die minimale Zeit ein.'];
$lang['maxTime']           = ['Maximale Zeit', 'Geben Sie hier die maximale Zeit ein.'];
$lang['dateWidget']        = ['Rendermodus Datumswidget', 'Wählen Sie hier aus, mit welchem Modus das Widget gerendert werden soll.'];
$lang['timeWidget']        = ['Rendermodus Zeitwidget', 'Wählen Sie hier aus, mit welchem Modus das Widget gerendert werden soll.'];
$lang['html5']             = ['Als HTML5-Feld ausgeben', 'Wählen Sie diese Option, um das Feld als HTML5-Feld auszugeben. Einige Browser fügen dann spezielle UI-Elemente ein.'];
$lang['inputGroup']        = ['Als Feldergruppe ausgeben ("input-group")', 'Wählen Sie diese Option, um dem Feld bspw. ein Icon voranzustellen.'];
$lang['inputGroupPrepend'] = ['Vorangestellter Inhalt', 'Select content that should be prepended to the input.'];
$lang['inputGroupAppend']  = ['Angefügter Inhalt', 'Select content that should be appended to the input.'];
$lang['cssClass']          = ['CSS-Klasse', 'Geben Sie hier durch Leerzeichen getrennte CSS-Klassen ein.'];
$lang['published']         = ['Veröffentlichen', 'Wählen Sie diese Option zum Veröffentlichen.'];
$lang['start']             = ['Anzeigen ab', 'Filterelement erst ab diesem Tag auf der Webseite anzeigen.'];
$lang['stop']              = ['Anzeigen bis', 'Filterelement nur bis zu diesem Tag auf der Webseite anzeigen.'];

/**
 * Legends
 */
$lang['general_legend']       = 'Allgemeine Einstellungen';
$lang['config_legend']        = 'Konfiguration';
$lang['visualization_legend'] = 'Darstellung';
$lang['expert_legend']        = 'Experten-Einstellungen';
$lang['publish_legend']       = 'Veröffentlichung';

/**
 * Buttons
 */
$lang['new']    = ['Neues Filterelement', 'Filterelement erstellen'];
$lang['edit']   = ['Filterelement bearbeiten', 'Filterelement ID %s bearbeiten'];
$lang['copy']   = ['Filterelement duplizieren', 'Filterelement ID %s duplizieren'];
$lang['delete'] = ['Filterelement löschen', 'Filterelement ID %s löschen'];
$lang['toggle'] = ['Filterelement veröffentlichen', 'Filterelement ID %s veröffentlichen/verstecken'];
$lang['show']   = ['Filterelement Details', 'Filterelement-Details ID %s anzeigen'];

/**
 * References
 */
$lang['reference'] = [
    'type'         => [
        'text'        => 'Text',
        'text_concat' => 'Konkatenierter Text',
        'textarea'    => 'Textarea',
        'email'       => 'E-Mail',
        'integer'     => 'Integer',
        'money'       => 'Geld',
        'number'      => 'Zahl',
        'password'    => 'Passwort',
        'percent'     => 'Prozent',
        'search'      => 'Suche',
        'url'         => 'URL',
        'range'       => 'Spanne (range)',
        'tel'         => 'Telefon',
        'color'       => 'Farbe',
        'choice'      => 'Choice',
        'country'     => 'Land',
        'language'    => 'Sprache',
        'locale'      => 'Region ("locale")',
        'button'      => 'Button',
        'reset'       => 'Reset',
        'submit'      => 'Submit',
        'hidden'      => 'Hidden',
        'checkbox'    => 'Checkbox',
        'radio'       => 'Radio',
        'other'       => 'Sonstiges',
        'initial'     => 'Initial',
        'date_time'   => 'Datum & Zeit',
        'date'        => 'Datum',
        'time'        => 'Zeit',
        'date_range'  => 'Datumsspanne (date range)',
    ],
    'roundingMode' => [
        \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_DOWN      => 'Abrunden (zu 0 hin)',
        \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_FLOOR     => 'Floor (zu –∞ hin)',
        \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_UP        => 'Aufrunden (von 0 weg)',
        \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_CEILING   => 'Ceiling (zu +∞ hin)',
        \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_HALF_DOWN => 'Half down (zum nächsten Nachbarn hin; bei Äquidistanz abrunden)',
        \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_HALF_EVEN => 'Half even (zum nächsten Nachbarn hin; bei Äquidistanz zum nächsten geraden Nachbarn runden)',
        \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_HALF_UP   => 'Half up (zum nächsten Nachbarn hin; bei Äquidistanz aufrunden)',
    ],
    'percentType'  => [
        'fractional' => 'Bruch (z. B. 0.55)',
        'integer'    => 'Ganzzahl (z. B. 55)'
    ]
];