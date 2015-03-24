def ReadFile(self, filename):
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
def ReadImages(self, folder_name, end):
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
def Parse(self, unique_ID):
    folder_name = "FastQC/" + unique_ID
    R1_data_name = folder_name + "_R1/fastqc_data.txt"
    R2_data_name = folder_name + "_R2/fastqc_data.txt"
    
    return "\t".join([self.ReadFile(R1_data_name), self.ReadFile(R2_data_name), self.ReadImages(folder_name, "R1"), self.ReadImages(folder_name, "R2")])