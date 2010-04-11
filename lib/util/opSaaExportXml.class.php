<?php

class opSaaExportXml extends opSaaExport
{
  protected $domDocument;

  public function export(array $data)
  {
    $this->domDocument = new DOMDocument('1.0', 'UTF-8');
    $this->arrayToXml($data);
    return $this->domDocument->saveXML();
  }

  protected function arrayToXml(array $array, DOMElement $parentElement = null)
  {
    foreach ($array as $key => $data)
    {
      if (is_array($data))
      {
        $element = $this->domDocument->createElement($key);
        $this->arrayToXml($data, $element);
      }
      else
      {
        $element = $this->domDocument->createElement($key, $data);
      }

      if ($parentElement)
      {
        $parentElement->appendChild($element);
      }
      else
      {
        $this->domDocument->appendChild($element);
      }
    }
  }

  public function getContentType()
  {
    return 'application/xml';
  }
}
