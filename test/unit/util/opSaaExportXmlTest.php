<?php

include_once dirname(__FILE__).'/../../bootstrap/unit.php';

$t = new lime_test(4, new lime_output_color());

$instance = new opSaaExportXml();
$t->is($instance->export(array('foo' => array('foo' => 'bar'))), '<?xml version="1.0" encoding="UTF-8"?>
<foo><foo>bar</foo></foo>
', '->export() returns string of XML');

$t->is($instance->export(array('statuses' => array(array('foo' => 'bar'), array('foo' => 'bar')))),
'<?xml version="1.0" encoding="UTF-8"?>
<statuses type="array"><foo>bar</foo><foo>bar</foo></statuses>
', '->export() returns string of XML');

$t->is($instance->export(array('statuses' => array(array('foo' => array('foo' => 'bar')), array('foo' => 'bar')))),
'<?xml version="1.0" encoding="UTF-8"?>
<statuses type="array"><foo><foo>bar</foo></foo><foo>bar</foo></statuses>
', '->export() returns string of XML');

$t->is($instance->export(array('statuses' => array())),
'<?xml version="1.0" encoding="UTF-8"?>
<statuses type="array"/>
', '->export() returns string of XML');

