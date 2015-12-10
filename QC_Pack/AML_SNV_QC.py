from __future__ import print_function
import argparse
import math
import os
import sys
from pylab import *
import csv
import pdb
import pandas as pd
from collections import defaultdict


def RANrateGE(data, fmin, fmax):
    dat = pd.DataFrame(data, columns=['vf'])
    return float(float(len( dat['vf'][ (dat['vf'] >= fmin) & (dat['vf'] <= fmax) ]))/float(len(dat)))

def make_data(VF_output):
    data = defaultdict(list)
    for line in csv.DictReader(open(VF_output), delimiter='\t'):
        data['sample'].append(float(line['samp1 variant freq.']))
    return data
        
def make_hist(data):
    #pdb.set_trace()
    for samp in data.keys():
        h = hist(sorted(data[samp]), bins = 100, color = 'k')
        title(samp + '\nAllele Frequency Distribution')
        xlabel('Frequency')
        ylabel('Count')
        show()

def make_table(data):
    ranges = [(0.0, 0.09), (0.10, 0.39), (0.40, 0.59), (0.60, 0.89), (0.90, 1.0)]
    for samp in data.keys():
        print(samp + "\nVF\t\tPercent")
        for i in ranges:
            print("{0:.2f} to {1:.2f}:\t{2:.2f}%".format(i[0], i[1], RANrateGE(sorted(data[samp]), i[0], i[1])*100 ))
        print()

data = make_data(sys.argv[1])
make_hist(data)
make_table(data)
