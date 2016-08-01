<?php namespace Norse\Util;

use Norse\Util\ZipStream;

class Util {
  public function serialize($data, $rep = 'json') {
    switch ($rep) {
      case 'csv':
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Content-Description: File Transfer');
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=ips.csv");
        header("Expires: 0");
        header("Pragma: public");
        $fh = @fopen( 'php://output', 'w'  );
        fputcsv($fh, array_keys($data[0]));
        foreach ($data as $row) {
          fputcsv($fh, array_values($row));
        }
        fclose($fh);
        exit;

      case 'json':
      default:
        header('Content-Type: application/json');
        echo json_encode($data,  JSON_NUMERIC_CHECK);
    }
  }
  public function getIfSet($val, $default) {
    return isset($_GET[$val]) ? $_GET[$val] : $default;
  }
}
