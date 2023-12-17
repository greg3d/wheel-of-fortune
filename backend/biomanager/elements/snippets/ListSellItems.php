<?php
/** @var modX $modx */
/** @var array $scriptProperties */
/** @var BioManager $BioManager */
$BioManager = $modx->getService('BioManager', 'BioManager', MODX_CORE_PATH . 'components/biomanager/model/', $scriptProperties);
if (!$BioManager) {
    return 'Could not load BioManager class!';
}

$tpl = $modx->getOption('tpl', $scriptProperties, 'tpl.sellPoints.item');
$limit = $modx->getOption('limit', $scriptProperties, 1000);
$outputSeparator = $modx->getOption('outputSeparator', $scriptProperties, "\n");
$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, false);

$c = $modx->newQuery('SellPoint');
$c->sortby('city', ASC);
$c->sortby('priority', ASC);

//$c->where(['active' => 1]);
$c->limit($limit);
$items = $modx->getIterator('SellPoint', $c);

// Iterate through items
$list = [];
$list[] = "<table class=\"tb-2 c4\" id=\"fullList\" style=\"margin-top: 40px\">\n";
$list[] = "<tbody>";

/** @var SellPoint $item */
foreach ($items as $item) {
    $list[] = $modx->getChunk($tpl, $item->toArray());
}

$list[] = "</tbody></table>";

// Output
$output = implode($outputSeparator, $list);
if (!empty($toPlaceholder)) {
  
    $modx->setPlaceholder($toPlaceholder, $output);
    return '';
}

return $output;


/*

    
    $datatext = $v['name'];
    
    $datatext=preg_replace_callback(
        '#(([\"]{2,})|(?![^\W])(\"))|([^\s][\"]+(?![\w]))#u',
        function ($matches) {
            if (count($matches)===3) return "«»";
            else if ($matches[1]) return str_replace('"',"«",$matches[1]);
            else return str_replace('"',"»",$matches[4]);
        },
        $datatext
    );
    
*/