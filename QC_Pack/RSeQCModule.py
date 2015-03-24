#RSeQC Duplication Rate
#base_name is either "sequence" or "mapping", for which duplication rate to use
def ReadDupeRate(folder_name, base_name):
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
def ReadGC(folder_name):
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
def ReadImages(folder_name):
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
def Parse(unique_ID, sample_name):
    folder_name = "RSeQC/" + unique_ID + "/" + sample_name + "."
    
    return "\t".join([ReadDupeRate(folder_name, "sequence"), ReadDupeRate(folder_name, "mapping"), ReadGC(folder_name), ReadImages(folder_name)])
