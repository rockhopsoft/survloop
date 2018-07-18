/*
$zips = SLZips::orderBy('ZipZip', 'asc')->get();
if ($zips->isNotEmpty()) {
    $founds = [0, 0, []];
    foreach ($zips as $z) {
        if (!in_array($z->ZipState, ['PR', 'VI', 'AE', 'MH', 'MP', 'FM', 'PW', 'GU', 'AS', 'AP', 'AA'])) {
            $chk = SLZipAshrae::where('AshrState', $z->ZipState)
                ->where('AshrCounty', $z->ZipCounty)
                ->get();
            if ($chk->isEmpty()) {
                $chk = SLZipAshrae::where('AshrState', $z->ZipState)
                    ->where('AshrCounty', $z->ZipCounty . ' PARISH')
                    ->first();
                if ($chk && isset($chk->AshrCounty)) {
                    $chk->AshrCounty = str_replace(' PARISH', '', $chk->AshrCounty);
                    $chk->save();
                    $founds[0]++;
                } else {
                    $founds[1]++;
                    if (!isset($founds[2][$z->ZipState])) $founds[2][$z->ZipState] = [[], ''];
                    if (!isset($founds[2][$z->ZipState][0][$z->ZipCounty])) $founds[2][$z->ZipState][0][$z->ZipCounty] = '';
                    $founds[2][$z->ZipState][0][$z->ZipCounty] = ', ' . $z->ZipZip;
                }
            } else {
                $founds[0]++;
            }
        }
    }
    if (sizeof($founds[2]) > 0) {
        foreach ($founds[2] as $state => $arr) {
            $zipStates = SLZips::where('ZipState', $state)
                ->orderBy('ZipCounty', 'asc')
                ->get();
            if ($zipStates->isNotEmpty()) {
                foreach ($zipStates as $st) {
                    if (strpos($founds[2][$state][1], ',' . $st->ZipCounty . ',') === false) {
                        $founds[2][$state][1] .= ',' . $st->ZipCounty . ',';
                    }
                }
            }
        }
    }
}
echo '<br /><br /><br />found total: ' . $founds[0] . ', not found total: ' . $founds[1] . '<br /><pre>'; print_r($founds[2]); echo '</pre>';
*/

/*
// Import ASHRAE Zip Codes (Ran One-Time)
$file = '../vendor/wikiworldorder/ASHRAE-raw.txt';
if (file_exists($file)) {
    $lines = $GLOBALS["SL"]->mexplode("\n", file_get_contents($file));
    if (sizeof($lines) > 0) {
        $currZone = '1A';
        echo 'line count: ' . sizeof($lines) . '<br />';
        foreach ($lines as $i => $l) {
            if (trim($l) != '' && substr($l, 0, 2) != '//') {
                if (substr($l, 0, 2) == '==') {
                    $currZone = str_replace('==', '', $l);
                } else {
                    $row = $GLOBALS["SL"]->mexplode(',', trim($l));
                    $row[0] = trim(str_replace('COUNTY', '', strtoupper($row[0])));
                    $row[1] = trim($row[1]);
                    $chk = SLZipAshrae::where('AshrCounty', $row[0])
                        ->where('AshrState', $GLOBALS["SL"]->states->getStateAbrr($row[1]))
                        ->get();
                    if ($chk && isset($chk->AshrZone)) echo '<br /><br /><br />found: ' . $l . '<br />';
                    else {
                        $chk = new SLZipAshrae;
                        $chk->AshrZone = $currZone;
                        $chk->AshrState = $GLOBALS["SL"]->states->getStateAbrr($row[1]);
                        $chk->AshrCounty = $row[0];
                        $chk->save();
                    }
                }
            }
        }
    }
}
*/
