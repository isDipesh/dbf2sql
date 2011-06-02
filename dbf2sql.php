#!usr/bin/php
<?php

/*
dbf2sql - dbf to sql converter
coded by xtranophilist
xtranophilist [at] gmail [dot] com
http://twitter.com/xtranophilist
utilizes DBF reader Class by Faro K Rasyid
*/

require_once("sys/base_class.php");

function displayHelp() {
    echo "\nUsage:\n php csv2sql.php <input_dbf_file> [<table_name>] [<output_sql_file>] [<options>]\n
        <input_dbf_file>  : full path of the file name, the name of the file only if in the same directory.
                            This argument is mandatory.\n
        <table_name>      : the name of the table in the database where data is to be inserted.
                            This argument is optional. If it's not specified, the name of the table will be the name of the input file without the extension.\n\n
        <output_sql_file> : the name of the output SQL file that should contain insert statements.
                            This argument is optional. If it's not specified, the name of the table will be as the filename with sql extension name.

        <options> :
            -h or --help  : Displays this help message!
            -b or --bulk  : Bulk Conversion - Converts all dbf files found in script directory to sql files!
            \n\n"
    ;
    die;
}

function convert($in,$table_name,$out){
//check if file exists
if (!file_exists($in)) {
    die ("\nError : The specified file $in doesn't exist!\n");
}

//check if file is empty
if (!filesize($in)) {
    die ("\nError : The specified file $in is empty!\n");
}

echo "\nStarting Conversion of $in (".format_size(filesize($in)).")!\n";

$dbf = new dbf_class($in);
$num_rec=$dbf->dbf_num_rec;
$num_field=$dbf->dbf_num_field;

$h=fopen($out, "w");
if($h) {
    echo "Writing ".$out."\n";
}else {
    die("\nWriting output file ".$out." failed!\n");
}





//data type conversions from dbase to sql
$type['N']="DOUBLE";
$type['C']="VARCHAR";
$type['F']="FLOAT";
$type['D']="DATE";
$type['L']="BIT";//logical
$type['M']="TEXT";//text


//create a create table query
$cq="CREATE TABLE IF NOT EXISTS ".$table_name." (";
//initate the common query string
$fi="INSERT INTO ".$table_name." VALUES (";

for($j=0; $j<$num_field; $j++) {
    //add size in paranthesis only if varchar
    $t=($dbf->dbf_names[$j]['type']=='C')?"(".$dbf->dbf_names[$j]['len'].")":'';
    //append each field entry into array
    $ta[]=" ".strtolower($dbf->dbf_names[$j]['name'])." ".$type[$dbf->dbf_names[$j]['type']].$t;
}
//create query from the array of fields
fwrite($h,$cq.implode(',',$ta).");\n\n");

//count the number of rows
$cnt=$num_rec/150;
$cntr=0;


for($i=0; $i<$num_rec; $i++) {
//increase the counter and upon reaching the multiple of cnt, print '.' to show the progress
    $cntr++;
//    if($cnt!=0) {
//        if (($cntr%$cnt)==0) echo ".";
//    }
    //get each row
    $row = $dbf->getRow($i);
    //into each field
    $ta=array();
    for($j=0; $j<$num_field; $j++) {
        //append each field value to the array with single quote after escaping it
        $ta[]="'".doEscape($row[$j])."'";
    }
    //insert row query
    fwrite($h,$fi.implode(',',$ta).");\n");
}


fclose($h);
if(!format_size(filesize($out))) {
    echo "\nError: You provided a file that is not a DBF!";
} else {
    echo "\nSuccess: The converted file $out is ".format_size(filesize($out))." big!\n";
}

}

function getFileName($a) {
    //returns the part of string before a period (.)
    $p=explode('.', $a);
    return $p[0];
}

function getExtension($a) {
    //returns the part of string after last period (.)
    $p=explode('.', $a);
    return $p[count($p)-1];
}

function doEscape($a) {
    $a=str_replace("'","\'",$a);
    $a=str_replace("\ ","\ ",$a);
    return $a;
}

function format_size($size) {
    $sizes = array(" Bytes", " KB", " MB", " GB");
    if ($size == 0) {
        return(0);
    } else {
        return (round($size/pow(1024, ($i = floor(log($size, 1024)))), $i > 1 ? 2 : 0) . $sizes[$i]);
    }
}

function bye(){
    error_reporting(0);
    echo "\nScript ended at ".date('l jS \of F Y h:i:s A')."\n\n";
    exit;
}

//Handle parameters

//check if help is asked
foreach ($argv as $arg) {
    if ($arg=='-h' ||$arg=='--help') {
        echo "Displaying help!\n";
        displayHelp();
    }
}

//check if bulk conversion
foreach ($argv as $arg) {
    if ($arg=='-a' ||$arg=='--all' ||$arg=='-b' ||$arg=='--bulk') {
        $c=0;
        echo "\nBulk Conversion Selected!\n";
    if ($handle = opendir('.')) {
      while (false !== ($file = readdir($handle))) {
        if (getExtension($file)=="dbf" || getExtension($file)=="DBF"){
            convert($file,strtolower(getFileName($file)),strtolower(getFileName($file)).".sql");
            $c++;
        }
    }
    closedir($handle);
    echo "\n$c files were successfully converted!\n";
}
        bye();
    }
}


$in=$argv[1];

if ($argc<2) {
    echo "\nError : Input DBF file name not specified!\n";
    displayHelp();
}
elseif ($argc<3) {
    echo "\nNotice : Table name not specified!\nUsing input file name for the table name and output file name!";
    $table_name = getFileName($in);
    $out = $table_name.".sql";
}
elseif ($argc<4) {
    echo "\nNotice : Output file name not specified!\nUsing table name for output file name!";
    $table_name = $argv[2];
    $out = strtolower($table_name.".sql");
}
else {
    $table_name = $argv[2];
    $out = $argv[3];
}

convert($in,$table_name,$out);


bye();
?>
