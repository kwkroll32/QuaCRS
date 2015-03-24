import os
import xml.etree.ElementTree as ET

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

curDir = os.path.dirname(__file__)
tree = ET.parse(os.path.join(curDir,'qc_table_definition.xml'))
root = tree.getroot()
for col in root:
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
VIEW_GENERAL=[(QC_TABLE_NAME+"ID"),
			  (SAMPLE_ID_COLUMN),
			  (SAMPLE_COLUMN),
			  (STUDY_COLUMN),
			  (DESCRIPTION_COLUMN)]

#Alignment Stats:
ALIGNMENT_STATS_VIEW="alignment_stats"
VIEW_ALIGNMENT_STATS=[(QC_TABLE_NAME+"ID"),
					  #("Total_PF_Reads"),
					  ("Aligned"),
					  ("Unique"),
					  ("Duplicates"),
					  ("Duplication_Rate")]
#GENOMIC STATS
GENOMIC_STATS_VIEW="genomic_stats"
VIEW_GENOMIC_STATS=[(QC_TABLE_NAME+"ID"),
					("Intragenic_Rate"),
				   	("Exonic_Rate"),
				   	("Intronic_Rate"),
				   	("Intergenic_Rate"),
				   	("Expression_Profiling_Efficiency"),
				   	("Expressed_Transcripts")]
#LIBRARY STATS
LIBRARY_STATS_VIEW="library_stats"
VIEW_LIBRARY_STATS=[(QC_TABLE_NAME+"ID"),
					("Estimated_Library_Size"),
		   	   		("RQS")]

#STRAND STATS
STRAND_STATS_VIEW="strand_stats"
VIEW_STRAND_STATS=[(QC_TABLE_NAME+"ID"),
				   ("End_1_Sense"),
				   ("End_1_Antisense"),
				   ("End_2_Sense"),
				   ("End_2_Antisense"),
				   ("End_1_%_Sense"),
				   ("End_2_%_Sense")]

#FAST QC VIEWS
FAST_QC_STATS_VIEW="fastqc_stats"
VIEW_FAST_QC_STATS=[(QC_TABLE_NAME+"ID"),
					("R1_Per_Base_Sequence_Quality"),
					("R1_Per_Sequence_Quality_Scores"),
					("R1_Per_Base_Sequence_Content"),
					("R1_Per_Base_GC_Content"),
					("R1_Per_Sequence_GC_Content"),
					("R1_Per_Base_N_Content"),
					("R1_Sequence_Length_Distribution"),
					("R1_Sequence_Duplication_Levels"),
					("R1_Overrepresented_Sequences"),
					("R1_Kmer_Content"),
					("R2_Per_Base_Sequence_Quality"),
					("R2_Per_Sequence_Quality_Scores"),
					("R2_Per_Base_Sequence_Content"),
					("R2_Per_Base_GC_Content"),
					("R2_Per_Sequence_GC_Content"),
					("R2_Per_Base_N_Content"),
					("R2_Sequence_Length_Distribution"),
					("R2_Sequence_Duplication_Levels"),
					("R2_Overrepresented_Sequences"),
					("R2_Kmer_Content")]

#RSeQC VIEWS
#GC CONTENT
GC_CONTENT_VIEW="GC_content"
VIEW_GC_CONTENT=[(QC_TABLE_NAME+"ID"),
				 ("GC_Avg"),
				 ("GC_Std_Dev"),
				 ("GC_Skew")]


#SEQUENCE DUPLICATES
SEQUENCE_DUPLICATES_VIEW="sequence_duplicates"
VIEW_SEQUENCE_DUPLICATES=[(QC_TABLE_NAME+"ID"),
						  ("1-10_sequence_dups"),
					      ("11-100_sequence_dups"),
					      ("100-1000_sequence_dups"),
					      (">_1000_sequence_dups")]


#MAPPING DUPLICATES
MAPPING_DUPLICATES_VIEW="mapping_duplicates"
VIEW_MAPPING_DUPLICATES=[(QC_TABLE_NAME+"ID"),
						 ("1-10_mapping_dups"),
					     ("11-100_mapping_dups"),
					     ("100-1000_mapping_dups"),
					     (">_1000_mapping_dups")]
