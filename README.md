__Version : 0.99__

It converts dBase files to sql scripts that can be imported into MySQL Database. It runs on console.

__Usage__:
Extract files from the dbf2sql package. dbf2sql.php and dbf_class.php are the required files.
Open terminal.
Change directory to where you have extracted the files.
Execute php dbf2sql.php _input_.dbf _tablename_ _output_.sql
where input.dbf is the dbf file to be converted, tablename is the name of the table that the generated SQL script should insert rows into.
`output.sql` is the output SQL script to be generated.

Only the input file argument is mandatory. The script is able to predict the table name and the output filename.

__Examples__:

`php dbf2sql.php input.dbf myData`

Since, output file name isn’t given, ‘myData.sql’ would be generated.

`php dbf2sql.php data.dbf`

This would create ‘data.sql’ script that would create a table named ‘data’.

`php dbf2sql.php --bulk` OR `php dbf2sql.php --all`

Converts all dbf files on current directory to sql scripts.