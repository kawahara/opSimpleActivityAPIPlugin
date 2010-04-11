<?php

include_once dirname(__FILE__).'/../../bootstrap/unit.php';

$t = new lime_test(1, new lime_output_color());

$instance = new opSaaExportJson();
$t->is($instance->export(array('foo' => array('foo' => 'bar'))), '{"foo":{"foo":"bar"}}', '->export() returns string of JSON');
