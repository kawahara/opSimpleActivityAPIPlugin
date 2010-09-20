<?php

class opSaaExportJson extends opSaaExport
{
  public function export(array $data)
  {
    $convertData = $this->convert($data);

    return json_encode($convertData);
  }

  protected function convert(array $array)
  {
    $result = array();

    foreach ($array as $key => $data)
    {
      if (is_array($data))
      {
        if (0 === count($data) || $key === 'statuses' || $key === 'status')
        {
          return $this->convert($data);
        }
        else
        {
          $result[$key] = $this->convert($data);
        }
      }
      else
      {
        $result[$key] = $data;
      }
    }

    return $result;
  }

  public function getContentType()
  {
    return 'application/json';
  }
}
