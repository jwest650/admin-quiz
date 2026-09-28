<?php
/*
 * Reads an uploaded CSV the way spreadsheet programs actually save it.
 *
 * Question and member imports ask people to fill a sheet in Excel (or Google
 * Sheets, Numbers, LibreOffice) and upload it as CSV. What comes out varies:
 *
 *   - "CSV UTF-8" starts with a byte-order mark, which sticks to the first heading.
 *   - Where the decimal separator is a comma, Excel separates cells with ";".
 *   - Plain "CSV" on Windows is Windows-1252, not UTF-8, so é or ’ arrive as bad bytes.
 *   - Rows that were cleared rather than deleted come out as ",,,,,".
 *
 * csv_import_rows() smooths all of that over and returns the rows that have
 * data, each with the line number people see in their spreadsheet.
 */

/*
 * The non-blank rows of a CSV file, as array('line' => int, 'cells' => array of
 * trimmed strings). Returns false when the file cannot be read.
 */
function csv_import_rows($path)
{
    $content = is_string($path) && $path !== '' ? @file_get_contents($path) : false;
    if ($content === false) {
        return false;
    }

    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
    $content = csv_import_to_utf8($content);
    $delimiter = csv_import_delimiter($content);

    $stream = fopen('php://temp', 'r+');
    fwrite($stream, $content);
    rewind($stream);

    $rows = array();
    $line = 0;
    // The escape argument is passed explicitly: PHP 8.4 prints a deprecation notice otherwise.
    while (($cells = fgetcsv($stream, 0, $delimiter, '"', '\\')) !== false) {
        $line++;
        $cells = array_map(function ($value) { return trim((string) $value); }, $cells);
        if (implode('', $cells) === '') {
            continue;
        }
        $rows[] = array('line' => $line, 'cells' => $cells);
    }
    fclose($stream);
    return $rows;
}

/* The text as UTF-8, treating anything that is not valid UTF-8 as Windows-1252 (Excel's plain "CSV"). */
function csv_import_to_utf8($content)
{
    if (preg_match('//u', $content)) {
        return $content;
    }
    if (function_exists('mb_convert_encoding')) {
        return mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
    }
    if (function_exists('iconv')) {
        $converted = @iconv('Windows-1252', 'UTF-8//IGNORE', $content);
        if ($converted !== false) {
            return $converted;
        }
    }
    return $content;
}

/* Comma, semicolon or tab: whichever appears most in the first line, ignoring quoted text. */
function csv_import_delimiter($content)
{
    $first_line = strtok($content, "\r\n");
    if ($first_line === false) {
        return ',';
    }
    $unquoted = preg_replace('/"[^"]*"/', '', $first_line);
    $best = ',';
    $best_count = substr_count($unquoted, ',');
    foreach (array(';', "\t") as $candidate) {
        $count = substr_count($unquoted, $candidate);
        if ($count > $best_count) {
            $best = $candidate;
            $best_count = $count;
        }
    }
    return $best;
}
