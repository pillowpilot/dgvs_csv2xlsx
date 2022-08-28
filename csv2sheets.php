<?php
/*
 * This namespace contains the main logic behind csv2xlsx
 */

namespace splitter;

require_once('utils.php');

use \utils\Utils;

class Splitter 
{
    public $UTIL_DIR = "util/";
    public $CSV_SEPARATOR = ',';
    public $LINE_ENDING = "\n"; // must be string
    public $MAX_LINE_LENGTH = 1024; // in bytes

    function __construct($util_dir = "util/", $csv_separator = ',', $line_ending = "\n", $max_line_length = 1024)
    {
        $this->UTIL_DIR = $util_dir;
        $this->CSV_SEPARATOR = $csv_separator;
        $this->LINE_ENDING = $line_ending;
        $this->MAX_LINE_LENGTH = $max_line_length;
    }

    public static function splitLineIntoValues($line, $separator)
    {
        $values = explode($separator, $line);
        return $values;
    }

    public function copyStreamToStream($source, $destination)
    {
        while(!feof($source)){
            $buffer = fread($source, $this->MAX_LINE_LENGTH);
            fwrite($destination, $buffer);
        }
    }

    public $sheet_id = 1;
    public function open_new_sheet_stream($output_dir)
    {
        $sheet_stream = fopen("{$output_dir}sheet-{$this->sheet_id}.xml", 'w');
        stream_set_write_buffer($sheet_stream, 1024*50);
        $sheet_header_stream = fopen("{$this->UTIL_DIR}sheet-header.xml", 'r');

        echo "Sheet {$this->sheet_id} opened. ";
        $this->copyStreamToStream($sheet_header_stream, $sheet_stream);

        $this->sheet_id++;
        fclose($sheet_header_stream);


        return $sheet_stream;
    }

    public function close_sheet_stream($sheet_stream)
    {
        $sheet_footer_stream = fopen("{$this->UTIL_DIR}sheet-footer.xml", 'r');
        
        $this->copyStreamToStream($sheet_footer_stream, $sheet_stream);

        fclose($sheet_footer_stream);
        fclose($sheet_stream);
        echo "Sheet closed.\n";
    }

    /*
     * Main function. Generates enough XLSX sheet files from the data provided.
     * 
     * Parameters:
     * $csv_stream: stream from which the csv is read (use fopen(...))
     * $output_dir: string with the path of the output directory (must exists)
     * $max_rows_per_sheet: number of rows per sheet
     * 
     * Return:
     * The number of sheets created.
     */
    public function splitCSVIntoSheetFiles($csv_stream, $output_dir, $max_rows_per_sheet)
    {
        $sheet_file_handle = $this->open_new_sheet_stream($output_dir);

        $row_id = 1;
        while( ($line = stream_get_line($csv_stream, $this->MAX_LINE_LENGTH, $this->LINE_ENDING)) !== false)
        {
            $values = $this->splitLineIntoValues($line, $this->CSV_SEPARATOR);
        
            $row_start = "<row r=\"{$row_id}\">\n";
            fwrite($sheet_file_handle, $row_start);
        
            $value_index = 0;
            foreach($values as $value)
            {
                $pretty_value = trim($value);
                $coord = Utils::stringFromColumnIndex($value_index) . $row_id;
                $cell_line = "<c r=\"{$coord}\" t=\"inlineStr\"><is><t>{$pretty_value}</t></is></c>\n";
                fwrite($sheet_file_handle, $cell_line);
        
                $value_index++;
            }
            $row_end = "</row>\n";
            fwrite($sheet_file_handle, $row_end);
        
            if($row_id === $max_rows_per_sheet)
            {
                $this->close_sheet_stream($sheet_file_handle);
                $sheet_file_handle = $this->open_new_sheet_stream($output_dir);
                $row_id = 1;
            }
            else
            {
                $row_id++;
            }
        }
        
        $this->close_sheet_stream($sheet_file_handle);
        fclose($csv_stream);

        return $this->sheet_id - 1;
    }

}

?>