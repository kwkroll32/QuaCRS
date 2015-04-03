import os
import xml.etree.ElementTree as ET
import pdb

#CONSTANTS
INPUT_FILE=None
INPUT_FILE_PATH=None
DELIMITER=","
QC_TABLE_DEFINITION="qc_table_definition.xml"

# SMAPLE PREPIX TO IDENTIFY THE TYPE OF THE STUDY
TOTAL_TRANSCRIPTOME_PREFIX = "T"
POLY_A_PREFIX = "R"
SMALL_RNA_PREFIX = "S"
METHYLATION_PREFIX = "M"

#DATABASE TABLE NAMES
QC_TABLE_NAME="qc"


#FOLDER NAMES FOR TOOLS
FASTQC_FOLDER_NAME = "FastQC"
RNASEQC_FOLDER_NAME = "RNASeQC"
RSEQC_FOLDER_NAME = "RSeQC"

#COLUMN POSTFIX
IMAGE_COLUMN_POSTFIX = "Location"



#QC_TABLE COLUMNS:
SAMPLE_ID_COLUMN = "Unique_ID"
SAMPLE_COLUMN = "Sample"
STUDY_COLUMN = "Study"
COMBINED_FLAG_COLUMN = "Shown"
DESCRIPTION_COLUMN = "Run_Description"

QC_COLUMNS=[]
VIEWS = {}
VIEWS['general'] = [(QC_TABLE_NAME+"ID"),(SAMPLE_ID_COLUMN),(SAMPLE_COLUMN),(STUDY_COLUMN),(DESCRIPTION_COLUMN)]

curDir = os.path.dirname(__file__)
tree = ET.parse(os.path.join(curDir,'karl_qc_table_definition.xml'))
root = tree.getroot()
for col in root:
	try:
		VIEWS[col.attrib['block']]
	except:
		VIEWS[col.attrib['block']] = [('qcID')]
	VIEWS[col.attrib['block']].append((col.find('field').text))
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

#VIEWS:
#General Fields
GENERAL_VIEW="general"
VIEW_GENERAL=VIEWS['general']
#Alignment Stats:
ALIGNMENT_STATS_VIEW="alignment_stats"
VIEW_ALIGNMENT_STATS=VIEWS['alignment_stats']
#GENOMIC STATS
GENOMIC_STATS_VIEW="genomic_stats"
VIEW_GENOMIC_STATS=VIEWS['genomic_stats']
#LIBRARY STATS
LIBRARY_STATS_VIEW="library_stats"
VIEW_LIBRARY_STATS=VIEWS['library_stats']
#STRAND STATS
STRAND_STATS_VIEW="strand_stats"
VIEW_STRAND_STATS=VIEWS['strand_stats']
#FAST QC VIEWS
FAST_QC_STATS_VIEW="fastqc_stats"
VIEW_FAST_QC_STATS=VIEWS['fastqc_stats']
#RSeQC VIEWS
#GC CONTENT
GC_CONTENT_VIEW="GC_content"
VIEW_GC_CONTENT=VIEWS['GC_content']
#SEQUENCE DUPLICATES
SEQUENCE_DUPLICATES_VIEW="sequence_duplicates"
VIEW_SEQUENCE_DUPLICATES=VIEWS['sequence_duplicates']
#MAPPING DUPLICATES
MAPPING_DUPLICATES_VIEW="mapping_duplicates"
VIEW_MAPPING_DUPLICATES=VIEWS['mapping_duplicates']
