---
title: Adding Nodes to the DOM
---

The parser allows you to add new elements to an existing document. Find below an
example for that.

**Remarks**

- It is not possible to create nodes via `->outertext`, `->innertext`, or
  `->plaintext`. These properties only change the text representation of a node
  and will return undesired results if used incorrectly.
- Use [`$html->createElement`](/api/simple_html_dom/createElement) and
  [`$html->createTextNode`](/api/simple_html_dom/createTextNode) to create
  new nodes.
- Use [`$node->appendChild`](/api/simple_html_dom_node/appendChild) to add a
  node as child to another node.
- Nodes can be combined in any order.

**Example**

```php
<?php

// This example illustrates adding new elements to the DOM.

require_once 'simple_html_dom.php';

/***************************** table data *************************************/

$header = array('Ocean', 'Volume (km^3)');

$data = array(
    array('Arctic Ocean', 18750000),
    array('Atlantic Ocean', 310410900),
    array('Indian Ocean', 264000000),
    array('Pacific Ocean', 660000000),
    array('Souce China Sea', 9880000),
    array('Southern Ocean', 71800000)
);

/***************************** template ***************************************/

$doc = <<<EOD
<html>
<head>
    <style>
table { border: 1px solid black; }

tr:nth-child(even)  { background: #CCC }
tr:nth-child(odd)   { background: #FFF }
    </style>
</head>
<body>
    <h1>Volumes of the World's Oceans</h1>
</body>
</html>
EOD;

/***************************** code *******************************************/

$html = str_get_html($doc);
$body = $html->find('body', 0);
$table = $html->createElement('table');

// Header row
$tr = $html->createElement('tr');
foreach ($header as $entry) {
    $th = $html->createElement('th', $entry);
    $tr->appendChild($th);
}
$table->appendChild($tr);

// Table data
foreach ($data as $row) {
    $tr = $html->createElement('tr');
    foreach ($row as $entry) {

        // (optional) Add info to the volume column
        if (is_numeric($entry)) {
            $value = number_format($entry);
            $td = $html->createElement('td', $value);
            $td->setAttribute('volume', $entry);
        } else {
            $td = $html->createElement('td', $entry);
        }

        $tr->appendChild($td);
    }
    $table->appendChild($tr);
}

$body->appendChild($table);

echo $html . PHP_EOL;

/**
 * Output (beautified)
 *
 * <html>
 * <head>
 *   <style>
 *     table { border: 1px solid black; }
 *     tr:nth-child(even)  { background: #CCC }
 *     tr:nth-child(odd)   { background: #FFF }
 *   </style>
 * </head>
 * <body>
 *   <h1>Volumes of the World's Oceans</h1>
 *
 *       Ocean  Volume (km^3)
 *       Arctic Ocean <div class="volume="18750000">18,750,000
 *       Atlantic Ocean <div class="volume="310410900">310,410,900
 *       Indian Ocean <div class="volume="264000000">264,000,000
 *       Pacific Ocean <div class="volume="660000000">660,000,000
 *       Souce China Sea <div class="volume="9880000">9,880,000
 *       Southern Ocean <div class="volume="71800000">71,800,000
 *
 * </body>
 * </html>
 */
```
