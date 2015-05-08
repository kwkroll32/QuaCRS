#Generates QC table for a sample
#Use --help for more information
import argparse
import collections
import numpy
import os
import scipy.stats
import sys

# import QC reading modules
import FastQCModule as FastQC
import RNASeQCModule as RNASeQC
import RSeQCModule as RSeQC
import ExpressionQCModule as ExpressionQC


###################################################################
#### Main Method
###################################################################


#Headers
header_info = "Unique ID\tStudy\tSample\tSequencing type\tSequencing date\tRun Description\tRQS\trRNA & mitoRNA %"

#FastQC
header_FastQC_R1 = "R1 Per Base Sequence Quality\tR1 Per Sequence Quality Scores\tR1 Per Base Sequence Content\tR1 Per Base GC Content\tR1 Per Sequence GC Content\tR1 Per Base N Content\tR1 Sequence Length Distribution\tR1 Sequence Duplication Levels\tR1 Overrepresented Sequences\tR1 Kmer Content"
header_FastQC_R2 = "R2 Per Base Sequence Quality\tR2 Per Sequence Quality Scores\tR2 Per Base Sequence Content\tR2 Per Base GC Content\tR2 Per Sequence GC Content\tR2 Per Base N Content\tR2 Sequence Length Distribution\tR2 Sequence Duplication Levels\tR2 Overrepresented Sequences\tR2 Kmer Content"
header_FastQC_R1_images = "R1 Duplication Levels Graph Location\tR1 Kmer Profiles Graph Location\tR1 Per Base GC Content Graph Location\tR1 Per Base N Content Graph Location\tR1 Per Base Quality Graph Location\tR1 Per Base Sequence Content Graph Location\tR1 Per Sequence GC Content Graph Location\tR1 Per Sequence Quality Graph Location\tR1 Sequence Length Distribution Graph Location"
header_FastQC_R2_images = "R2 Duplication Levels Graph Location\tR2 Kmer Profiles Graph Location\tR2 Per Base GC Content Graph Location\tR2 Per Base N Content Graph Location\tR2 Per Base Quality Graph Location\tR2 Per Base Sequence Content Graph Location\tR2 Per Sequence GC Content Graph Location\tR2 Per Sequence Quality Graph Location\tR2 Sequence Length Distribution Graph Location"
header_FastQC = "\t".join([header_FastQC_R1, header_FastQC_R2, header_FastQC_R1_images, header_FastQC_R2_images])

#RNASeQC
header_RNASeQC_PE = "Aligned\tUnique\tDuplicates\tDuplication Rate\tEstimated Library Size\tIntragenic Rate\tExonic Rate\tIntronic Rate\tIntergenic Rate\tExpression Profiling Efficiency\tExpressed Transcripts\tEnd 1 Sense\tEnd 1 Antisense\tEnd 2 Sense\tEnd 2 Antisense\tEnd 1 % Sense\tEnd 2 % Sense"
header_RNASeQC_SE = "Aligned\tUnique\tDuplicates\tDuplication Rate\tEstimated Library Size\tIntragenic Rate\tExonic Rate\tIntronic Rate\tIntergenic Rate\tExpression Profiling Efficiency\tExpressed Transcripts"

#RSeQC
header_RSeQC_dup_rate_sequence = "1-10 sequence dups\t11-100 sequence dups\t100-1000 sequence dups\t> 1000 sequence dups\t%1-10 sequence dups\t%11-100 sequence dups\t%100-1000 sequence dups\t%>1000 sequence dups"
header_RSeQC_dup_rate_mapping = "1-10 mapping dups\t11-100 mapping dups\t100-1000 mapping dups\t> 1000 mapping dups\t%1-10 mapping dups\t%11-100 mapping dups\t%100-1000 mapping dups\t%>1000 mapping dups"
header_RSeQC_GC = "GC Avg\tGC Std Dev\tGC Skew"
header_RSeQC_Junctions = "Spliced Reads\t% Spliced Reads\tReads with Known Splices\tReads with Novel Splices\t% Known Spliced Reads\t% Novel Spliced Reads\tTotal Splice Sites\tKnown Splice Sites\tNovel Splice Sites\t% Known Splice Sites\t% Novel Splice Sites"
header_RSeQC_images = "Duplication Rate Plot Location\tGC Plot Location\tGene Body Coverage Plot Location\tNVC Plot Location\tQuality Boxplot Location\tQuality Heatmap Location"
".DupRate_plot.pdf", ".GC_plot.pdf", ".geneBodyCoverage.pdf", ".NVC_plot.pdf", ".qual.boxplot.pdf", ".qual.heatmap.pdf"
header_RSeQC = "\t".join([header_RSeQC_dup_rate_sequence, header_RSeQC_dup_rate_mapping, header_RSeQC_GC, header_RSeQC_Junctions, header_RSeQC_images])

#Expression QC
header_global = "Global Genes >1 FPKM\tGlobal Genes >10 FPKM\tGlobal Genes >100 FPKM"
header_coding = "Coding Genes >1 FPKM\tCoding Genes >10 FPKM\tCoding Genes >100 FPKM"
header_lincRNA = "lincRNA Genes >1 FPKM\tlincRNA Genes >10 FPKM\tlincRNA Genes >100 FPKM"
header_housekeeping="C1orf43\tCHMP2A\tEMC7\tGPI\tPSMB2\tPSMB4\tRAB7A\tREEP5\tSNRPD3\tVCP\tVPS29"
header_expression_images = "Global FPKM Graph Location\tCoding FPKM Graph Location\tlincRNA FPKM Graph Location"
header_ExpressionQC = "\t".join([header_global, header_coding, header_lincRNA, header_housekeeping, header_expression_images])

#Parse arguments
parser = argparse.ArgumentParser()
parser.add_argument("unique_ID", type = str, help = "Unique ID")
parser.add_argument("study", type = str, help ="Study Name")
parser.add_argument("sample_name", type = str, help = "Sample Name")
parser.add_argument("PE_str", type = str, choices = ["yes", "no"], help = "Is Paired-End")
parser.add_argument("-st", "--sequencing_type", type = str, default = "", help = "Sequencing Type")
parser.add_argument("-sd", "--sequencing_date", type = str, default = "", help = "Sequencing Date")
parser.add_argument("-rd", "--run_description", type = str, default = "", help = "Run Description")
parser.add_argument("-rqs", "--rqs", type = float, help = "RQS")
parser.add_argument("-cr", "--contamination_rate", type = str, default = "", help = "rRNA & mitoRNA %")
args = parser.parse_args()

PE = args.PE_str == "yes"

if args.rqs:
    RQS_str = str(args.rqs)
else:
    RQS_str = ""

#Determine which RNASeQC header to use
if PE:
    header_RNASeQC = header_RNASeQC_PE
else:
    header_RNASeQC = header_RNASeQC_SE

#Generate header and data
header = [header_info, header_FastQC, header_RNASeQC, header_RSeQC, header_ExpressionQC]

data_info = "\t".join([args.unique_ID, args.study, args.sample_name, args.sequencing_type, args.sequencing_date, args.run_description, RQS_str, args.contamination_rate])
data = [data_info, FastQC.Parse(args.unique_ID), RNASeQC.Parse(args.unique_ID, args.sample_name, PE), RSeQC.Parse(args.unique_ID, args.sample_name), ExpressionQC.Parse(args.unique_ID)]

#Print output
output_filename = "qc_" + args.unique_ID + ".csv"

try:
    file_out = open(output_filename, 'w')
except IOError:
    sys.exit("Fatal error: Could not create output file for " + args.unique_ID)

print >>file_out, "\t".join(header)
print >>file_out, "\t".join(data)

file_out.close()
