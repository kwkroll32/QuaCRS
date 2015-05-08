from __future__ import print_function
from collections import defaultdict
import pandas as pd
import sys
import pdb

def GTF2DF(filename):
    '''
    Assembles a pandas dataframe from the input GTF file
        Each GTF line is appended to a dictionary of lists, which is finally transformed into a dataframe 
    '''

    # stores values in a dictionary until the GTF is completely read
    result = defaultdict(list)          
    columns = ['seqname', 'source', 'feature', 'start', 'end', 'score', 'strand', 'frame', 'gene_id', 'gene_name', 'gene_type', 'transcript_id', 'transcript_name', 'transcript_type']
    for line in open(filename):
        if line.startswith('#'): 
            continue
            # skip header lines

        # extract relevant line content into a per-line dictionary    
        line_dict = LineToDict(line)    
        if result.keys() and len(set(line_dict.keys()) - set(result.keys())):
            print('Error: This line is not formatted correctly')    # the master dictionary keys do not match this line
            print(line)
            sys.exit(1)
        #append this line to the master dictionary  
        for key, value in line_dict.items():
            result[key].append(value)
    # convert the completed master dictionary into pandas dataframe
    df = pd.DataFrame(result, columns=columns)
    return df.sort(['seqname','start','end'])

def LineToDict(line):
    '''
    Converts 1 GTF line into a dictionary of relevant values 
    '''

    columns = ['seqname', 'source', 'feature', 'start', 'end', 'score', 'strand', 'frame', 'gene_id', 'gene_name', 'gene_type', 'transcript_id', 'transcript_name', 'transcript_type']
    values = line.strip().strip(';').split('\t')
    out = {}

    for i in range(8): 
        out[columns[i]] = values[i]

    attr = values[-1]
    for val in attr.split('; '):
        this_name = val.split(' ')[0]
        this_value = val.split(' ')[1].strip('"')
        if this_name in columns:
            out[this_name] = this_value
    return out

def SumExonLen(grp, feature):
    '''
    Counts the exonic length of a feature by adding each exonic base location to a set.
    This avoids double-counting overlapping feature regions.
    Counts each chromosome separately because genes can have copies on many chromosomes 
    '''

    # set up a variable to hold all the contig sets
    locs = {}
    for contig in set(grp.seqname.values):
        # declare the set to hold locations for this feature on this contig
        locs[contig] = set() 
        # add every base pair position between the start and stop of every exon to the above set
        for row in grp[grp['seqname']==contig][['start', 'end']].drop_duplicates().values: 
            for position in range(int(row[0]),int(row[1])+1): # inclusive, ie: use +1 to catch the last base
                locs[contig].add(position)
    # total up the length of each contig set
    total = sum([len(locs[x]) for x in locs.keys()]) 
    # assign the feature name and count to a dictionary, then convert to a pandas Series before returning 
    out = {grp[feature].drop_duplicates().values[0]:total} 
    return pd.Series(out)

def GetFeatureSubset(df, feature, feature_type):
    '''
    Print lists of features that have a common "type" in the GTF file. 
    Ex: feature='gene_name' and feature_type='protein_coding' will print a text file of all gene names that are identified as being "protein_coding" in the gene_type field in the GTF. 
    '''
    type_col = str(feature.split('_')[0]) + "_type" # the name of the column that has 'protein_coding', 'lncrna', 'lincrna', etc.
    outDF = df[df[type_col]==feature_type][feature].drop_duplicates()
    if len(outDF) == 0:
        print("Error: could not find any GTF rows with value {0} in column {1}".format(feature_type, type_col))
        return False
    out = open( str(str(feature_type) + "_" + str(feature) + "s.txt"), 'w' )
    outDF.to_csv(out, sep='\t', index=False)
    return True

def main(fn):
    feature = 'gene_name' # can be any of: [gene_name, gene_id, transcript_name, transcript_id]
    output_file = 'exonic_gene_lengths.txt'
    print('Reading GTF Annotation: {0}'.format(fn))
    df = GTF2DF(fn)
    print('Calculating exonic length of each {0}'.format(feature))
    grps = df[df['feature']=='exon'].groupby(feature)
    lengths = grps.apply(SumExonLen, feature)
    lengthsDF = pd.DataFrame(lengths).reset_index(feature, drop=True)
    lengthsDF.columns = ['Exon_Length']
    print('Writing lengths to {0}'.format(output_file))
    out = open(output_file, 'w')
    lengthsDF.to_csv(out, sep='\t', index=True, header=False)
    interesting_gene_groups = ['protein_coding', 'lincRNA']
    for feature_type in interesting_gene_groups:
        print('Writing list of {0} {1}s'.format(feature_type, feature))
        GetFeatureSubset(df, feature, feature_type)


if __name__ == '__main__':
    main(sys.argv[1])
