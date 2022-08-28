<?php

require_once('utils.php');
require_once('csv2sheets.php');
require_once('xlsxHeader.php');

use \utils\Utils;
use \header\XlsxHeader;

$csv_stream = fopen('csv_examples/5m_sales.csv', 'r');
stream_set_read_buffer($csv_stream, 1024*50); // default = 4096 bytes

Utils::copy_directory("util/minimalXlsx/", "outputDir/"); // outputDir must exists (TODO fix this)

$splitter = new splitter\Splitter();
$number_of_sheets = $splitter->splitCSVIntoSheetFiles($csv_stream, "outputDir/minimalXlsx/xl/worksheets/", 1000*1000);

XlsxHeader::updateContentTypes("outputDir/minimalXlsx/", $number_of_sheets);
XlsxHeader::updateRelationshipsAndWorkbookFiles("outputDir/minimalXlsx/", $number_of_sheets);

Utils::zip_directory("outputDir/minimalXlsx/", "outputDir/minimalXlsx_2.xlsx");

?>