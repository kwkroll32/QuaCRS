#Generates QC table for a sample
#Use --help for more information
import argparse
import collections
import numpy
import os
import scipy.stats
import sys


###################################################################
#### FastQC
###################################################################


#FastQC Read File
def FastQC_read_file(filename):
    out_cols = [""] * 10
    
    try:
        file_in = open(filename, 'r')
    except IOError:
        return "\t".join(out_cols)
    
    #Read through file
    for line in file_in:
        line = line.strip()
        start = line.find("\t") + 1
        
        #Check if line is of interest
        if line.find(">>Per base sequence quality") > -1:
            out_cols[0] = line[start:]
        elif line.find(">>Per sequence quality scores") > -1:
            out_cols[1] = line[start:]
        elif line.find(">>Per base sequence content") > -1:
            out_cols[2] = line[start:]
        elif line.find(">>Per base GC content") > -1:
            out_cols[3] = line[start:]
        elif line.find(">>Per sequence GC content") > -1:
            out_cols[4] = line[start:]
        elif line.find(">>Per base N content") > -1:
            out_cols[5] = line[start:]
        elif line.find(">>Sequence Length Distribution") > -1:
            out_cols[6] = line[start:]
        elif line.find(">>Sequence Duplication Levels") > -1:
            out_cols[7] = line[start:]
        elif line.find(">>Overrepresented sequences") > -1:
            out_cols[8] = line[start:]
        elif line.find(">>Kmer Content") > -1:
            out_cols[9] = line[start:]
    
    file_in.close()
    return "\t".join(out_cols)


#FastQC Images
def FastQC_images(folder_name, end):
    images = ["duplication_levels", "kmer_profiles", "per_base_gc_content", "per_base_n_content", "per_base_quality", "per_base_sequence_content", "per_sequence_gc_content", "per_sequence_quality", "sequence_length_distribution"]
    out_cols = [""] * len(images)
    
    #Gather filenames of existing images
    i = 0
    for image in images:
        filename = folder_name + "_" + end + "/Images/" + end + "_" + image + ".png"
        if os.path.isfile(filename):
            out_cols[i] = filename
        i += 1
    
    return "\t".join(out_cols)


#FastQC Main
def FastQC(unique_ID):
    folder_name = "FastQC/" + unique_ID
    R1_data_name = folder_name + "_R1/fastqc_data.txt"
    R2_data_name = folder_name + "_R2/fastqc_data.txt"
    
    return "\t".join([FastQC_read_file(R1_data_name), FastQC_read_file(R2_data_name), FastQC_images(folder_name, "R1"), FastQC_images(folder_name, "R2")])


###################################################################
#### RNASeQC
###################################################################


#RNASeQC Main
#PE is True for Paired-End, False for Single-End
def RNASeQC(unique_ID, sample_name,  PE):
    filename = "RNASeQC/" + unique_ID + "/" + sample_name + "/" + sample_name + ".metrics.txt"
    
    try:
        file_in = open(filename , 'r')
    except(IOError):        
        if PE:
            return "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t"
        else:
            return "\t\t\t\t\t\t\t\t\t\t"
    
    #Read in metrics file
    out_cols = []
    skip_line = False
    for line in file_in:
        if line.find("Mapped") > -1:
            skip_line = True
        
        if line[0].isdigit() and float(line.split('\t')[0]) != 0:
            if skip_line == False:
                line = line.strip()
                out_cols.append(line)
            skip_line = False
    file_in.close()
    
    return "\t".join(out_cols)


###################################################################
#### RSeQC
###################################################################


#RSeQC Duplication Rate
#base_name is either "sequence" or "mapping", for which duplication rate to use
def RSeQC_dupRate(folder_name, base_name):
    if base_name == "sequence":
        base = "seq"
    elif base_name == "mapping":
        base = "pos"
    else:
        return "\t\t\t"
    
    filename = folder_name + base + ".DupRate.xls"
    
    try:
        file_in = open(filename, 'r')
    except IOError:
        return "\t\t\t"
    
    x = []
    y = []
    ranges = [0] * 4
    
    #Read in file
    file_in = open(filename)
    file_in.readline()
    for line in file_in:
        line = line.strip()
        cols = line.split()
        x_val = int(cols[0])
        y_val = int(cols[1])
        x.append(x_val)
        y.append(y_val)
        
        #Add value to appropriate range
        if x_val <= 10:
            ranges[0] += y_val
        elif x_val <= 100:
            ranges[1] += y_val
        elif x_val < 1000:
            ranges[2] += y_val
        else:
            ranges[3] += y_val
    file_in.close()
    
    return "\t".join(str(value) for value in ranges)


#RSeQC GC
def RSeQC_GC(folder_name):
    filename = folder_name + "GC.xls"
    
    try:
        file_in = open(filename, 'r')
    except IOError:
        return "\t\t"
    
    gc = []
    
    #Read in file
    file_in = open(filename)
    file_in.readline()
    for line in file_in:
        line = line.strip()
        cols = line.split("\t")
        gc_percent = float(cols[0])
        read_count = int(cols[1])
        
        #Add value
        i = 0
        while i < read_count:
            gc.append(gc_percent)
            i += 1
    file_in.close()
    
    #Calculate statistics
    avg = numpy.average(gc) / 100.0
    std_dev =  numpy.std(gc) / 100.0
    skew = scipy.stats.skew(gc)
    
    return "\t".join([str(avg), str(std_dev), str(skew)])


#RSeQC Images
def RSeQC_images(folder_name):
    images = ["DupRate_plot", "GC_plot", "geneBodyCoverage", "NVC_plot", "qual.boxplot", "qual.heatmap"]
    out_cols = [""] * len(images)
    
    #Gather filenames of existing images
    i = 0
    for image in images:
        filename = folder_name + image + ".png"
        if os.path.isfile(filename):
            out_cols[i] = filename
        i += 1
    
    return "\t".join(out_cols)


#RSeQC Main
def RSeQC(unique_ID, sample_name):
    folder_name = "RSeQC/" + unique_ID + "/" + sample_name + "."
    
    return "\t".join([RSeQC_dupRate(folder_name, "sequence"), RSeQC_dupRate(folder_name, "mapping"), RSeQC_GC(folder_name), RSeQC_images(folder_name)])


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
header_RSeQC_dup_rate_sequence = "1-10 sequence dups\t11-100 sequence dups\t100-1000 sequence dups\t> 1000 sequence dups"
header_RSeQC_dup_rate_mapping = "1-10 mapping dups\t11-100 mapping dups\t100-1000 mapping dups\t> 1000 mapping dups"
header_RSeQC_GC = "GC Avg\tGC Std Dev\tGC Skew"
header_RSeQC_images = "Duplication Rate Plot Location\tGC Plot Location\tGene Body Coverage Plot Location\tNVC Plot Location\tQuality Boxplot Location\tQuality Heatmap Location"
".DupRate_plot.pdf", ".GC_plot.pdf", ".geneBodyCoverage.pdf", ".NVC_plot.pdf", ".qual.boxplot.pdf", ".qual.heatmap.pdf"
header_RSeQC = "\t".join([header_RSeQC_dup_rate_sequence, header_RSeQC_dup_rate_mapping, header_RSeQC_GC, header_RSeQC_images])

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
header = [header_info, header_FastQC, header_RNASeQC, header_RSeQC]

data_info = "\t".join([args.unique_ID, args.study, args.sample_name, args.sequencing_type, args.sequencing_date, args.run_description, RQS_str, args.contamination_rate])
data = [data_info, FastQC(args.unique_ID), RNASeQC(args.unique_ID, args.sample_name, PE), RSeQC(args.unique_ID, args.sample_name)]

#Print output
output_filename = "qc_" + args.unique_ID + ".csv"

try:
    file_out = open(output_filename, 'w')
except IOError:
    sys.exit("Fatal error: Could not create output file for " + args.unique_ID)

print >>file_out, "\t".join(header)
print >>file_out, "\t".join(data)

file_out.close()
