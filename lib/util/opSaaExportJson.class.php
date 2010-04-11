<?php

class opSaaExportJson extends opSaaExport
{
  public function export(array $data)
  {
    return json_encode($data);
  }

  public function getContentType()
  {
    return 'application/json';
  }
}
