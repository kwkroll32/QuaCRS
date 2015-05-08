#This file contains all the information needed to connect to the MySQL database


DB_HOST="localhost"
DB_USER="root"
DB_PASS="password"
DB_PORT=""
DB_NAME="QuaCRS"

"""
This constant should contain where you copy the files
for the front enf of the project.

You should have a server (local) running on the machine that you are running the code.

**Full path

"""
WEB_APP_PATH="/var/www/html/qc/"




#DEBUGGING OPTIONS DB
#OPTIONS:
#
#	-NONE
#	-ALL
#	-INSERTS
#	-UPDATES
#	-DELETES
#	-SELECTS
#	-CREATES
#	-VIEWS
#	or any combination of these in an array
#	["INSERTS", "SELECTS"]

DB_DEBUG_MODE = "NONE"
DEBUG_MODE = False
