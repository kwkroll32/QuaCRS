import os
import xml.etree.ElementTree as ET
import pdb

#CONSTANTS
INPUT_FILE=None
INPUT_FILE_PATH=None
DELIMITER=","
QC_TABLE_DEFINITION="qc_table_definition.xml"

'''
# SMAPLE PREPIX TO IDENTIFY THE TYPE OF THE STUDY
TOTAL_TRANSCRIPTOME_PREFIX = "T"
POLY_A_PREFIX = "R"
SMALL_RNA_PREFIX = "S"
METHYLATION_PREFIX = "M"
'''

#DATABASE TABLE NAMES
QC_TABLE_NAME="qc"

#FOLDER NAMES FOR TOOLS
FASTQC_FOLDER_NAME = "FastQC"
RNASEQC_FOLDER_NAME = "RNASeQC"
RSEQC_FOLDER_NAME = "RSeQC"

#COLUMN POSTFIX
IMAGE_COLUMN_POSTFIX = "Location"

#REQUIRED QC_TABLE COLUMNS:
SAMPLE_ID_COLUMN = "Unique_ID"
SAMPLE_COLUMN = "Sample"
STUDY_COLUMN = "Study"
COMBINED_FLAG_COLUMN = "Shown"
DESCRIPTION_COLUMN = "Run_Description"

QC_COLUMNS=[]
VIEWS = {}
VIEWS['General'] = [(QC_TABLE_NAME+"ID"),(SAMPLE_ID_COLUMN),(SAMPLE_COLUMN),(STUDY_COLUMN),(DESCRIPTION_COLUMN)]

curDir = os.path.dirname(__file__)
tree = ET.parse(os.path.join(curDir,QC_TABLE_DEFINITION))
root = tree.getroot()
for view in root:
	try:
		view_name = view.attrib['name'][0].lower() + view.attrib['name'][1:]
	except:
		view_name = ""
	try:
		VIEWS[view_name]
	except:
		VIEWS[view_name] = [(QC_TABLE_NAME+"ID")]
	for col in view.getchildren():
		VIEWS[view_name].append((col.find('field').text))
		for i,c in enumerate(col):
			if c.text is None:
				col[i].text = ""
		
		data = col[1].text+ " "+ str(col[2].text)+ " " + str(col[3].text) + " " + str(col[4].text) + " "+ str(col[5].text)
		data = " ".join(data.split())
		m_tuple = col[0].text, data
		QC_COLUMNS.append(m_tuple)
	
QC_COLUMNS_DICT={}
for i,col in enumerate(QC_COLUMNS):
	QC_COLUMNS_DICT[col[0]]=i	
