<?php

namespace header;

class XlsxHeader
{
    public static function updateContentTypes($output_dir, $number_of_sheets)
    {
        $to_inject = "";
        for($i = 1; $i <= $number_of_sheets; $i++)
        {
            $to_inject .= "<Override PartName=\"/xl/worksheets/sheet-{$i}.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml\"/>\n";
        }

        // $content_types_stream = fopen("{$output_dir}[Content_Types].xml", 'w');
        $content_types_content = file_get_contents("{$output_dir}[Content_Types].xml");
        $content_types_content = str_replace("</Types>", $to_inject . "</Types>", $content_types_content);
        file_put_contents("{$output_dir}[Content_Types].xml", $content_types_content);
    }

    public static function updateRelationshipsAndWorkbookFiles($output_dir, $number_of_sheets)
    {
        $initial_id = 3;

        $to_inject_into_rels = "";
        $to_inject_into_wb = "";
        for($i = 1; $i <= $number_of_sheets; $i++)
        {
            $id = $initial_id + $i;
            $to_inject_into_rels .= "<Relationship Id=\"rId{$id}\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet\" Target=\"worksheets/sheet-{$i}.xml\"/>\n";
            $to_inject_into_wb .= "<sheet name=\"Sheet{$i}\" sheetId=\"{$i}\" r:id=\"rId{$id}\"/>\n"; // sheetId must start with 1
        }

        $relationships_content = file_get_contents("{$output_dir}/xl/_rels/workbook.xml.rels");
        $relationships_content = str_replace("</Relationships>", $to_inject_into_rels . "</Relationships>", $relationships_content);
        file_put_contents("{$output_dir}/xl/_rels/workbook.xml.rels", $relationships_content);

        $workbook_content = file_get_contents("{$output_dir}/xl/workbook.xml");
        $workbook_content = str_replace("</sheets>", $to_inject_into_wb . "</sheets>", $workbook_content);
        file_put_contents("{$output_dir}/xl/workbook.xml", $workbook_content);
    }
}

?>