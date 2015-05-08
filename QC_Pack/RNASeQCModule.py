import collections
import numpy
import os
import scipy.stats
import sys

#RNASeQC Main
#PE is True for Paired-End, False for Single-End
def ParseOld(unique_ID, sample_name,  PE):
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

def Parse(unique_ID, sample_name,  PE):
    filename = "RNASeQC/" + unique_ID + "/" + sample_name + "/" + sample_name + ".metrics.txt"
    
    if PE:
        out_cols = [""] * 17
    else:
        out_cols = [""] * 11
    
    try:
        file_in = open(filename , 'r')
    except(IOError):        
        return "\t".join(out_cols)
    
    #Read in metrics file
    col_names = []
    col_vals = []
    for i, line in enumerate(file_in):
        line = line.strip()
        cols = line.split("\t")
        
        if i % 2 == 0:
            col_names.extend(cols)
        else:
            col_vals.extend(cols)
    file_in.close()
    
    #Find desired values
    for name, val in zip(col_names, col_vals):
        if name == "Total Purity Filtered Reads Sequenced" or name == "Total":
            out_cols[0] = val
        elif name == "Mapped Unique":
            out_cols[1] = val
        elif name == "Duplication Rate" or name == "Duplication Rate of Mapped":
            out_cols[3] = val
        elif name == "Estimated Library Size":
            out_cols[4] = val
        elif name == "Intragenic Rate":
            out_cols[5] = val
        elif name == "Exonic Rate":
            out_cols[6] = val
        elif name == "Intronic Rate":
            out_cols[7] = val
        elif name == "Intergenic Rate":
            out_cols[8] = val
        elif name == "Expression Profiling Efficiency":
            out_cols[9] = val
        elif name == "Expressed Transcripts" or name == "Transcripts Detected":
            out_cols[10] = val
        elif name == "End 1 Sense":
            out_cols[11] = val
        elif name == "End 1 Antisense":
            out_cols[12] = val
        elif name == "End 2 Sense":
            out_cols[13] = val
        elif name == "End 2 Antisense":
            out_cols[14] = val
        elif name == "End 1 % Sense":
            out_cols[15] = val
        elif name == "End 2 % Sense":
            out_cols[16] = val
    out_cols[2] = str(int(out_cols[0]) - int(out_cols[1]))
    
    return "\t".join(out_cols)
