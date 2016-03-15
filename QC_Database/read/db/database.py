#IMPORTS
import MySQLdb as mdb
from pdb import set_trace as stop # pdb.set_trace()

#IMPORTS FOR DATABASE CREDENTIALS
from constant.config import *
from constant.error import *
from constant.warning import *
from constant.constants import *


class Database:

	def __init__(self, db_host, db_user, db_pass, db_name, db_port=""):
		"""
		In this function, the program attemps to connect to the database.
		If no connection can be made, the program will output the appropriate
		error and exits the program.

		INPUTS:
			db_host:	Is where the database is hosted. e.i. localhost
			db_user:	Is the user used to connect to the Database
			db_pass:	Is the password used for the MySQL Database
			db_name:	Is the name of the database used for this project
			db_port:	Is the port used to connect to MySQL database (OPTIONAL)

		OUTPUT:
			True if a connection to the databse was successfully made
			False if no connection was made
		"""
		self.con=None
		self.cur=None
		try:
			self.con = mdb.connect(db_host, db_user, db_pass, db_name);
			self.cur = self.con.cursor()

		except:
			return None


	def is_connected(self):
		"""
		This function will check if there is a connection to the MySQL database
		if so returns True otherwise returns False
		"""
		if self.con:
			return True
		else:
			return False


	def table_exist(self, tableName):
		"""
		This function will return if a table with the name `tableName` exist in the
		database that is currently being used. If so, return True! Otherwise, return False
		"""
		sql = "SHOW TABLES LIKE '"
		sql +=str(tableName)
		sql +="'"

		try:
			self.cur.execute(sql)
			if self.cur.rowcount == 0:
				return False
			else:
				return True
		except:
			self.print_db_error(ERR_DB_IDK)
			return False


	def create_table(self, tableName, fields):
		"""
		This function will create a table with the name `tableName` with the `fields`
		The fields variable have to have the format of
			[(<str_field_name>,<str_field_type>), ...]


		NOTE: if a table with the name `tableName` already exist, this function will NOT re-create the table
		Example:
			fields=[("id","INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT"),
					("fname","VARCHAR(45) NOT NULL"),
					("lname","VARCHAR(45) NOT NULL"),
					("email","VARCHAR(45)")]

			db.create_table("user",fields)
		"""

		# checking to see if the table already exists
		if self.table_exist(tableName):
			print WRN_DB_TABLE_EXISTS
			return False

		sql = "CREATE TABLE `"
		sql += str(tableName)
		sql += "` ( "

		if isinstance(fields,list):
			for i,field in enumerate(fields):
				if isinstance(field,tuple):
					sql += "`"+str(field[0])+"` "+str(field[1])+", "

		sql=sql[:-2]	#getting rid of the extra comma at the end!

		sql += " )"
		
		self.debug_log(sql,"CREATES")

		try:
			self.cur.execute(sql)
			return True
		except:
			self.print_db_error(ERR_DB_CREATE_TABLE)
			return False

	def alter_table(self, tableName, fields, delete=False):
		"""
		This function will alter a table, adding column `fields` to table `tableName`
		The fields variable have to have the format of
			[(<str_field_name>,<str_field_type>), ...]


		NOTE: if a table with the name `tableName` already exist, this function will NOT re-create the table
		Example:
			fields=[("id","INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT"),
					("fname","VARCHAR(45) NOT NULL"),
					("lname","VARCHAR(45) NOT NULL"),
					("email","VARCHAR(45)")]

			db.create_table("user",fields)
		"""

		# checking to see if the table already exists
		# check if column exists too?
		#if self.table_exist(tableName):
			#print WRN_DB_TABLE_EXISTS
		sql = "ALTER TABLE `"
		sql += str(tableName)
		if not delete:
			sql += "` ADD "

			if isinstance(fields,list):
				for i,field in enumerate(fields):
					if isinstance(field,tuple):
						sql += "`"+str(field[0])+"` "+str(field[1])+", "

			sql=sql[:-2]	#getting rid of the extra comma at the end!

			sql += " AFTER `"
			sql += str([ k for k,v in QC_COLUMNS_DICT.items() if v == int(QC_COLUMNS_DICT[fields[0][0]] - 1) ][0])
			sql += "`;"
			self.debug_log(sql,"alters in a new column")
		elif delete:
			sql += "` DROP COLUMN "
			sql += "`"+str(fields)
			sql += "`;"
			self.debug_log(sql,"alters out an old column")
		try:
			
			self.cur.execute(sql)
			return True
		except:
			self.print_db_error("error modifying the table {0} with column {1}".format(str(tableName), str(fields)))
			return False



	def alter_view(self, viewName, fields):
		"""
		This function will alter a table, adding column `fields` to table `viewName`
		The fields variable have to have the format of
			[(<str_field_name>,<str_field_type>), ...]


		NOTE: if a table with the name `viewName` already exist, this function will NOT re-create the table
		Example:
			fields=[("id","INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT"),
					("fname","VARCHAR(45) NOT NULL"),
					("lname","VARCHAR(45) NOT NULL"),
					("email","VARCHAR(45)")]

			db.create_table("user",fields)
		"""

		# checking to see if the table already exists
		# check if column exists too?
		#if self.table_exist(viewName):
			#print WRN_DB_TABLE_EXISTS

		sql = "ALTER VIEW `"
		sql += str(viewName)
		sql += "` AS SELECT `"
		sql += str(fields[0][0])
		sql += "` FROM `"
		sql += QC_TABLE_NAME
		sql += "`;"
		self.debug_log(sql,"alters in a new column")
		try:
			self.cur.execute(sql)
			return True
		except:
			self.print_db_error("error modifying the view {0} with column {1}".format(str(viewName), str(fields[0][0])))
			return False

	def user_exists(self, tableName, username):
		"""
		The function is used to test whether a user already exists in the `users` table
		"""

		# Checking if the table exist
		if self.table_exist(tableName) == False:
			self.print_db_error(ERR_DB_TABLE_DOES_NOT_EXIST)
			return False

		sql = "SELECT EXISTS( SELECT 1 FROM "
		sql += str(tableName)
		sql += " WHERE username='"
		sql += str(username)
		sql += "' ); "

		self.debug_log(sql,"EXISTS")
		try:
			self.cur.execute(sql)
			self.con.commit()
			for guy in self.cur:
				return guy[0]
		except:
			self.print_db_error(ERR_DB_INSERT)
			return False

	def perms_exist(self, userID, study):
		"""
		The function is used to test whether a user already has permissions in the `permissions` table
		"""

		# Checking if the table exist
		if self.table_exist("permissions") == False:
			self.print_db_error(ERR_DB_TABLE_DOES_NOT_EXIST)
			return False

		sql = "SELECT EXISTS( SELECT 1 FROM "
		sql += str("permissions")
		sql += " WHERE userID='"
		sql += str(userID)
		sql += "' AND "
		sql += "project='"
		sql += str(study)
		sql += "' ); "

		self.debug_log(sql,"EXISTS")
		try:
			self.cur.execute(sql)
			self.con.commit()
			for guy in self.cur:
				return guy[0]
		except:
			self.print_db_error(ERR_DB_INSERT)
			return False



	def insert(self, tableName, columns, values):
		"""
		In this function, the `values` will be inserted in the `columns` in the `tableName`
		OUTPUT:	This function will return the id of the inserted row.

		This function can also be used with list as the columns and values
		columns=["fname","lname","email"]
		values=["'Nima'", "'Esmaili Mokaram'", "'niesmo@yahoo.com'"]
		print db.insert("user",columns,values)
		Example:
			print db.insert("user","fname, lname, email","'Nima','Esmaili Mokaram', 'niesmo@yahoo.com'")
		"""

		# Checking if the table exist
		if self.table_exist(tableName) == False:
			self.print_db_error(ERR_DB_TABLE_DOES_NOT_EXIST)
			return False

		if isinstance(columns,str):
			columns=columns.strip('\n').split(',')

		if isinstance(values,str):
			values=values.strip('\n').split(',')


		sql = "INSERT INTO "
		sql += str(tableName)
		sql += " ( "
		
		for col in columns:
			sql += str(col) + ", "
		sql = sql[:-2]

		sql += " ) "
		sql += "VALUES ("
		
		for val in values:
			sql += str(val) + ", "
		sql = sql[:-2]			
		
		sql += " ) "

		self.debug_log(sql,"INSERTS")
		try:
			self.cur.execute(sql)
			self.con.commit()
			return self.cur.lastrowid
		except:
			self.print_db_error(ERR_DB_INSERT)
			return False

	def update(self, tableName, columns, values, where):
		"""
		This function will update the the column and set those to the values specified 
		where the `where` is true
		
		There are two ways to use this function, one is to use it with the parameters being a list
			In this case, the elements in the columns should correspond to the elements in the 
			values in the same order.
			For example:
				columns=['fname','lname']
				values=["'John'","'Doe'"]
				db.update("user",columns, values,"email='niesmo@yahoo.com'")

		The other way is to use them as being strings
		In this way you populate the columns and values with comma separated strings.
		For example:
			columns="fname, lname"
			values="'John','Doe'"

			db.update("user","fname, lname", "'John', 'Doe'", "email='niesmo@yahoo.com'")
			db.update("user",columns, values, "email='niesmo@yahoo.com'")

		Output:
			This function will return the number of rows that were affected by this update statment
		"""

		if self.table_exist(tableName) == False:
			self.print_db_error(ERR_DB_TABLE_DOES_NOT_EXIST)
			return False

		if isinstance(columns, str):
			columns=columns.strip('\n').split(',')

		if isinstance(values, str):
			values=values.strip('\n').split(',')

		if len(columns) != len(values):
			self.print_db_error(ERR_DB_UPDATE_COLUMNS_MISMATCH)
			return False



		sql = "UPDATE "
		sql += str(tableName)
		sql += " SET "
		for i,col in enumerate(columns):
			sql += str(col) + " = " + str(values[i]) + " , "

		sql=sql[:-2] #getting rid of the extra comma at the end

		sql += " WHERE "
		sql += str(where)

		self.debug_log(sql,"UPDATES")

		try:
			self.cur.execute(sql)
			self.con.commit()
			return self.cur.rowcount
		except:
			self.print_db_error(ERR_DB_IDK)
			return False


	def delete(self, tableName, where):
		"""
		This function will remove the rows that satisfy the where condition

		Example:
			db.delete("user", "name = 'Nima'")

		Output:
			This function will return false if for any reason, it is not able to delete the rows
			and will return the number of rows deleted if everything went fine.
		"""

		if self.table_exist(tableName) == False:
			self.print_db_error(ERR_DB_TABLE_DOES_NOT_EXIST)
			return False

		sql = "DELETE FROM "
		sql += str(tableName)
		sql += " WHERE "
		sql += str(where)

		self.debug_log(sql, "DELETES")

		try:
			self.cur.execute(sql)
			self.con.commit()
			return self.cur.rowcount
		except:
			self.print_db_error(ERR_DB_IDK)
			return False


	def select(self, tableName, columns="*", where=None,group=None,order=None,limit=None,having=None, mode="ALL" ):
		"""
		This is probably by far the most commonly used function of this file where you are
		able to retrieve data back from the database.

		There is only 1 mandatory parameter: tableName
		In this case, it will return all the rows and columns of the given table name.

		Parameters (in order):
			tableName:	Name of the table that you are trying to get data from
			columns:	List of the columns that you are getting data back. | Default='*'
			where:		Condition that you are filtering the query on. | default = ''
			group:		List of the columns that you are grouping over. | default = ''
			order:		List of columns that you are sorting by. You can include ASC or DESC to change the direction of the order | default = ''
			limit:		The number of rows that you are willing to get back from the table | default = ''
			having:		Having is used in very specific occasions. Read more about it in the link below. | default = ''
			mode:		Mode can be one of the following: [ALL | DISTINCT | DISTINCTROW ]. | default = "ALL"

		Output:
			An array containing the result of the query! The result is like a 2D array that you
			can access the columns in the same order that you ask for them in the `columns` variable

			If for any reason, the query was not processed, False will be returned. An appropriate message
			will be outputted on the screen

		"""

		if self.table_exist(tableName) == False:
			self.print_db_error(ERR_DB_TABLE_DOES_NOT_EXIST)
			return False

		sql = "SELECT "
		sql += str(mode) + " "
		sql += str(columns) + " "
		sql += " FROM `"
		sql += str(tableName) + "` "

		if where is not None:
			sql += " WHERE " + str(where)

		if group is not None:
			sql += " GROUP BY " + str(group)

		if having is not None:
			sql += " HAVING " + str(having)

		if order is not None:
			sql += " ORDER BY " + str(order)

		if limit is not None:
			sql += " LIMIT " + str(limit)

		self.debug_log(sql, "SELECTS")

		try:
			self.cur.execute(sql)
			return self.cur.fetchall()
		except:
			#self.print_db_error(ERR_DB_IDK)
			return False




	def create_view(self, viewName, tableName, columns="*", where=None,group=None,order=None,limit=None,having=None, mode="ALL" ):
		"""
		This function creates views.
		The parameters are very similar to the select fucntion of this class.
		There is only one difference between select and create_view and that is the fact that in this function
		a view will be created for such select statment.

		Parameters (in order):
			viewName: 	Name of the view that you are trying to create
			tableName:	Name of the table that you are trying to get data from
			columns:	List of the columns that you are getting data back. | Default='*'
			where:		Condition that you are filtering the query on. | default = ''
			group:		List of the columns that you are grouping over. | default = ''
			order:		List of columns that you are sorting by. You can include ASC or DESC to change the direction of the order | default = ''
			limit:		The number of rows that you are willing to get back from the table | default = ''
			having:		Having is used in very specific occasions. Read more about it in the link below. | default = ''
			mode:		Mode can be one of the following: [ALL | DISTINCT | DISTINCTROW ]. | default = "ALL"
		"""

		if self.table_exist(viewName):
			self.print_db_error(ERR_DB_VIEW_ALREADY_EXIST)
			return False

		'''
		# this is handled elsewhere
		# insert any new columns into the database
		for col in columns:
			if not self.select(tableName, columns=col):
				print "The column {0} is not in table {1}!".format(col, tableName)
				self.alter_table(tableName, [ QC_COLUMNS[QC_COLUMNS_DICT[col]] ] )
		'''

		str_column = ""
		if isinstance(columns, list):
			for col in columns:
				str_column += '`'+str(col)+'` ' + ', '

		str_column = str_column[:-2]


		sql =  "CREATE VIEW "
		sql += "`"+str(viewName)+"`"
		sql += " AS "

		sql += "SELECT "
		sql += str(mode) + " "
		sql += str(str_column) + " "
		sql += " FROM `"
		sql += str(tableName) + "` "

		if where is not None:
			sql += " WHERE " + str(where)

		if group is not None:
			sql += " GROUP BY " + str(group)

		if having is not None:
			sql += " HAVING " + str(having)

		if order is not None:
			sql += " ORDER BY " + str(order)

		if limit is not None:
			sql += " LIMIT " + str(limit)

		self.debug_log(sql, "VIEWS")

		try:
			self.cur.execute(sql)
			return self.cur.fetchall()
		except:
			self.print_db_error(ERR_DB_IDK)
			return False


	def drop_table(self, tableName, view=False):
		"""
		This function will drop/delete a table/view
		"""
		sql = "DROP "
		if view:
			sql += "VIEW "
		else:
			sql += "TABLE "

		sql += "`"+ tableName +"`"

		try:
			self.cur.execute(sql)
		except:
			return False

	def get_table_names(self):
		'''
		This function will return a list of tables that are in the database
		'''
		# get the tables in the database
		sql = 'SHOW TABLES '
		self.cur.execute(sql)
		results = self.cur.fetchall()

		# make the sql result into a list of table names
		tables = []
		for table in results:
			tables.append(table[0])

		# remove 'users' and 'permissions' from the list of tables, since we want to keep those two
		qc_tables = set(tables) - set(['users', 'permissions', 'qc'])
		return qc_tables

	def get_column_names(self):
		'''
		This function will return a list of columns currently in the database
		'''
		sql = "SELECT column_name FROM information_schema.columns WHERE table_name='"
		sql += QC_TABLE_NAME 
		sql += "' AND table_schema='"
		sql += DB_NAME + "';"
		self.cur.execute(sql)
		results = self.cur.fetchall()
		return [x[0] for x in results]

	def clear_qc_data(self, tables_to_delete):
		''' 
		This function will drop/delete all tables in the database that are not users and/or permissions
		'''
		# delete the tables
		for table in tables_to_delete:
			self.drop_table(table, True)

	def debug_log(self,sql,mode):
		"""
		This function is used for debugging puposes ONLY
		"""
		if isinstance(DB_DEBUG_MODE,str):
			if DB_DEBUG_MODE == "ALL" or DB_DEBUG_MODE == mode:
				print "DEBUG:\t",sql

		elif isinstance (DB_DEBUG_MODE,list):
			if mode in DB_DEBUG_MODE:
				print "DEBUG:\t",sql

	def print_db_error(self,err):
		print err
