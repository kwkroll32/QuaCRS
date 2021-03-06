#IMPORTS
import sys
import os
from glob import glob
import getopt
from shutil import copyfile, rmtree
from pdb import set_trace as stop


#IMPORTS FOR UTILITIES
from collections import defaultdict
from re import split


#CONSTANT IMPORTS
from constant.constants import *
from constant.error import *
from constant.config import *

#IMPORT FOR DB
from db.database import *


CURDIR = os.path.abspath('')





def main():
	"""
	usage: python read.py [[-i|--input-file]|[-b|--input-directory]]
		   [-d|--delimiter] COMMAND [ARGS]

	*** One of the input file methods is mandatory!

	The commands are:
		create		create sample(s)
		update		Update sample(s)
		clear		clear the database
	"""



	# Parse the command line options
	try:
		opts, args = getopt.getopt(sys.argv[1:], "hi:d:b:", ["help", "input-file=", "delimiter=", "input-directory="])
	except getopt.error, msg:
		print msg
		print "For help use --help"
		sys.exit(2)

	# Checking for any arguments
	if len(args) == 0:
		print main.__doc__
		sys.exit(0)

	# Default options
	global DELIMITER
	DELIMITER="\t"
	
	# Processing the options
	for o, a in opts:
		if o in ("-i", "--input-file"):
			global INPUT_FILE
			INPUT_FILE=str(os.path.realpath(os.path.expanduser(a)))
			if not os.path.isfile(INPUT_FILE):
				print_error("Argument passed to '-i' is not a regular file! Verify that it exists, or use '-b' for directories.")
				sys.exit(1)

		if o in ("-d", "--delimiter"):
			DELIMITER=str(a)

		if o in ("-b", "--input-directory"):
			global INPUT_FILE_PATH
			INPUT_FILE_PATH=str(os.path.realpath(os.path.expanduser(a))) + '/'
			if not os.path.isdir(INPUT_FILE_PATH):
				print_error("Argument passed to '-b' is not a directory! Verify that it exists, or use '-i' for files.")
				sys.exit(1)

		if o in ("-h", "--help"):
			print main.__doc__
			sys.exit(0)

	print_log("PASSED PROCESSING THE OPTIONS")


	# Preparing for the processing
	#	1) Connecting to the database
	global db
	db=Database(DB_HOST, DB_USER, DB_PASS, DB_NAME,DB_PORT)
	if db == None:
		print_error(ERR_DB_NOT_CONNECTED)
		exit(1)

	
	# Processing the arguments
	for arg in args:
		process(arg)

	print_log("DONE WITH PROCESSING THE ARGUMENT(S)")

	print "\n\nOpen your browser and browse to where you uploaded the qc folder and see the result"




def process(arg):
	"""
	This is the main function that send the users' request to different parts
	of the program -- functions. In other word, we process the user's commands here

	The options that are available are:
		create
		update
		clear
	"""

	arg=arg.lower()

	if arg == "clear":
		if INPUT_FILE is not None:
			clear_item(get_unique_id_from_path(INPUT_FILE))
		else:
			clear_project()
		exit(0)

	# checking if users and permissions tables are in database yet
	if db.table_exist('users') == False:
		try:
			# Table does not exist.
			db.create_table('users',[("id","tinyint(4) NOT NULL PRIMARY KEY AUTO_INCREMENT"), ("username","varchar(10) NOT NULL"), ("password","varchar(100) NOT NULL")])
		except:
			print_error(ERR_DB_CREATE_TABLE)
			if exitIfError:
				sys.exit(1)
	if db.table_exist('permissions') == False:
		try:
			# Table does not exist.
			db.create_table('permissions',[("userID","int"), ("project","varchar(45)"), ("primary key(userID, project)")])
			db.insert(str('users'), str('username,password'), str("'user',MD5('password')"))
		except:
			print_error(ERR_DB_CREATE_TABLE)
			if exitIfError:
				sys.exit(1)	
	
	# checking to see if all the mandatory options were correctly inputed
	if INPUT_FILE is None and INPUT_FILE_PATH is None:
		print_error(ERR_INPUT_FILE_MISSING)
		exit(1)

	if WEB_APP_PATH == "" or WEB_APP_PATH is None:
		print_error(ERR_SERVER_NOT_SETUP)
		exit(1)
	
	if arg=="create":
		if INPUT_FILE_PATH is None:
			create()
		else:
			process_files("create")

	elif arg=="update":
		if INPUT_FILE_PATH is None:
			update()
		else:
			process_files("update")

	else:
		print "The command is not defined!"
		print main.__doc__
		sys.exit(0)

def process_files(command):
	"""
	This function is invoked only when the user is trying to create/update a bunch of files
	all together and does not want to run the same command multiple time for every sample.

	So they used the -p / --input-file-path option to specify where all the files are stored

	This fucntion will go through the files and set the INPUT_FILE constant to the appropriate
	values and then calls the create function.
	"""
	failed = 0
	ans = None
	rtn = -1
	try:
		files = glob(INPUT_FILE_PATH+"*.csv")
		print_log("READ ALL THE CSV FILES IN THE FOLDER")

		for m_file in files:
			print "Processing ", m_file, " . . . ."
			
			global INPUT_FILE
			INPUT_FILE = str(m_file)
			'''str(INPUT_FILE_PATH)+'''

			if command == "create":
				print_log("CALLING THE CREATE FUNCTION ")
				if create(exitIfError=False) == 1:
					failed += 1

			elif command == "update":
				print_log("CALLING THE UPDATE COMMAND")
				(rtn, ans) = update(exitIfError=False, yesToAll=True, answer=ans)

				if rtn == 1:
					failed += 1

	except Exception as ex:
	    template = "An exception of type {0} occured. Arguments:\n{1!r}"
	    message = template.format(type(ex).__name__, ex.args)
	    print message
	    sys.exit(1)
		

	print failed, "files failed/skipped"
	print len(files)-failed, "file(s) were successfully processed"



def create(exitIfError=True):
	"""
	This function will first checks to see if the table exists or not. If not, we create the table first!
	Then we check the entire document to see if there is going to be anything that is going to be overwritten
	if there is, we throw an error saying that the sample already exist and exit the program.
	otherwise, simply insert them into the table

	Arguments:
		exitIfError : when is set to True, the execution will stop when an error occurs. when set equal to 
					  False, it will simply return to the caller. This option is mainly used with -p / --input-file-path
	Output:
		When the exitIfError is set to False, the output of the function depends on whether an error occures
		or the execution for the file goes fine! if all goes fine, the function will return 0, if anything 
		goes wrong that would have caused the program to stop from execution, the function returns 1.			  
	"""

	ret = os.access(INPUT_FILE, os.R_OK)
	if ret == False:
		print_error(ERR_READ_PERMISSION_DENIED)
		if exitIfError:
			sys.exit(1)
		else:
			return 1

	
	inFile=open(INPUT_FILE,"r")
	header=get_header(inFile)
	# Checking if the header matches what is in the database
	header = check_header(header)

	# Check if we have already created the table
	# if not, create the table based on the columns defined in the constant.constants.QC_COLUMNS
	# if the tables does not exist, no data will be updated or over written, therefore, there will 
	# be no warning! However if the table already exists, that means that we need to check and see
	# the new data will be over righting any of the already previously existing data.
	#if db.table_exist(QC_TABLE_NAME) == False:
	create_database(exitIfError)

	# if the table already exist
	# we first have to process the line and see if the sample already exist
	#else:
	fwo = file_will_overwrite(inFile,header)

	#the data will overwrite something in the table
	if fwo == 1 or fwo == -1:
		print_error(ERR_SAMPLE_ALREADY_EXIST)
		if exitIfError:
			sys.exit(1)
		else:
			return 1

	# the data will not overwrite anything in the table
	else:
		#open the file again!
		inFile=open(INPUT_FILE,"r")
		#trim off the file header
		inFile.readline()

		insert_content_to_db(inFile,header)

	return 0



def update(exitIfError=True, yesToAll=False, answer=None):
	"""
	This function will update the a given sample id. If the sample does not exist, it will throw an error.
	If the sample exist but with updating, you will be overwriting some data during the process, it will
	throw a warning asking for the user's permission.
	If the data that is being uploaded, will not be overwriting any cell and the sample ID exist, the updating
	will take place without any error or warning.
	"""

	ret = os.access(INPUT_FILE, os.R_OK)
	if ret == False:
		print_error(ERR_READ_PERMISSION_DENIED)
		if exitIfError:
			sys.exit(1)
		else:
			return (1, None)

	print_log("HAVE PERMISSIONS TO READ THE FILE!")

	create_database(exitIfError=True)

	inFile=open(INPUT_FILE,"r")
	header=get_header(inFile)

	print_log("HAVE READ THE FILE AND THE HEADER")

	# Checking if the header matches what is in the database
	header = check_header(header)

	print_log("CHECKED THE HEADER AND THERE WAS NO PROBLEM")

	
	for line in inFile:
		line = line.strip('\n')
		data = split(DELIMITER, line)

		sample_id = data[header[SAMPLE_ID_COLUMN]]  # this is the Unique_ID
		sampleID = sample_exist(sample_id)			# this is the table's primary key or None
		
		if sampleID == None:
			# The sample does not exist in the database
			# throw an error 
			print_error(ERR_SAMPLE_DOES_NOT_EXIST)
			print "Try using `CREATE` instead of `UPDATE`"
			if exitIfError:
				sys.exit(1)
			else:
				return (1, None)
		else:
			#sample does exist, check to see if its going to overwrite anything
			if will_overwrite(data, header, True) != -1:
				print_log("INSIDE THE IF OF WILL OVERWRITE")
				
				if answer != "yta":
					answer = ask_to_overwrite(yesToAll=yesToAll).lower()

				if answer == "yta":
					update_line(data,header)
					return (0,"yta")

				elif answer == 'y':
					update_line(data,header)
					return (0,'y')

				elif answer == 's':
					return (1, 's')

				elif answer == 'q':
					if not exitIfError:
						print_warning(WRN_UPDATED_FILES)
					exit(0)

				else:
					print_error(ERR_INVALID_RESPONSE)
					exit(1)

			# sample data wont be overwriting the data (The sample might exist but the columns wont be overwritten)
			else:
				update_line(data,header)
				return (0,None)
				



	#check if the sample exist in this study!


def insert_content_to_db(inFile,header):
	"""
	In this function, the rest of the input file will be read and inserted into the database. 
	The program will first check the file to see if any data will get overwritten in this process
	and if so, the program will warn the user and give the user a chance to cancel the operation 
	or continue the process.
	"""
	for line in inFile:
		
		line  = line.strip('\n')
		data  = split(DELIMITER,line)

		columns = []
		values = []


		'''
		Create folders (image folders) corresponding to the patient
		'''
		
		if os.path.exists(WEB_APP_PATH+"assets/img/"+data[header[SAMPLE_ID_COLUMN]]) == False:
			os.makedirs(WEB_APP_PATH+"assets/img/"+data[header[SAMPLE_ID_COLUMN]])

		if os.path.exists(WEB_APP_PATH+"assets/img/"+data[header[SAMPLE_ID_COLUMN]]+"/"+FASTQC_FOLDER_NAME) == False:
			os.makedirs(WEB_APP_PATH+"assets/img/"+data[header[SAMPLE_ID_COLUMN]]+"/"+FASTQC_FOLDER_NAME)

		if os.path.exists(WEB_APP_PATH+"assets/img/"+data[header[SAMPLE_ID_COLUMN]]+"/"+RNASEQC_FOLDER_NAME) == False:
			os.makedirs(WEB_APP_PATH+"assets/img/"+data[header[SAMPLE_ID_COLUMN]]+"/"+RNASEQC_FOLDER_NAME)

		if os.path.exists(WEB_APP_PATH+"assets/img/"+data[header[SAMPLE_ID_COLUMN]]+"/"+RSEQC_FOLDER_NAME) == False:
			os.makedirs(WEB_APP_PATH+"assets/img/"+data[header[SAMPLE_ID_COLUMN]]+"/"+RSEQC_FOLDER_NAME)

		file_path = INPUT_FILE[:INPUT_FILE.rfind('/')+1]

		#checking to see if this sample has a combined version or not
		sampleName = data[header[SAMPLE_COLUMN]]
		studyName = data[header[STUDY_COLUMN]]
		combinedCol = data[header[DESCRIPTION_COLUMN]]
		hasCombined = has_combined_sample(sampleName)





		for key in header.keys():

			if IMAGE_COLUMN_POSTFIX in key:

				if data[header[key]] == "":
					continue

				imgName = data[header[key]].split('/')[-1]
				toolName = data[header[key]].split('/')[0]

				mFrom = file_path+data[header[key]]
				mTo = WEB_APP_PATH+"assets/img/"+data[header[SAMPLE_ID_COLUMN]]+"/"+toolName+"/"+str(imgName)
				copyFile(mFrom,mTo)
				data[header[key]] = data[header[SAMPLE_ID_COLUMN]]+"/"+toolName+"/"+str(imgName)


			columns.append('`'+str(key)+'`')
			values.append(prep_value_for_db(data[header[key]]))

		if combinedCol == "COMBINED":
			# we first have to "UNCOMBINE" the other samples of this that we thought
			# they might be combined! Simply becasue we did not have the combined version yet
			
			set_off_combined_flag(sampleName,studyName)

			columns.append("`"+COMBINED_FLAG_COLUMN+"`")
			values.append(prep_value_for_db(1))

		else:
			columns.append("`"+COMBINED_FLAG_COLUMN+"`")
			values.append(prep_value_for_db(int(not hasCombined)))
		
		
		db.insert(QC_TABLE_NAME,columns,values)




def file_will_overwrite(inFile, header):
	"""
	This function is very similar to the function below `will_overwrite`! the only difference is 
	that this file will check the entire inputFile and will_overwrite only checks one line of the file

	Output:
		- If there is anyline that has a column that will result in overwirting the data in the table
		it will return 1

		- If the columns that the data will be overwriting are empty in the table, it will return
		the value (-1)

		- If the columns will not overwrite anything, or basically the samples does not exist
		it will return 0 
	"""
	res = 0
	for line in inFile:
		line = line.strip('\n')
		data = split(DELIMITER, line)

		sample = data[header[SAMPLE_COLUMN]]
		wo = will_overwrite(data,header,False)
		if wo == 1:
			return 1
		elif wo == -1:
			res = -1

	return res



def will_overwrite(data, header, printDetail=True):
	"""
	Output:
		- If there is a column that has that will result in overwirting the data in the table
		it will return 1

		- If the columns that the data will be overwriting are empty/None in the table, it will return
		the value (-1)

		- If the columns will not overwrite anything, or basically the samples does not exist
		it will return 0 
	"""

	print_log("INSIDE WILL OVERWRITE")

	sampleID = data[header[SAMPLE_ID_COLUMN]] # this is the unique ID
	sampleData = db.select(QC_TABLE_NAME,where=SAMPLE_ID_COLUMN+"='"+sampleID+"'", limit=1)
	threshold = 0.0001
	changed = False

	if len(sampleData) == 0:
		return 0
	else:
		sampleData = sampleData[0]

	res = -1

	changed_data = [["Column", "Old_val", "New_val"]]
	changed_data_row = 1
	for key in header.keys():
		if key == SAMPLE_COLUMN:
			continue

		if IMAGE_COLUMN_POSTFIX in key:
			
			if data[header[key]]  != "":	# in the file, there is a location for the image

				if sampleData[QC_COLUMNS_DICT[key]] is None:	# in the db there is nothing
					return -1

				
				pathArr = data[header[key]].split('/')
				dbPathArr = sampleData[QC_COLUMNS_DICT[key]].split('/')
				print_log("AFTER THE SPLIT")

			
				if pathArr[0] == "FastQC":
					#print "DB   : ", dbPathArr
					#print "FILE : ", pathArr
					sampleFolderName = pathArr[1]
					
					pathArr[1] = pathArr[0]
					pathArr[0] = dbPathArr[0]
					pathArr.pop(2)
					newPath =  '/'.join(pathArr)

				else:
					pathArr.pop(1)
					pathArr = '/'.join(pathArr)
					newPath = pathArr
					newPath = sampleID+"/"+newPath
				

			
			if are_same_items(newPath, sampleData[QC_COLUMNS_DICT[key]]) == False :
				#print "SOMETHING IS CHANGED "
				#print data[header[key]] == ""
				#print "FILE : ", data[header[key]]
				#print "  DB : ", sampleData[QC_COLUMNS_DICT[key]]

				changed = True
				newData = newPath
				oldData = sampleData[QC_COLUMNS_DICT[key]]
			

		elif are_same_items(data[header[key]], sampleData[QC_COLUMNS_DICT[key]]) == False :
			newData = data[header[key]]
			oldData = sampleData[QC_COLUMNS_DICT[key]]
			changed = True

		if changed:
			#and data[header[key]] != sampleData[QC_COLUMNS_DICT[key]]:
			#if data[header[key]] == "" or abs(float(data[header[key]]) - float(sampleData[QC_COLUMNS_DICT[key]])) > threshold:
			#informing the user on what columns are being overwritten
			#FORMAT: Column_name		old_val		new_val
			if printDetail:
				#if res == -1:
					#print "Column\t\tOld_val\t\tNew_val"
					#print "----------------------------------------"
				#print str(key)+"\t\t"+str(oldData)+"\t\t"+str(newData)
				changed_data.append([])
				changed_data[changed_data_row] = [key, oldData, newData]
				changed_data_row += 1
				res = 1
			else:
				return 1

			changed = False

	if changed and printDetail:
		# print change dict here
		col_width = max(len(str(word)) for row in changed_data for word in row) + 2  
		for row in changed_data:
			print "".join(str(word).ljust(col_width) for word in row)

	return res


def update_line(data, header):
	"""
	This function will update the QC_TABLE with the content in the data variable. The primary key
	used to determin which line will be updated is the SAMPLE_COLUMN. 
	"""

	sample_uniq_ID = data[header[SAMPLE_ID_COLUMN]]
	sampleData = db.select(QC_TABLE_NAME,columns=QC_TABLE_NAME+"ID" ,where=SAMPLE_ID_COLUMN+"='"+sample_uniq_ID+"'", limit=1)[0]

	sampleID = str(sampleData[QC_COLUMNS_DICT[QC_TABLE_NAME+"ID"]])
	file_path = INPUT_FILE[:INPUT_FILE.rfind('/')+1]

	columns = []
	values = []
	
	for key in header.keys():

		if IMAGE_COLUMN_POSTFIX in key:

			if data[header[key]] == "":
				continue

			imgName = data[header[key]].split('/')[-1]
			toolName = data[header[key]].split('/')[0]

			mFrom = file_path+data[header[key]]
			mTo = WEB_APP_PATH+"assets/img/"+data[header[SAMPLE_ID_COLUMN]]+"/"+toolName+"/"+str(imgName)

			copyFile(mFrom, mTo)
			
			data[header[key]] = data[header[SAMPLE_ID_COLUMN]]+"/"+toolName+"/"+str(imgName)


		columns.append("`"+key+"`")
		values.append(prep_value_for_db(data[header[key]]))
	
	#print columns
	#print values
	db.update(QC_TABLE_NAME, columns, values, QC_TABLE_NAME+"ID"+"="+sampleID)



def has_combined_sample(sampleName):
	"""
	This function will look at all the same names and if there is a sample that is flagged at combined
	and have the same sample name, it will return True.
	Otherwise, returns False.
	"""

	res = db.select(QC_TABLE_NAME,columns=QC_TABLE_NAME+"ID", where=SAMPLE_COLUMN + " = '"+sampleName+"' AND " + DESCRIPTION_COLUMN + " = 'COMBINED'", limit=1)
	if len(res) == 0:
		return False
	else:
		return True

def set_off_combined_flag(sampleName, studyName):
	"""
	This function will set the combined flag to 0 for all the sample with sample name= `sampleName`

	This function is used when inserting samples into the database:
	when there is a sample that is combined, and we want to set the combined flags of all the runs for that sample
	to zero!

	"""

	db.update(QC_TABLE_NAME, COMBINED_FLAG_COLUMN, "0", SAMPLE_COLUMN+" ='"+sampleName+"' AND "+STUDY_COLUMN + "='"+studyName+"'")



# Utility Functions

def are_same_items(m_file, m_db):
	"""
	This function will check whether the two items passed to the function are the same or not
	There is also a threshold considered in this function
	"""
	threshold = 0.0001

	item1Str = str(m_file).lower()
	item2Str = str(m_db).lower()

	if m_db is None and (m_file is not None or m_file != ""):
		#print "1.5 IF"
		return True

	


	if item2Str == "":
		return True

	if item1Str == item2Str:
		return True

	item1IsAlpha = item1Str.isalpha()
	item2IsAlpha = item2Str.isalpha()


	#checking to see if they are both alphabetic
	if item1IsAlpha != item2IsAlpha:
		#print "SECOND IF"
		return False

	try:
		item1Float = float(item1Str)
		item2Float = float(item2Str)
		if abs(item1Float - item2Float) < threshold:
			return True
		else:
			#print "THRID CONDITION"
			return False
	except:
		#print "EXCEPTION"
		return False


def ask_to_overwrite(msg=None,yesToAll=False):
	"""
	this function will ask the user to if they want to overwrite the information in the table or NOT
	Output:
	is the literal string that they user enters!
	"""
	if msg is None:
		msg = WRN_OVERWRITING

	print_warning(msg)
	if yesToAll:
		answer = raw_input(OVERWRITING_Q_YTA)
	else:
		answer = raw_input(OVERWRITING_Q)
	return answer


def prep_value_for_db(value):
	"""
	this funcion will return the correct string for the value that you are trying to insert to the table
	For example, if the data is a number it will return the number!
	if the data is string it will return the string wil quotation marks srounding it!
	"""

	if value == "" or value is None:
		return "NULL"

	if str(value).isdigit():
			return int(value)
	else:
		try:
			return float(value)
		except:
			return "\""+str(value)+"\""

def sample_exist(sampleID):
	"""
	This function will return True is the given sample exist in the QC_TABLE
	Otherwise, it returns False
	"""

	res = db.select(QC_TABLE_NAME,columns="qcID", where=SAMPLE_ID_COLUMN+"='"+sampleID+"'", limit=1)
	if not res:
		return None
	else:
		return res[0][0]


def get_header(inFile):
	"""
	In this function, get_header(<inFile>), we look at the first line of the file and determin what the headers are and store the result in a dictionary which has the index of the column as the key, and the name of the column as the value

	Note, this function will read the first line of the file! Therefore, we wont worry about reading the header again.
	"""
	#VARIABLES
	headerDict = defaultdict(int)
	firstLine  = inFile.readline()

	firstLine  = firstLine.strip('\n')
	firstLine  = split(DELIMITER,firstLine)

	for i,tab in enumerate(firstLine):
		headerDict[str(tab).strip('\n').strip('\r').replace(" ","_")]=i

	return headerDict


def check_header(header):
	"""
	This function will compare the input file header to the columns in the existing MySQL database
	If all of the file columns are already in the database, returns (a copy of) the original header 
	If there are unexpected columns in the file, they are removed and returns only the columns that are permitted in the database
	If the input file header lacks any of the requried columns, throws error and quit program. 
	"""
	reqs = [STUDY_COLUMN, SAMPLE_ID_COLUMN, SAMPLE_COLUMN, DESCRIPTION_COLUMN]
	if not all([ header.has_key(x) for x in reqs ]):
		print_error("Missing one of the required columns! {0}".format(str(reqs)))
		sys.exit(1)
	new_header = {}
	
	for key in header.keys():
		if QC_COLUMNS_DICT.has_key(key) == False:
			print_warning(WAR_DB_COLUMN.format(key))
		else:
			new_header[key] = header[key]
	
	return new_header 



def get_unique_id_from_path(path):
	"""
	This function will parse the path given as the parameter and take the last part of the path.
	Then takes out the "qc_" and the ".csv" portion of the path and resturns the result
	"""
	uniqueID = path.strip('\n').split('/')[-1]
	uniqueID = uniqueID[3:-4]
	return uniqueID
	

def copyFile(_from, _to):
	"""
	This function simply copies the file from `from` to `to`.
	Please note that the value of `from` has to be a file! it cannot be a folder containing files
	"""

	#print "FROM : ", _from
	#print "TO   : ", _to
	if not os.path.exists('/'.join(_to.split('/')[:-1])):
		os.makedirs('/'.join(_to.split('/')[:-1]))
	copyfile(_from, _to)


def clear_item(uniqueID):
	"""
	This function will remove one item from the project
	"""
	db.delete(QC_TABLE_NAME,SAMPLE_ID_COLUMN + " = '" + uniqueID + "'" )

	if os.path.exists(WEB_APP_PATH+"assets/img/"+uniqueID):
		print "removing : ",WEB_APP_PATH+"assets/img/"+uniqueID
		rmtree(WEB_APP_PATH+"assets/img/"+uniqueID)


	
def clear_project(exitIfError=True):
	"""
	This function will erease the tables and views that were created
	The name of the table that is going to be deleted, are all in the config
	file in the constant forler.

	This function is dropping/deleting all views and the single table
	"""
	tables_to_delete = db.get_table_names()
	db.clear_qc_data(tables_to_delete)

	db.drop_table(QC_TABLE_NAME)
	if os.path.exists(WEB_APP_PATH+"assets/img/"):
		files = os.listdir(WEB_APP_PATH+"assets/img/")
	else:
		files = []
	for img_folder in files:
		print "removing : ",WEB_APP_PATH+"assets/img/"+img_folder
		rmtree(WEB_APP_PATH+"assets/img/"+img_folder)

	if os.path.exists(WEB_APP_PATH+"assets/reports/"):
		files = os.listdir(WEB_APP_PATH+"assets/reports/")
	else:
		files = [] 
	for report in files:
		print "removing : ",WEB_APP_PATH+"assets/reports/"+report
		os.remove(WEB_APP_PATH+"assets/reports/"+report)
	
	# recreate the database tables and views
	create_database(exitIfError)

def create_database(exitIfError):
	try:
		
		if db.table_exist(QC_TABLE_NAME) == False:
			# Table does not exist
			db.create_table(QC_TABLE_NAME,QC_COLUMNS)

		#copying the qc table stucture to the server
		mFrom = '/'.join((sys.argv[0].split('/')[:-1] + ['constant/qc_table_definition.xml'])) 
		mTo = WEB_APP_PATH+"assets/config/"+QC_TABLE_DEFINITION

		copyFile(mFrom , mTo)
		
		#creating the views for the front end part:
		# There are 9 views that need to be created!

		# if there's a metric in the database that isn't in the XML, remove it from the database
		
		database_columns = db.get_column_names()
		database_columns.remove('cur_timestamp')
		delete_these_cols = set(database_columns) - set(QC_COLUMNS_DICT.keys())
		#stop()
		for col in delete_these_cols:
			print_warning(WRN_DELETING_COLUMN.format(col))
			answer = raw_input(DELETING_Q).lower()
			if answer == "yes" or answer == "y":
				#stop()
				db.alter_table(QC_TABLE_NAME, col, delete=True)
			else:
				print_error(ERR_DB_DELETE)
				sys.exit(1)

		for table, columns in VIEWS.items():
			if table == QC_TABLE_NAME:
				# we already created the primary qc table at the start of this fxn			
				continue

			# if any XML metric is missing from the primary qc table, add it
			for col in columns:
				search_col = "`" + col + "`"
				if not db.select(QC_TABLE_NAME, columns=search_col) and db.select(QC_TABLE_NAME, columns=search_col) != tuple():
					db.alter_table(QC_TABLE_NAME, [ QC_COLUMNS[QC_COLUMNS_DICT[col]] ] )
			
			# if a view already exists, drop it and rebuild it
			# simultaneously handles adding columns to an existing view and removing columns from exiting views
			#     since the existing view may be different than what has been parsed from the XML definitions 
			if table and db.table_exist(table):
				db.drop_table(table, True)
				db.create_view(table, QC_TABLE_NAME, columns=columns)
			elif table and not db.table_exist(table):
				db.create_view(table, QC_TABLE_NAME, columns=columns)

		# delete any old views that existed in the database, but are now not defined in the XML
		for table in (set(db.get_table_names()) - set(VIEWS.keys())):
			db.drop_table(table, True)

	except Exception:
		#something went wrong while creating the table
		print_error(ERR_DB_CREATE_TABLE)
		if exitIfError:
			sys.exit(1)
		else:
			return 1


def print_error(objs):
    print "ERROR: ", objs

def print_warning(objs):
    print "WARNING: ", objs

def print_log(objs):

	if DEBUG_MODE:
		print "LOG: ", objs


if __name__ == "__main__":
	main()
