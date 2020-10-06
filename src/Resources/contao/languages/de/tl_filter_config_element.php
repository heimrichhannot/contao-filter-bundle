<?php

$lang = &$GLOBALS['TL_LANG']['tl_filter_config_element'];

/**
 * Fields
 */
$lang['tstamp']                       = ['Änderungsdatum', ''];
$lang['title']                        = ['Titel', 'Geben Sie hier bitte den Titel ein.'];
$lang['type']                         = ['Typ', 'Wählen Sie hier bitte einen Typ aus.'];
$lang['field']                        = ['DCA-Feld', 'Wählen Sie hier das verknüpfte DCA-Feld aus.'];
$lang['fields']                       = ['Felder', 'Wählen Sie hier mehrere Felder aus, die in der gewählten Reihenfolge verknüpft werden sollen.'];
$lang['parentField']                  = ['Eltern-DCA-Feld', 'Wählen Sie hier das verknüpfte Eltern-DCA-Feld aus.'];
$lang['customOptions']                = ['Optionen anpassen', 'Wählen Sie diese Option, um benutzerdefinierte Optionswerte festzulegen.'];
$lang['reviseOptions']                = ['Vorhandene Optionen bereinigen', 'Wählen Sie diese Option, um *vorhandene* Optionswerte (bspw. options_callback) zu bereinigen, d.h., Optionen ohne Vorkommen in der Ergebnismenge zu entfernen.'];
$lang['adjustOptionLabels']           = ['Optionen-Labels anpassen', 'Wählen Sie diese Option, wenn Sie die Labels der Optionswerte anpassen möchten.'];
$lang['optionLabelPattern']           = ['Optionen-Label', 'Wählen Sie ein Optionen-Label aus, auf dessen Vorlage die Optionslabels angepasst werden sollen.'];
$lang['sortOptionValues']             = ['Optionen sortieren', 'Wählen Sie diese Option, um die Optionswerte alphabetisch zu sortieren.'];
$lang['sortOptionValuesInverted']     = ['Optionen umgekehrt sortieren', 'Wählen Sie diese Option, um die Optionswerte umgekehrt alphabetisch zu sortieren.'];
$lang['options']                      = ['Optionen', 'Wenn JavaScript deaktiviert ist, speichern Sie Ihre Änderungen, bevor Sie die Reihenfolge verändern.'];
$lang['customName']                   = ['Name anpassen', 'Setzen Sie hier einen benutzerdefinierten Namen.'];
$lang['name']                         = ['Formularfeldname', 'Geben Sie hier den gewünschten Namen im HTML-Formular ein.'];
$lang['customOperator']               = ['Operator anpassen', 'Setzen Sie hier einen benutzerdefinierten Operator.'];
$lang['operator']                     = ['Operator', 'Wählen Sie hier den gewünschten Operator aus.'];
$lang['addPlaceholder']               = ['Platzhalter hinzufügen', 'Wählen Sie diese Option, um dem Filter einen Platzhaltertext hinzuzufügen.'];
$lang['placeholder']                  = ['Platzhalter', 'Wählen Sie hier einen Platzhalter aus.'];
$lang['hideLabel']                    = ['Label verstecken', 'Wählen Sie diese Option, um das Label des Filters zu verstecken.'];
$lang['customLabel']                  = ['Label anpassen', 'Wählen ie diese Option, um einen benutzerdefinierten Label-Text festzulegen.'];
$lang['label']                        = ['Label', 'Wählen Sie hier ein Label aus.'];
$lang['expanded']                     = ['Expanded (Radio/Checkboxes)', 'Wählen Sie diese Option, um Optionen als "radio"- oder "checkbox"-Elemente auszugeben.'];
$lang['multiple']                     = ['Multiple', 'Wählen Sie diese Option, wenn der Nutzer mehrere Optionen auswählen können soll.'];
$lang['currency']                     = ['Währung', 'Wählen Sie die Währung aus, in der der eingegebene Wert vorliegen soll.'];
$lang['divisor']                      = ['Divisor', 'Geben Sie hier einen ganzzahligen Divisor ein, wenn aus Gründen der Anwendungslogik, der auszugebende Wert vorab durch einen Divisor geteilt werden soll.'];
$lang['grouping']                     = ['Zahlengruppierung', 'Aktivieren Sie diese Option, wenn auf der aktuellen Region ("locale") basierend Zahlen durch Interpunktion getrennt werden sollen (z.B. 12345.123 -> 12,345.123)'];
$lang['scale']                        = ['Erlaubte Dezimalstellen', 'Geben Sie hier ein, wie viele Dezimalstellen nach dem Runden bestehen bleiben sollen.'];
$lang['roundingMode']                 = ['Rundungsmodus (Standard: Abrunden)', 'Wählen Sie hier aus, ob auf- oder abgerundet werden soll.'];
$lang['alwaysEmpty']                  = ['Immer leer ausgeben', 'Aktivieren Sie diese Option, wenn das Feld immer mit leerem Wert ausgegeben werden soll (auch dann, wenn durch ein Abschicken des Filters ein Wert vorhanden ist).'];
$lang['percentType']                  = ['Prozent-Typ', 'Spezifizieren Sie hier, wie der Wert des Felds gespeichert werden soll (0.55 vs. 55).'];
$lang['defaultProtocol']              = ['Standardprotokoll', 'Wenn ein Wert abgeschickt wird, der nicht mit einem Protokoll beginnt (bspw. http://, ftp://, ...), wird das hier festgelegte Protokoll dem String vorangestellt.'];
$lang['customCountries']              = ['Länder anpassen', 'Wählen Sie diese Option, um die Länderauswahl anzupassen.'];
$lang['countries']                    = ['Länder', 'Wählen Sie hier die gewünschten Länder aus.'];
$lang['customLanguages']              = ['Sprachen anpassen', 'Wählen Sie diese Option, um die Sprachauswahl anzupassen.'];
$lang['languages']                    = ['Sprachen', 'Wählen Sie hier die gewünschten Sprachen aus.'];
$lang['customLocales']                = ['Region ("locale") anpassen', 'Wählen Sie diese Option, um die Regionsauswahl anzupassen.'];
$lang['locales']                      = ['Regionen', 'Wählen Sie hier die gewünschten Regionen aus.'];
$lang['customValue']                  = ['Wert anpassen', 'Wählen Sie diese Option, um den Wert anzupassen.'];
$lang['value']                        = ['Wert', 'Geben Sie hier den gewünschten Wert ein.'];
$lang['isInitial']                    = ['Initiales Filterelement', 'Wählen Sie diese Option, um das Filterelement als "initial" zu kennzeichnen. Dadurch wird es im Frontend nicht ausgegeben, aber trotzdem angewendet. Normale Filterelemente überschreiben initiale Filterelemente.'];
$lang['initialValueType']             = ['Typ des initialen Werts', 'Wählen Sie hier den Typ des initialen Werts aus.'];
$lang['initialValue']                 = ['Initialer Wert', 'Legen Sie hier den initialen Wert fest.'];
$lang['initialValue_value']           = ['Wert', ''];
$lang['addDefaultValue']              = ['Standardwert hinzufügen', 'HINWEIS: Dieser Wert wird NICHT initial ausgewertet, sondern gibt nur einen Standardwert für das Filterfeld vor. Wenn Sie einen initialen *Filter* festlegen wollen, markieren Sie das Filterelement als "initial".'];
$lang['defaultValueType']             = ['Typ des Standardwerts', 'Wählen Sie hier den Typ des Standardwerts aus.'];
$lang['defaultValue']                 = ['Standardwert', 'Legen Sie hier den Standardwerts fest.'];
$lang['defaultValue_value']           = ['Wert', ''];
$lang['startElement']                 = ['Start-Filterelement', 'Wählen sie hier das Filterelement, das als Anfangsdatum im Filter genutzt werden soll.'];
$lang['stopElement']                  = ['Stop-Filterelement', 'Wählen sie hier das Filterelement, das als Enddatum im Filter genutzt werden soll.'];
$lang['timeFormat']                   = ['Zeitformat', 'Geben Sie hier ein valides Zeitformat ein.'];
$lang['dateFormat']                   = ['Datumsformat', 'Geben Sie hier ein valides Datumsformat ein.'];
$lang['dateTimeFormat']               = ['Datum-/Zeitformat', 'Geben Sie hier ein valides Datum-/Zeitformat ein.'];
$lang['minDate']                      = ['Minimales Datum', 'Geben Sie hier das minimale Datum ein.'];
$lang['maxDate']                      = ['Maximales Datum', 'Geben Sie hier das maximale Datum ein.'];
$lang['minTime']                      = ['Minimale Zeit', 'Geben Sie hier die minimale Zeit ein.'];
$lang['maxTime']                      = ['Maximale Zeit', 'Geben Sie hier die maximale Zeit ein.'];
$lang['min']                          = ['Minimaler Wert', 'Geben Sie hier den minimalen Wert ein.'];
$lang['max']                          = ['Maximaler Wert', 'Geben Sie hier den maximalen Wert ein.'];
$lang['dateWidget']                   = ['Rendermodus Datumswidget', 'Wählen Sie hier aus, mit welchem Modus das Widget gerendert werden soll.'];
$lang['timeWidget']                   = ['Rendermodus Zeitwidget', 'Wählen Sie hier aus, mit welchem Modus das Widget gerendert werden soll.'];
$lang['html5']                        = ['Als HTML5-Feld ausgeben', 'Wählen Sie diese Option, um das Feld als HTML5-Feld auszugeben. Einige Browser fügen dann spezielle UI-Elemente ein.'];
$lang['inputGroup']                   = ['Als Feldergruppe ausgeben ("input-group")', 'Wählen Sie diese Option, um dem Feld bspw. ein Icon voranzustellen.'];
$lang['inputGroupPrepend']            = ['Vorangestellter Inhalt', 'Wählen Sie hier Inhalt aus, der dem Feld vorangestellt werden soll.'];
$lang['inputGroupAppend']             = ['Angefügter Inhalt', 'Wählen Sie hier Inhalt aus, der dem Feld angefügt werden soll.'];
$lang['invertField']                  = ['Feldwert invertieren', 'Wählen Sie diese Option, wenn ein "true" im Veröffentlicht-Feld einem nichtöffentlichen Zustand entspricht.'];
$lang['ignoreFePreview']              = ['Frontendvorschau ignorieren', 'Wählen Sie diese Option, wenn die Frontendvorschau für das Filterlement ignoriert werden soll.'];
$lang['addStartAndStop']              = ['Start- und Stopfeld hinzufügen', 'Wählen Sie diese Option, wenn Sie das Filterelement Start- und Stopfeld beachten wollen.'];
$lang['startField']                   = ['Startfeld', 'Wählen Sie hier ein Feld aus.'];
$lang['stopField']                    = ['Stopfeld', 'Wählen Sie hier ein Feld aus.'];
$lang['cssClass']                     = ['CSS-Klasse', 'Geben Sie hier durch Leerzeichen getrennte CSS-Klassen ein.'];
$lang['published']                    = ['Veröffentlichen', 'Wählen Sie diese Option zum Veröffentlichen.'];
$lang['start']                        = ['Anzeigen ab', 'Filterelement erst ab diesem Tag auf der Webseite anzeigen.'];
$lang['stop']                         = ['Anzeigen bis', 'Filterelement nur bis zu diesem Tag auf der Webseite anzeigen.'];
$lang['whereSql']                     = ['Zusätzliches WHERE-SQL', 'Geben Sie hier SQL ein, welches dem WHERE-Statement hinzugefügt wird.'];
$lang['submitOnChange']               = ['Formular bei Änderung absenden (submitOnChange)', 'Wählen Sie diese Option, wenn der Nutzer das Formular bei Änderung abschicken soll.'];
$lang['alwaysShow']                   = ['Immer anzeigen', 'Das Filterelement immer anzeigen.'];
$lang['dynamicOptions']               = ['Optionswerte aus bestehenden Entitäten berechnen', 'Aktivieren Sie diese Option, um die Optionswerte anhand der vorhandenen Daten zu erhalten. Initiale Filterelemente mit Skalaren oder Array-Werten werden beachtet.'];
$lang['alternativeValueSource']       = ['Filterwert aus alternativer Quelle beziehen', 'Dieses Feld wird durch andere Bundles konfiguriert (bspw. heimrichhannot/contao-reader-bundle).'];
$lang['addGroupChoiceField']          = ['Gruppen-Choice hinzufügen', 'Wählen Sie diese Option, wenn den Choices ein Feld hinzugefügt werden soll, welches mehrere Choices gruppiert. Im Standardfall fungiert dies als Select-All-Field.'];
$lang['modifyGroupChoices']           = ['Gruppen-Choice-Optionen modifizieren', 'Wählen Sie diese Option, wenn Sie die Choices, die in diesem Feld gruppiert werden sollen einschränken wollen.'];
$lang['groupChoices']                 = ['Optionen', ''];
$lang['addOptionCount']               = ['Anzahl Einträge für Optionen anzeigen', 'Die Anzahl der Elemente für jede Option mit ausgeben. Nur in Verbindung mit dynamischen Optionen.'];
$lang['optionCountLabel']             = ['Ausgabe-Format für Optionen mit Element-Anzahl', 'Wählen sie hier, in welchem Format die Optionen ausgegeben werden sollen.'];
$lang['sourceTable']                  = ['Quell-Tabelle', 'Wählen Sie die Tabelle, in der die Quell-Entität gehalten wird.'];
$lang['sourceField']                  = ['Quell-Feld', 'Wählen Sie das Feld, dass die Filter-Werte beinhaltet.'];
$lang['sourceEntityResolve']          = ['Bedingung zum Bestimmen der Quell-Entität', 'Erstellen Sie hier die Bedingung mit deren Hilfe die Quellentität gefunden wird.'];
$lang['allowHtmlGeoLocation']         = ['HTML-Geo-Location erlauben', 'Wählen Sie diese Option, wenn der Nutzer auch seinen eigenen Standpunkt freigeben darf.'];
$lang['useCurrentLocationElement']    = ['"Aktueller Standort"-Checkbox-Filterelement', 'Wählen Sie hier das Filterelement für die Checkbox aus, die den aktuellen Standort abfragt.'];
$lang['currentLocationHiddenElement'] = ['"Aktueller Standort"-Hidden-Filterelement', 'Wählen Sie hier das Filterelement aus, in dem der aktuelle Standort gespeichert wird.'];
$lang['radiusElement']                = ['Radius-Filterelement', 'Wählen Sie hier das Filterelement aus, in dem der Radius ausgewählt werden kann.'];
$lang['cityElement']                  = ['Ort-Filterelement', 'Wählen Sie hier das Filterelement für den Ort aus.'];
$lang['postalElement']                = ['Postleitzahl-Filterelement', 'Wählen Sie hier das Filterelement für die Postleitzahl aus.'];
$lang['stateElement']                 = ['Bundesland-Filterelement', 'Wählen Sie hier das Filterelement für das Bundesland aus.'];
$lang['countryFallback']              = ['Land-Fallback', 'Wählen Sie hier das Land aus, das für den Bezug von Koordinaten aus Postleitzahlen und Städten genutzt werden soll um Mehrdeutigkeiten zu vermeiden. Sie können unter "Land-Feld" ein eigenes Feld auswählen, das dann im Filter vorhanden ist, damit der Nutzer dieses Feld überschreiben kann.'];
$lang['countryElement']               = ['Land-Filterelement', 'Wählen Sie hier das Filterelement für das Land aus.'];
$lang['coordinatesMode']              = ['Koordinatentyp', 'Wählen Sie hier aus, ob die Koordinaten in 1 oder 2 Feldern gespeichert sind.'];
$lang['coordinatesField']             = ['Zusammengesetztes Koordinaten-DCA-Feld', 'Wählen Sie hier das DCA-Feld aus, in dem Koordinaten in der Form &lt;lat&gt;,&lt;long&gt; gespeichert werden.'];
$lang['latField']                     = ['Latitude-DCA-Feld', 'Wählen Sie hier das DCA-Feld aus, in dem die Latitude gespeichert wird.'];
$lang['longField']                    = ['Longitude-DCA-Feld', 'Wählen Sie hier das DCA-Feld aus, in dem die Longitude gespeichert wird.'];
$lang['proximitySearchExplanation']   = '<strong>Notwendig</strong> für eine Umkreissuche ist immer ein Radius-Filterelement (Typ Radius-Choice). Außerdem muss es in der Entität, die durchsucht werden soll, eines oder mehrere DCA-Felder geben, in denen die Koordinaten gespeichert sind.<br><br><strong>Optional</strong> können Sie verschiedene Felder wie Ort, PLZ, Land, ... festlegen, die für die Berechnung der Suchkoordinaten herangezogen werden. Die Berechnung erfolgt on-demand beim Absenden des Filters und setzt einen gesetzten Google-API-Key in den Contao-Systemeinstellungen voraus (Geocode-API!).';

// sort
$lang['sortOptions']           = ['Sortier-Optionen', 'Fügen Sie hier die gewünschten Sortieroptionen hinzu.'];
$lang['sortOptions_class']     = ['Sortier-Klasse', 'Wählen Sie hier die Sortierklasse aus.'];
$lang['sortOptions_field']     = ['DCA-Feld', 'Wählen Sie hier das zu sortierende Feld aus.'];
$lang['sortOptions_direction'] = ['Sortierreihenfolde', 'Wählen Sie eine Reihenfolge für die Sortierung aus.'];
$lang['sortOptions_fieldText'] = ['Sortiertext', 'Wählen Sie hier den Text des Sortierfeldes aus.'];
$lang['sortOptions_standard']  = ['Standard', 'HINWEIS: Dieser Wert wird NICHT initial ausgewertet, sondern gibt nur einen Standardwert für das Sortierfeld vor. Der initiale Sortierfilter wird in der Listenkonfiguration festgelegt.'];

/**
 * Legends
 */
$lang['general_legend']       = 'Allgemeine Einstellungen';
$lang['config_legend']        = 'Konfiguration';
$lang['visualization_legend'] = 'Darstellung';
$lang['expert_legend']        = 'Experten-Einstellungen';
$lang['publish_legend']       = 'Veröffentlichung';
$lang['source_legend']        = 'externe Entität-Einstellungen';

/**
 * Buttons
 */
$lang['new']    = ['Neues Filterelement', 'Filterelement erstellen'];
$lang['edit']   = ['Filterelement bearbeiten', 'Filterelement ID %s bearbeiten'];
$lang['copy']   = ['Filterelement duplizieren', 'Filterelement ID %s duplizieren'];
$lang['cut']    = ['Filterelement verschieben', 'Filterelement ID %s verschieben'];
$lang['delete'] = ['Filterelement löschen', 'Filterelement ID %s löschen'];
$lang['toggle'] = ['Filterelement veröffentlichen', 'Filterelement ID %s veröffentlichen/verstecken'];
$lang['show']   = ['Filterelement Details', 'Filterelement-Details ID %s anzeigen'];

/**
 * References
 */
$lang['reference'] = [
    'type'                                                                                   => [
        'text'                                                                    => 'Text',
        'text_concat'                                                             => 'Konkatenierter Text',
        'textarea'                                                                => 'Textarea',
        \HeimrichHannot\FilterBundle\Filter\Type\EmailType::TYPE                  => 'E-Mail',
        'integer'                                                                 => 'Integer',
        'money'                                                                   => 'Geld',
        'number'                                                                  => 'Zahl',
        'password'                                                                => 'Passwort',
        'percent'                                                                 => 'Prozent',
        'search'                                                                  => 'Suche',
        'url'                                                                     => 'URL',
        'range'                                                                   => 'Spanne (range)',
        \HeimrichHannot\FilterBundle\Filter\Type\MultipleRangeType::TYPE          => 'Multi-Feld-Spanne (range)',
        'tel'                                                                     => 'Telefon',
        'color'                                                                   => 'Farbe',
        'choice'                                                                  => 'Choice',
        \HeimrichHannot\FilterBundle\Filter\Type\RadiusChoiceType::TYPE           => 'Radius-Choice',
        'country'                                                                 => 'Land',
        \HeimrichHannot\FilterBundle\Filter\Type\ProximitySearchType::TYPE        => 'Umkreissuche',
        'language'                                                                => 'Sprache',
        'locale'                                                                  => 'Region ("locale")',
        'parent'                                                                  => 'Elternentität',
        'skip_parents'                                                            => 'Elternentitäten ausschließen',
        'visible'                                                                 => 'Veröffentlicht',
        'button'                                                                  => 'Button',
        'reset'                                                                   => 'Reset',
        'submit'                                                                  => 'Submit',
        'hidden'                                                                  => 'Hidden',
        'checkbox'                                                                => 'Checkbox',
        'radio'                                                                   => 'Radio',
        'other'                                                                   => 'Sonstiges',
        'initial'                                                                 => 'Initial',
        \HeimrichHannot\FilterBundle\Filter\Type\DateTimeType::TYPE               => 'Datum & Zeit',
        \HeimrichHannot\FilterBundle\Filter\Type\DateType::TYPE                   => 'Datum',
        'time'                                                                    => 'Zeit',
        \HeimrichHannot\FilterBundle\Filter\Type\DateRangeType::TYPE              => 'Datumsspanne (date range)',
        \HeimrichHannot\FilterBundle\Filter\Type\SqlType::TYPE                    => 'SQL',
        \HeimrichHannot\FilterBundle\Filter\Type\SortType::TYPE                   => 'Sortierung',
        \HeimrichHannot\FilterBundle\Filter\Type\YearType::TYPE                   => 'Jahr',
        \HeimrichHannot\FilterBundle\Filter\Type\DateChoiceType::TYPE             => 'Datumsauswahl',
        \HeimrichHannot\FilterBundle\Filter\Type\ExternalEntityType::TYPE         => 'externe Entität',
        \HeimrichHannot\FilterBundle\Filter\Type\CurrentMemberType::TYPE          => 'aktuelles Mitglied',
        \HeimrichHannot\FilterBundle\Filter\Type\CurrentMemberType::TYPE_ID       => 'ID',
        \HeimrichHannot\FilterBundle\Filter\Type\CurrentMemberType::TYPE_USERNAME => 'Benutzername',
    ],
    'roundingMode'                                                                           => [
        \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_DOWN      => 'Abrunden (zu 0 hin)',
        \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_FLOOR     => 'Floor (zu –∞ hin)',
        \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_UP        => 'Aufrunden (von 0 weg)',
        \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_CEILING   => 'Ceiling (zu +∞ hin)',
        \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_HALF_DOWN => 'Half down (zum nächsten Nachbarn hin; bei Äquidistanz abrunden)',
        \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_HALF_EVEN => 'Half even (zum nächsten Nachbarn hin; bei Äquidistanz zum nächsten geraden Nachbarn runden)',
        \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_HALF_UP   => 'Half up (zum nächsten Nachbarn hin; bei Äquidistanz aufrunden)',
    ],
    'percentType'                                                                            => [
        'fractional' => 'Bruch (z. B. 0.55)',
        'integer'    => 'Ganzzahl (z. B. 55)',
    ],
    \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_SCALAR                      => 'Skalar',
    \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_ARRAY                       => 'Array',
    \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_LATEST                      => 'Neueste',
    \HeimrichHannot\FilterBundle\Filter\Type\ProximitySearchType::COORDINATES_MODE_COMPOUND  => 'Zusammengesetzt (&lt;lat&gt;,&lt;long&gt;)',
    \HeimrichHannot\FilterBundle\Filter\Type\ProximitySearchType::COORDINATES_MODE_SEPARATED => 'Separiert in Latitude- und Longitude-Feld'
];
