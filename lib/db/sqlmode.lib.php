<?php
  /**
   * This file contains MySql queries highlighter.
   * It uses to show mysql-queries in color.
   *
   * @author Alex Roldugin <alex.roldugin@gmail.com>
   * @copyright Alex Roldugin
   */

  /**
   * Class to highlight MySQL-code
   */
class SqlMode {
  var $keywords = array(
    "action", "add", "after", "against", "all", "alter", "and", "as", "asc",
    "auto_increment", "avg_row_length", "bdb", "between", "by", "cascade",
    "case", "change", "character", "check", "checksum", "close", "collate",
    "collation", "column", "columns", "comment", "committed", "concurrent",
    "constraint", "create", "cross", "data", "database", "default",
    "delay_key_write", "delayed", "delete", "desc", "directory", "disable",
    "distinct", "distinctrow", "do", "drop", "dumpfile", "duplicate", "else",
    "enable", "enclosed", "end", "escaped", "exists", "fields", "first", "for",
    "force", "foreign", "from", "full", "fulltext", "global", "group", "handler",
    "having", "heap", "high_priority", "if", "ignore", "in", "index", "infile",
    "inner", "insert", "insert_method", "into", "is", "isam", "isolation", "join",
    "key", "keys", "last", "left", "level", "like", "limit", "lines", "load",
    "local", "lock", "low_priority", "match", "max_rows", "merge", "min_rows",
    "mode", "modify", "mrg_myisam", "myisam", "natural", "next", "no", "not",
    "null", "offset", "oj", "on", "open", "optionally", "or", "order", "outer",
    "outfile", "pack_keys", "partial", "password", "prev", "primary",
    "procedure", "quick", "raid0", "raid_type", "read", "references", "rename",
    "repeatable", "restrict", "right", "rollback", "rollup", "row_format",
    "savepoint", "select", "separator", "serializable", "session", "set",
    "share", "show", "sql_big_result", "sql_buffer_result", "sql_cache",
    "sql_calc_found_rows", "sql_no_cache", "sql_small_result", "starting",
    "straight_join", "striped", "table", "tables", "temporary", "terminated",
    "then", "to", "transaction", "truncate", "type", "uncommitted", "union",
    "unique", "unlock", "update", "use", "using", "values", "when", "where",
    "with", "write", "xor"
  );

  var $types = array(
    "bigint", "binary", "bit", "blob", "bool", "boolean", "char", "curve", "date",
    "datetime", "dec", "decimal", "double", "enum", "fixed", "float", "geometry",
    "geometrycollection", "int", "integer", "line", "linearring", "linestring",
    "longblob", "longtext", "mediumblob", "mediumint", "mediumtext",
    "multicurve", "multilinestring", "multipoint", "multipolygon",
    "multisurface", "national", "numeric", "point", "polygon", "precision",
    "real", "smallint", "surface", "text", "time", "timestamp", "tinyblob",
    "tinyint", "tinytext", "unsigned", "varchar", "year", "year2", "year4",
    "zerofill",
  );

  var $colors = array(
    "keywords" => "#008000",
    "types" => "blue",
    "columns" => "blue",
    "digits" => "#c53d33",
    "seq" => "#e6e6e6",
  );
  
  function hiString($string) {
    $string = htmlspecialchars($string);
    $pattern = implode($this->keywords, '|');
    $result = preg_replace('/([0-9\*"\']+)/', '<span style="color: '.$this->colors["digits"].'">\\1</span>', $string);
    $result = preg_replace('/('.$pattern.')(\s+)/i', '<span style="color: '.$this->colors["keywords"].'">\\1</span>\\2', $result);
    
    $pattern = implode($this->types, '|');
    $result = preg_replace('/('.$pattern.')(\s+)/i', '<span style="color: '.$this->colors["types"].'">\\1</span>\\2', $result);
    
    $result = preg_replace('/(`[^`]*?`)/i', '<span style="color: '.$this->colors["columns"].'">\\1</span>', $result);

    //$result = preg_replace('/(from|where|limit)/i', "\r\n\t\\1", $result);


    return $result;
  } // hiString

  function hiArray($array) {
    if(sizeof($array)) {
      $temp = array();
      foreach($array as $string) {
        $temp[] = $this->hiString($string);
      }
      $array = $temp;
    }
    return $array;
  } // hiArray

  /**
   * Generates highlighted before array of sql-queries
   *
   * @param array $array array of highlighted before queries
   */
  function generate($array) {
    $result = "";
    ob_start();
    if(sizeof($array)) {
      $i = 0;
      foreach($array as $string) {
        $i++;
        echo "<span style='background-color: ".$this->colors["seq"]."; border-right: 1px solid #949494;'>".$i.".&nbsp;</span>&nbsp;";
        echo $string."<br/>\r\n";
      }
    }
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
  } // generate

  /**
   * Static method to highlight SQL-code
   */
  function hi($hi) {
    if(is_string($hi)) {
      $hi = array($hi);
    }

    $hi = $this->hiArray($hi);
    $result = $this->generate($hi);
    ob_start();
    echo '<pre style="background-color: #f9f9f9;">';
    if(sizeof($hi)) {
      echo $result;
    } else {
      echo "<span style='background-color: " . $this->colors["seq"] . "; border-right: 1px solid #949494;'>1.&nbsp;</span>&nbsp;" . 
        "<span style='color: green;'>Query list is empty</span>";
    }
    echo '</pre>';
    $result = ob_get_contents();
    ob_end_clean();
    
    return $result;
  } // hi
  
}

?>