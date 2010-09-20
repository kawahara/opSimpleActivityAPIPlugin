<?php

include_once dirname(__FILE__).'/../../bootstrap/unit.php';

$t = new lime_test(2, new lime_output_color());

$instance = new opSaaExportJson();
$t->is($instance->export(array('foo' => array('foo' => 'bar'))), '{"foo":{"foo":"bar"}}', '->export() returns string of JSON');

$t->is($instance->export(array(
  'statuses' => array(
    array('status' => array('foo1' => 'bar', 'foo2' => 'bar')),
    array('status' => array('foo1' => 'bar', 'foo2' => 'bar')),
  )
)), '[{"foo1":"bar","foo2":"bar"},{"foo1":"bar","foo2":"bar"}]', '->export() returns string of JSON');
