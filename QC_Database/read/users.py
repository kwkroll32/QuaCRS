#IMPORTS
import sys
import os
from glob import glob
import getopt
from shutil import copyfile, rmtree
import getpass
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
	usage: python users.py [-u|--user] [-s|--study]
		   COMMAND

	The commands are:
		create 		Create user [ and add to study ] \n\t\t\t\tRequires user, study optional\n
		add 		Add existing users to studies \n\t\t\t\tRequires study, users optional\n
		remove		Remove user from study \n\t\t\t\tRequires either user or study, or both\n
		show		Show set permissions\n
		clear		Clear all users and permissions from the database \n\t\t\t\tReset to default
	"""



	# parse the command line options
	try:
		opts, args = getopt.getopt(sys.argv[1:], "hu:s:", ["help", "user=", "study="])
	except getopt.error, msg:
		print msg
		print "For help use --help"
		sys.exit(2)

	# Checking for any arguments
	if len(args) == 0:
		print main.__doc__
		sys.exit(0)

	# processing the options
	for o, a in opts:
		if o in ("-u", "--user"):
			global USERS
			USERS=str(a)

		if o in ("-s", "--study"):
			global STUDIES
			STUDIES=str(a)

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

	
	# processing the arguments
	for arg in args:
		process(arg)

	#print_log("DONE WITH PROCESSING THE ARGUMENT(S)")

def process(arg):
	"""
	This is the main function that send the users' request to different parts
	of the program -- functions. In other word, we process the user's commands here

	The options that are available are:
		create
		add
		remove
		show
	"""
	global given_users
	global given_studies
	try:
		USERS
	except :
		given_users = False
	else:
		given_users = True
	try:
		STUDIES
	except:
		given_studies = False
	else:
		given_studies = True

	if arg == "create":
		if given_users:
			add_users()
		else:
			print main.__doc__
			sys.exit(0)
		if given_studies:
			add_studies()
		exit(0)

	elif arg=="add":
		try:
			STUDIES
		except:
			print main.__doc__
			sys.exit(0)
		else:
			add_studies()
		exit(0)

	elif arg=="remove":
		if given_users and given_studies:
			remove_studies()
		elif given_users and not given_studies:
			remove_studies()
			remove_users()
		elif given_studies and not given_users:	
			remove_studies()
		exit(0)

	elif arg=="show":
		show_perms()

	elif arg=="clear":
		clear()

	else:
		print "The command is not defined!"
		print main.__doc__
		sys.exit(0)


def add_users(exitIfError=True):
	""" 
	This function will add users to the users table

	"""
	if db.table_exist('users') == False:
		try:
			# Table does not exist.
			fields = [("id","tinyint(4) NOT NULL PRIMARY KEY AUTO_INCREMENT"), ("username","varchar(10) NOT NULL"), ("password","varchar(100) NOT NULL")]
			db.create_table('users',fields)
		except:
			print_error(ERR_DB_CREATE_TABLE)
			if exitIfError:
				sys.exit(1)
	for element in USERS.split(','):
		print("enter password for user: " + element)
		p = getpass.getpass(stream=sys.stderr)
		thing1 = str('users')
		thing2 = str('username,password')
		thing3 = str("'") + element + str("',MD5('") + str(p) + str("')")
		thing4 = str("username='") + element + str("'")
		exists = db.user_exists(thing1, element)
		if exists == 0:
			db.insert(thing1,thing2,thing3)
		elif exists > 0:
			db.update(thing1,thing2,thing3,thing4)

def add_studies(exitIfError=True):
	"""
	This function will add studies to the permissions table. If users are not supplied, defaults to all users

	"""
	if db.table_exist('permissions') == False:
		try:
			# Table does not exist.
			fields = [("userID","int"), ("project","varchar(45)"), ("primary key(userID, project)")]
			db.create_table('permissions',fields)
		except:
			print_error(ERR_DB_CREATE_TABLE)
			if exitIfError:
				sys.exit(1)
	if given_studies and not given_users:
		members = ""
		for pair in user_list():
			members += pair[0] + ","
		global USERS
		USERS = members.strip(",")
	else:
		pass
	for study in STUDIES.split(','):
		for user_name in USERS.split(','):
			try:
				user_id = db.select('users', columns='id', where=str("username='" + user_name + "'"))[0][0]
			except:
				print(user_name + " not found in users table. Create this user with 'create'")
				continue
			thing1 = str('permissions')
			thing2 = str('userID,project')
			thing3 = str("'") + str(user_id) + str("','") + str(study) + str("'")
			exists = db.perms_exist(user_id, study)			
			if exists == 0:
				print("Adding " + user_name + " to " + study)
				db.insert(thing1, thing2, thing3)
			else:
				print(user_name + " is already in study " + study)


def remove_users(exitIfError=True):
	""" 
	This function will remove users from the users table

	"""
	if db.table_exist('users') == False:
		if exitIfError:
			sys.exit(1)
	for user_name in USERS.split(','):
		thing1 = str('users')
		thing2 = str("username='") + user_name + str("'")
		exists = db.user_exists(thing1, user_name)
		if exists == 0:
			print(user_name + " is not a registered user")
		else:
			db.delete(thing1,thing2)
			print(user_name + " removed")

def remove_studies(exitIfError=True):
	""" 
	This function will remove user permissions from the permissions table associated with the studies

	"""
	if db.table_exist('permissions') == False:
		print("no permissions table")
		if exitIfError:
			sys.exit(1)
	try:
		for user_name in USERS.split(','):
			try:
				user_id = db.select('users', columns='id', where=str("username='" + user_name + "'"))[0][0]
			except:
				print(user_name + " not found in users table. Create this user with 'add'")
				if exitIfError:
					sys.exit(1)
			thing1 = str('permissions')
			try:
				for study in STUDIES.split(','):
					thing2 = str("userID='") + str(user_id) + str("' AND project='") + str(study) + str("'")
					exists = db.perms_exist(user_id, study)
					if exists == 0:
						print(user_name + " is not authorized for " + study)
					else:
						db.delete(thing1,thing2)
						print(user_name + " removed from " + study)
			except:
				thing2 = str("userID='") + str(user_id) + str("'")
				db.delete(thing1,thing2)
	except NameError:
		for study in STUDIES.split(','):
			thing1 = str('permissions')
			thing2 = str("project='") + str(study) +str("'")
			db.delete(thing1,thing2)
			print("all user permissions removed for project " + str(study))

def show_perms():
	"""
	This function will show all users in the database, and all set permissions
	"""
	print("\nUser\tPermitted Projects\n--------------------------")
	for pair in user_list():
		user_name = pair[0]
		user_id = pair[1]
		user_projects = ""
		for proj in db.select('permissions', columns='project', where=str("userID='" + str(user_id) + "'")):
			user_projects += str(proj[0]) + ", "
		if user_projects == "":
			user_projects = None
		print(str(user_name) + ":\t" + str(user_projects).strip(', '))
	print("\n")

def clear():
	"""
	This function will erease the tables and views that were created
	Including permissions and users tables
	"""

	db.drop_table('permissions')
	db.drop_table('users')
	db.create_table('users',[("id","tinyint(4) NOT NULL PRIMARY KEY AUTO_INCREMENT"), ("username","varchar(10) NOT NULL"), ("password","varchar(100) NOT NULL")])
	db.create_table('permissions',[("userID","int"), ("project","varchar(45)"), ("primary key(userID, project)")])
	db.insert(str('users'), str('username,password'), str("'user',MD5('password')"))
	print("permissions reset to default values")

def print_error(objs):
    print "ERROR: ", objs

def print_warning(objs):
    print "WARNING: ", objs

def user_list():
	out_list = []
	queryRes = db.select('users', columns='username, id')
	if queryRes:
		for i in queryRes:
			out_list.append(i)
	return out_list

def print_log(objs):

	if DEBUG_MODE:
		print "LOG: ", objs


if __name__ == "__main__":
	main()
