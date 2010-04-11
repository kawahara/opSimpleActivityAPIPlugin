<?php

include_once dirname(__FILE__).'/../../bootstrap/unit.php';

$t = new lime_test(1, new lime_output_color());

$instance = new opSaaExportXml();
$t->is($instance->export(array('foo' => array('foo' => 'bar'))), '<?xml version="1.0" encoding="UTF-8"?>
<foo><foo>bar</foo></foo>
', '->export() returns string of XML');
