<?php

abstract class opSaaExport
{
  abstract function export(array $data);

  function getContentType()
  {
    return 'text/plain';
  }
}
