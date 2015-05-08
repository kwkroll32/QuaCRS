import numpy
import os
import scipy.stats
import sys
import pdb
import numpy as np
import pandas as pd
from collections import defaultdict
import pdb # pdb.set_trace()

#import RNASeQCModule as RNASeQC

#RSeQC Duplication Rate
#base_name is either "sequence" or "mapping", for which duplication rate to use
def ReadDupeRate(folder_name, base_name):
    blank_output = "\t\t\t\t\t\t\t"

    if base_name == "sequence":
        base = "seq"
    elif base_name == "mapping":
        base = "pos"
    else:
        return blank_output

    filename = folder_name + base + ".DupRate.xls"
    
    try:
        file_in = open(filename, 'r')
    except IOError:
        return blank_output
    
    x = []
    y = []
    ranges  = [0] * 4
    ranges2 = [0] * 4
    
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
            ranges2[0] += y_val*x_val
        elif x_val <= 100:
            ranges[1] += y_val
            ranges2[1] += y_val*x_val
        elif x_val < 1000:
            ranges[2] += y_val
            ranges2[2] += y_val*x_val
        else:
            ranges[3] += y_val
            ranges2[3] += y_val*x_val
    file_in.close()
    
    return "\t".join([str(value) for value in ranges] + [str(float(x)/np.sum(ranges2)) for x in ranges2])


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
###
class Splices(object):
    ''' object to keep track of the read counts and splice sites '''
    def __init__(self):
        self.totReads = 0
        self.TRS = defaultdict(dict) # total reads dict
        self.splicedReads = 0
        self.SRS = defaultdict(dict) # spliced reads dict
        self.novReads = 0
        self.NRS = defaultdict(dict) # novel reads dict
        self.knoReads = 0
        self.KRS = defaultdict(dict) # known reads dict
        self.novReads = 0
        self.knoReads = 0
        self.partNovelReads = 0
        self.novSites = 0
        self.knoSites = 0 
        self.partNovelSites = 0
        self.SJDF = pd.DataFrame()
        self.SJD = defaultdict(bool)
        self.contigs = {}

    def __str__(self):
        try:
            spliceRate = float( self.splicedReads/float(self.totReads) )
        except:
            spliceRate = 0
        try:
            knownSpliceRate = float( self.knoReads/float(self.splicedReads) )
        except:
            knownSpliceRate = 0
        try:
            novelSpliceRate = float( self.novReads/float(self.splicedReads) )
        except:
            novelSpliceRate = 0
        try:
            novelSiteRate = float( self.knoSites/float(self.novSites + self.knoSites) )
        except:
            novelSiteRate = 0
        try:
            knownSiteRate = float( self.novSites/float(self.novSites + self.knoSites) )
        except:
            knownSiteRate = 0
        #return( "Uniquely Mapped Reads\t\t{0}\nSpliced Reads\t\t\t{1}\n% Spliced Reads\t\t\t{2:.4f}\nReads with Known Splices\t{3}\nReads with Novel Splices\t{4}\n% Known Spliced Reads\t\t{5:.4f}\n% Novel Spliced Reads\t\t{6:.4f}\n\nTotal Splice Sites\t\t{7}\nKnown Splice Sites\t\t{8}\nNovel Splice Sites\t\t{9}\n% Known Splice Sites\t\t{10:.4f}\n% Novel Splice Sites\t\t{11:.4f}".format( self.totReads, self.splicedReads, spliceRate, self.knoReads, self.novReads, knownSpliceRate, novelSpliceRate, self.novSites + self.knoSites, self.knoSites, self.novSites, novelSiteRate, knownSiteRate) )
        return( "{1}\t{2:.4f}\t{3}\t{4}\t{5:.4f}\t{6:.4f}\t{7}\t{8}\t{9}\t{10:.4f}\t{11:.4f}".format( self.totReads, self.splicedReads, spliceRate, self.knoReads, self.novReads, knownSpliceRate, novelSpliceRate, self.novSites + self.knoSites, self.knoSites, self.novSites, novelSiteRate, knownSiteRate) )

    def Load(self, SJLog):
        ''' parse the RSeQC splice junction file into PANDAS dataframe '''
        columns = {0:'chrom', 1:'intron_st(0-based)', 2:'intron_end(1-based)', 3:'read_count', 4:'annotation'}
        readingDict = {'chrom':[], 'intron_st(0-based)':[], 'intron_end(1-based)':[], 'read_count':[], 'annotation':[]}
        for line in open(SJLog):
            col = line.strip().split('\t')
            if col[4] == "annotation":
                continue
            for i in range(len(col)):
                try:
                    readingDict[columns[i]].append( int(col[i]) )
                except:
                    readingDict[columns[i]].append( col[i] )
        self.SJDF = pd.DataFrame(readingDict, columns=columns.values())

        self.knoSites = len(self.SJDF[ self.SJDF['annotation'] == 'annotated' ])
        self.novSites = len(self.SJDF[ self.SJDF['annotation'] == 'complete_novel' ])
        self.partNovelSites = len(self.SJDF[ self.SJDF['annotation'] == 'partial_novel' ])

        self.knoReads = self.SJDF[ self.SJDF['annotation'] == 'annotated' ]['read_count'].sum()
        self.novReads = self.SJDF[ self.SJDF['annotation'] == 'complete_novel' ]['read_count'].sum()
        self.partNovelReads = self.SJDF[ self.SJDF['annotation'] == 'partial_novel' ]['read_count'].sum()
        self.splicedReads = self.knoReads + self.novReads + self.partNovelReads
        return True

def ReadJunctions(folder_name):
    try:
        data = Splices()
        #print("loading STAR Splice Junction Log ...")
        data.Load(folder_name + 'junction.xls')
        data.totReads, data.splicedReads = GetRSeQCBAMStats(folder_name)
        #print("complete!")
        return str(data)
    except:
        return "\t\t\t\t\t\t\t\t\t\t"

#RSeQC Main
def Parse(unique_ID, sample_name):
    folder_name = "RSeQC/" + unique_ID + "/" + sample_name + "."
    
    return "\t".join([ReadDupeRate(folder_name, "sequence"), ReadDupeRate(folder_name, "mapping"), ReadGC(folder_name), ReadJunctions(folder_name), ReadImages(folder_name)])

def GetRSeQCBAMStats(folder_name):
    for line in open(folder_name + 'bam.stat.txt'):
        if line.strip().split(':')[0] == 'Total records':
            aligned_total = line.strip().split(' ')[-1]
        elif line.strip().split(':')[0] == 'Splice reads':
            spliced_reads = line.strip().split(' ')[-1]    
    return int(aligned_total), int(spliced_reads)