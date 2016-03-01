from __future__ import print_function
import sys
import pdb # pdb.set_trace()
import pandas as pd
import os
import csv
import argparse
from collections import defaultdict
from pylab import *

class ExpressionMatrix(object):
    ''' an object for count based expression matrix '''

    def __init__(self):
        self.outdir = ""
        self.outfile = ""
        self.raw = defaultdict(list)
        self.rawDF = pd.DataFrame()
        self.normDF = pd.DataFrame()
        self.fpkmDF = pd.DataFrame()
        self.columns = []
        self.lengths = pd.DataFrame()
        self.name = "sample"

    def __str__(self):
        print("normalized DF")
        for sid in self.normDF.keys():
            print('\t' + sid)

    def readRaw(self, fn):
        lengths = {}
        for line in csv.DictReader(open(fn), delimiter='\t', fieldnames=['Geneid', 'Chr', 'Start', 'End', 'Strand', 'Length', 'Count']):
            if line['Geneid'].startswith('#') or line['Geneid'].startswith('Geneid'):
                continue
            try:
                self.columns.append(line['Geneid'])
                self.raw[self.name].append(int(line['Count']))
                lengths[line['Geneid']] = int(line['Length'])
            except:
                print("warning: ")
        self.lengths = pd.DataFrame(lengths.values(), index=lengths.keys(), columns=['lengths'])
        return True

    def rawToNorm(self):
        # normalize the raw dataframe based on the lowest per-sample read yield in the matrix
        self.normDF = self.rawDF.transpose().div( self.rawDF.transpose().sum(axis=1).div(1000000), axis=0).transpose()
        # self.rawDF.transpose().sum(axis=1).min()
        return True

    def readLengths(self, fn):
        genes = []
        lengths = []
        for line in open(fn):
            genes.append( line.split('\t')[0].strip() )
            lengths.append( int(line.split('\t')[1].strip()) )
        self.lengths = pd.DataFrame( lengths, index=genes, columns=['lengths'] )
        return True

    def frameUp(self):
        self.dropEmptyKeys()
        if self.raw:
            self.rawDF = pd.DataFrame(self.raw, index=self.columns)
            self.rawDF.index.name = 'Gene'
        return True

    def dropEmptyKeys(self):
        for key in self.raw.keys():
            if not self.raw[key]:
                self.raw.pop(key, None)
        return True

    def normalize(self):
        for gene in (set(self.normDF.index.values) - set(self.lengths.index.values)):
            print("WARNING: Gene '{0}' has unknown length! Skipping it...".format(gene))
        usable_lengths = pd.DataFrame(pd.merge(self.normDF, self.lengths, left_index=True, right_index=True)['lengths'])
        for SID in self.normDF.keys().values:
            self.fpkmDF[SID] = self.normDF[SID].div(usable_lengths['lengths'], axis='index').dropna().multiply(1000)
        self.fpkmDF.index.name = 'Gene'
        return True


    def write(self):
        out = pd.ExcelWriter("fpkm.xlsx")
        self.fpkmDF.to_excel(out, str("FPKM"), na_rep='?', index=True)
        self.normDF.to_excel(out, str("Normalized Counts"), na_rep='?', index=True)
        self.rawDF.to_excel(out, str("Raw Counts"), na_rep='?', index=True)
        self.lengths.to_excel(out, str("Gene Lengths"), na_rep='?', index=True)
        out.save()
        return True

    def printSummary(self):
        printMets(self.fpkmDF, 'Global', self.outdir, self.outfile)
        plotMets(self.fpkmDF, 'Global', self.outdir)
        return True

    def printSubset(self, fn, name):
        subGenes = set()
        for gene in open(fn):
            if gene.strip():
                subGenes.add(gene.strip())
        df = self.normDF.loc[subGenes,:].sort()
        printMets(df, name, self.outdir, self.outfile )
        plotMets(df, name, self.outdir)
        return True

def plotMets(dframe, name, outdir):
    gmax = 0
    for SID in dframe.keys().values:
        lmax = len(dframe[(dframe > 0.5) & (dframe < 1.5)][SID].dropna().values)
        if lmax > gmax:
            gmax = lmax
    for SID in dframe.keys().values:
        hg = hist(dframe[(dframe < 100.5) & (dframe > 0.5)][SID].dropna().values, bins = 100, color = 'k')
        # hg = hist( log(dframe[(dframe < 1000) & (dframe > 0)][SID].dropna().values), bins = 100, color = 'k' )
        title(str(SID) + ' ' + name + '\n Gene Expression Distribution')
        xlabel('FPKM')
        ylabel('Number of Genes')
        ylim([0,gmax*1.05])
        xlim([0,100.5])
        savefig(outdir + '/' + str(SID) + '_' + name + '.png')
        close()
        hg = 0
    return True

def printMets(dframe, name, outdir, out):
    # out = open(outdir + '/' + 'expression_qc.txt', 'a')
    ranges = [1, 10, 100]
    #print(name + '\t' + '\t'.join(map(str, ranges)))
    for SID in dframe.keys().values:
        counts = []
        for lim in ranges:
            counts.append( len(dframe[ dframe > lim ][SID].dropna()) )
        print(str(name) + '\t' + '\t'.join(map(str, counts)), sep='\t', file=out)
    return True 

def printHousekeepingGenes(dframe, outdir, fn):
    HKGenes = {}
    for line in open(fn,'r'):
        HKGenes[line.strip()] = 0
    #HKGenes = {"C1orf43":0, "CHMP2A":0, "EMC7":0, "GPI":0, "PSMB2":0, "PSMB4":0, "RAB7A":0, "REEP5":0, "SNRPD3":0, "VCP":0, "VPS29":0}
    out = open(outdir + '/' + 'housekeeping_expression.txt', 'w')
    for gene in HKGenes.keys():
        print(str(gene) + '\t' + str(dframe.loc[gene].values[0]), file=out)
    out.close()
    return True

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("-name", "--name", required=True, help="Sample name")
    parser.add_argument("-raw", "--raw_counts", required=True, help="Raw read counts per gene from HTSeq")
    parser.add_argument("-len", "--gene_lengths", required=False, help="tab-delimited text file of gene_name and total_gene_length. not needed when using subread featureCounts")
    parser.add_argument("-lnc", "--lncRNA_names", required=False, help="text file of long non-coding RNA names")
    parser.add_argument("-linc", "--lincRNA_names", required=False, help="text file of long intergenic non-coding RNA names")
    parser.add_argument("-hk", "--housekeeping_names", required=False, help="text file of housekeeping gene names")
    parser.add_argument("-coding", "--codingRNA_names", required=False, help="text file of coding RNA names")
    parser.add_argument("-other", "--other_names", required=False, help="text file of some other RNA names")
    args = parser.parse_args()

    data = ExpressionMatrix()
    data.name = str(args.name)
    output_dir = "./" # "/".join(["ExpressionQC", args.name])
    if not os.path.exists(output_dir):
        os.makedirs(output_dir)
    data.outdir = output_dir
    data.outfile = open(data.outdir + '/' + 'expression_qc.txt', 'w')

    if args.raw_counts:
        print("Reading in raw data ...")
        data.readRaw(args.raw_counts)
        print("Making it into a dataframe ...")
        data.frameUp()
        print("Normalizing the raw dataframe for read yield ...")
        data.rawToNorm()
    '''
    print("parsing gene lengths ...")
    data.readLengths(args.gene_lengths)
    '''
    print("Normalizing the read-yield-normalized data for gene length ...")
    data.normalize()
    print("Writing output ...")
    data.printSummary()

    if args.housekeeping_names:
        printHousekeepingGenes(data.fpkmDF, data.outdir, args.housekeeping_names)
    if args.lncRNA_names:
        data.printSubset(args.lncRNA_names, "lncRNA")
    if args.lincRNA_names:
        data.printSubset(args.lincRNA_names, "lincRNA")
    if args.codingRNA_names:
        data.printSubset(args.codingRNA_names, "Coding_RNA")
    if args.other_names:
        data.printSubset(args.other_names, "Genes")

def abortWithMessage(message):
    print("ERROR: {0}".format(message))
    sys.exit(1)

if __name__ == '__main__':
    main()
