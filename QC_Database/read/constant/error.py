#ERRORS
ERR_READ_PERMISSION_DENIED="Read permission denied! We need to have read permission to the input file"
ERR_HEADER_NOT_MATCH="The headers in the input file does not match the fields in the database. For more info, please read the README file"
ERR_INVALID_RESPONSE="Your response is NOT valid! Exiting the program now"
ERR_INPUT_FILE_MISSING="No input file was specifed. Use -i or --input-file to specify the input file."
ERR_INPUT_FILE_NAME_FORMAT="The input file name has to follow this rule: <STUDY TYPE: T|S|M|R>_<STUDY NAME>_*.*"
ERR_SAMPLE_ALREADY_EXIST="At least one of the samples that you are trying to create already exist! Please use the update statment if you want ot upldate the dataset!"
ERR_SAMPLE_DOES_NOT_EXIST="The sample does not exist in the database!"
ERR_DIRECTORY_PROBLEM="Something went wrong while trying to read the content in the directory specified using -p / --input-file-path!"
ERR_SERVER_NOT_SETUP="The `WEB_APP_PATH` is not set to point at where your server is. Please set the `WEB_APP_PATH` to a folder in the server where you coppied all the files for the fornt end."

#DB_ERRORS
ERR_DB_CREATE_TABLE="While creating the table, something went wrong! Check the fileds variable format"
ERR_DB_INSERT="Insertion failed! Make sure that you are using the correnct fields and values."
ERR_DB_TABLE_DOES_NOT_EXIST="The table you are using does NOT exist in the database"
ERR_DB_UPDATE_COLUMNS_MISMATCH="In the update, the number of elements in the columns variable should match the number of element in the values variable."
ERR_DB_NOT_CONNECTED="The database is not connected! Please check the config.py inside the constant folder!"
ERR_DB_TABLE_ALREADY_EXIST="The table already exist"
ERR_DB_VIEW_ALREADY_EXIST="The view already exist"
ERR_DB_DELETE="XML column definitions must match the MySQL database columns. Update the XML or delete the offending column(s)."

#WARNINGS
WAR_DB_COLUMN="The column {0} does not exist in the database. Has it been properly defined in the XML definitions?"

ERR_DB_IDK="Unexpected Error!"