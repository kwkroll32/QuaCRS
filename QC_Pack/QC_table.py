#Generates QC table for a sample
#Use --help for more information
import argparse
import collections
import numpy
import os
import scipy.stats
import sys
import pdb

# import QC reading modules
import FastQCModule as FastQC
import RNASeQCModule as RNASeQC
import RSeQCModule as RSeQC
import ExpressionQCModule as ExpressionQC
import VariantQCModule as VariantQC


###################################################################
#### Main Method
###################################################################


#Headers
header_info = ["Unique ID", "Study", "Sample", "Sequencing type", "Sequencing date", "Run Description", "RQS", "rRNA & mitoRNA %"]

#FastQC
header_FastQC_R1 = ["R1 Per Base Sequence Quality", "R1 Per Sequence Quality Scores", "R1 Per Base Sequence Content", "R1 Per Base GC Content", "R1 Per Sequence GC Content", "R1 Per Base N Content", "R1 Sequence Length Distribution", "R1 Sequence Duplication Levels", "R1 Overrepresented Sequences", "R1 Kmer Content"]
header_FastQC_R2 = ["R2 Per Base Sequence Quality", "R2 Per Sequence Quality Scores", "R2 Per Base Sequence Content", "R2 Per Base GC Content", "R2 Per Sequence GC Content", "R2 Per Base N Content", "R2 Sequence Length Distribution", "R2 Sequence Duplication Levels", "R2 Overrepresented Sequences", "R2 Kmer Content"]
header_FastQC_R1_images = ["R1 Duplication Levels Graph Location", "R1 Kmer Profiles Graph Location", "R1 Per Base GC Content Graph Location", "R1 Per Base N Content Graph Location", "R1 Per Base Quality Graph Location", "R1 Per Base Sequence Content Graph Location", "R1 Per Sequence GC Content Graph Location", "R1 Per Sequence Quality Graph Location", "R1 Sequence Length Distribution Graph Location"]
header_FastQC_R2_images = ["R2 Duplication Levels Graph Location", "R2 Kmer Profiles Graph Location", "R2 Per Base GC Content Graph Location", "R2 Per Base N Content Graph Location", "R2 Per Base Quality Graph Location", "R2 Per Base Sequence Content Graph Location", "R2 Per Sequence GC Content Graph Location", "R2 Per Sequence Quality Graph Location", "R2 Sequence Length Distribution Graph Location"]
header_FastQC = header_FastQC_R1 + header_FastQC_R2 + header_FastQC_R1_images + header_FastQC_R2_images

#RNASeQC
header_RNASeQC_PE = ["Aligned", "Unique", "Duplicates", "Duplication Rate", "Estimated Library Size", "Intragenic Rate", "Exonic Rate", "Intronic Rate", "Intergenic Rate", "Expression Profiling Efficiency", "Expressed Transcripts", "End 1 Sense", "End 1 Antisense", "End 2 Sense", "End 2 Antisense", "End 1 % Sense", "End 2 % Sense"]
header_RNASeQC_SE = ["Aligned", "Unique", "Duplicates", "Duplication Rate", "Estimated Library Size", "Intragenic Rate", "Exonic Rate", "Intronic Rate", "Intergenic Rate", "Expression Profiling Efficiency", "Expressed Transcripts"]

#RSeQC
header_RSeQC_dup_rate_sequence = ["1-10 sequence dups", "11-100 sequence dups", "100-1000 sequence dups", "> 1000 sequence dups", "%1-10 sequence dups", "%11-100 sequence dups", "%100-1000 sequence dups", "%>1000 sequence dups"]
header_RSeQC_dup_rate_mapping = ["1-10 mapping dups", "11-100 mapping dups", "100-1000 mapping dups", "> 1000 mapping dups", "%1-10 mapping dups", "%11-100 mapping dups", "%100-1000 mapping dups", "%>1000 mapping dups"]
header_RSeQC_GC = ["GC Avg", "GC Std Dev", "GC Skew"]
header_RSeQC_Junctions = ["Spliced Reads", "% Spliced Reads", "Reads with Known Splices", "Reads with Novel Splices", "% Known Spliced Reads", "% Novel Spliced Reads", "Total Splice Sites", "Known Splice Sites", "Novel Splice Sites", "% Known Splice Sites", "% Novel Splice Sites"]
header_RSeQC_images = ["Duplication Rate Plot Location", "GC Plot Location", "Gene Body Coverage Plot Location", "NVC Plot Location", "Quality Boxplot Location", "Quality Heatmap Location"]
header_RSeQC = header_RSeQC_dup_rate_sequence + header_RSeQC_dup_rate_mapping + header_RSeQC_GC + header_RSeQC_Junctions + header_RSeQC_images

#Expression QC
header_global = ["Global Genes >1 FPKM", "Global Genes >10 FPKM", "Global Genes >100 FPKM"]
header_coding = ["Coding Genes >1 FPKM", "Coding Genes >10 FPKM", "Coding Genes >100 FPKM"]
header_lincRNA = ["lincRNA Genes >1 FPKM", "lincRNA Genes >10 FPKM", "lincRNA Genes >100 FPKM"]
header_housekeeping = ["C1orf43", "CHMP2A", "EMC7", "GPI", "PSMB2", "PSMB4", "RAB7A", "REEP5", "SNRPD3", "VCP", "VPS29"]
header_expression_images = ["Global FPKM Graph Location", "Coding FPKM Graph Location", "lincRNA FPKM Graph Location"]
header_ExpressionQC = header_global + header_coding + header_lincRNA + header_housekeeping + header_expression_images

#Variant QC
header_bins = ["VAF from 0.00 to 0.09", "VAF from 0.10 to 0.39", "VAF from 0.40 to 0.59", "VAF from 0.60 to 0.89", "VAF from 0.90 to 1.00"]
header_VAF_image = ["Variant Frequency Histogram Location"]
header_VariantQC = header_bins + header_VAF_image

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
header = header_info + header_FastQC + header_RNASeQC + header_RSeQC + header_ExpressionQC + header_VariantQC

data_info = [args.unique_ID, args.study, args.sample_name, args.sequencing_type, args.sequencing_date, args.run_description, RQS_str, args.contamination_rate]
data = data_info + FastQC.Parse(args.unique_ID).split('\t') + RNASeQC.Parse(args.unique_ID, args.sample_name, PE).split('\t') + RSeQC.Parse(args.unique_ID, args.sample_name).split('\t') + ExpressionQC.Parse(args.unique_ID).split('\t') + VariantQC.Parse(args.unique_ID).split('\t')

#Print output
output_filename = "qc_" + args.unique_ID + ".csv"

try:
    file_out = open(output_filename, 'w')
except IOError:
    sys.exit("Fatal error: Could not create output file for " + args.unique_ID)

results = dict(zip(header,data))
header_out = []
data_out = []
for metric in header:
    if results[metric]:
        header_out.append(metric)
        data_out.append(results[metric])

print >>file_out, "\t".join(header_out)
print >>file_out, "\t".join(data_out)

file_out.close()
