#IMPORTS
import sys
import os
from glob import glob


files = glob("../../Samples/*.csv")
for m_file in files:
	curFile = open(m_file,"r")
	newFile = ""
	for line in curFile:
		data = line.strip('\n').split('\t')
		if data[0] == "ID":
			data[0] = "Unique ID"
		newFile += '\t'.join(data)
		newFile += "\n"
		
	print newFile
	curFile.close()

	newCurFile = open(m_file, "w")
	newCurFile.write(newFile)
	newCurFile.close()
