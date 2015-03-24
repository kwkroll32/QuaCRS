#RNASeQC Main
#PE is True for Paired-End, False for Single-End
def Parse(unique_ID, sample_name,  PE):
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