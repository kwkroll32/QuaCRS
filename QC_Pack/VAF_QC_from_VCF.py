#Max Westphal
#Repurposed by Karl Kroll
#Calculate variant allele frequency at each vcf location using the respective sample's bam file
#use the -h option for execution instructions
from __future__ import print_function
from collections import defaultdict
import argparse
import pysam
from time import gmtime, strftime
import matplotlib as mp 
mp.use('Agg')
from matplotlib.pyplot import hist, title, xlabel, ylabel, savefig
import pandas as pd
import os
import pdb

def ReadVCF(in_vcf, name):
	variant_data = defaultdict(lambda: defaultdict(list)) 
	with open(in_vcf, 'r') as readfile:
		for lines in readfile:
			if lines[0] == "#": continue
			cols = lines.strip().split("\t")
			if len(cols[3]) != len(cols[4]): continue
			loc = cols[0] + ":" + cols[1]
			total = 0
			alt_tot = 0
			variant_frequency = 0
			variant_data[loc]['ref'] = cols[3]
			variant_data[loc]['alt'] = cols[4]
			variant_data[loc]['id'] = cols[2]
	readfile.close()
	return variant_data

def BamLookup(variant_data, samples, bams):
	outdata = defaultdict(list)
	process_count = 0
	for variant in variant_data.keys():
		process_count += 1
		if process_count % 1000 == 0: 
			print( '{1} | Processed {0} variants '.format( str(process_count), strftime("%H:%M:%S")) )
		chrom = variant.split(":")[0]
		pos = int(variant.split(":")[1])
		for i, sample in enumerate(samples):
			bam = bams[i]
			samfile = pysam.Samfile(bam, 'rb')
			if variant_data[variant][sample] == []:
				ref = variant_data[variant]['ref']
				alt = variant_data[variant]['alt']
				reads = []
				for alignedread in samfile.fetch(chrom, pos -1, pos): #pysam fetches with standard coordinates
					try:
						index = alignedread.positions.index(pos - 1) #pysam lists with python 0-based coordinates
						reads.append(alignedread.query[index])
					except(ValueError):
						continue
				reads = ''.join(reads)		
				reads = reads.lower()
				dp = len(reads)
				if dp == 0: variant_data[variant][sample] = [0., 0.]
				else:
					alt = variant_data[variant]['alt'].lower()
					ref = variant_data[variant]['ref'].lower()
					vf = float(reads.count(alt)) / float(dp)
					alt_count = reads.count(alt)
					ref_count = reads.count(ref)
					variant_data[variant][sample] = [dp, vf]
					outdata[sample].append(vf)
	return outdata


def RateInRange(data, fmin, fmax):
    hits = 0
    for frequency in data:
    	if frequency > fmin and frequency <=fmax:
    		hits += 1
    rate = float(hits)/float(len(data))
    return rate
        
def MakeHist(data):
    for uniqueID in data.keys():
        output_dir = "/".join(["VariantQC", uniqueID])
        if not os.path.exists(output_dir):
            os.makedirs(output_dir)
        h = hist(sorted(data[uniqueID]), bins = 100, color = 'k')
        title(uniqueID + '\nAllele Frequency Distribution')
        xlabel('Frequency')
        ylabel('Count')
        savefig(str(output_dir + '/' + uniqueID + '_AF_dist' + '.png'))

def MakeTable(data):
    ranges = [(0.0, 0.09), (0.10, 0.39), (0.40, 0.59), (0.60, 0.89), (0.90, 1.0)]
    for uniqueID in data.keys():
        output_dir = "/".join(["VariantQC", uniqueID])
        out = open(output_dir + '/' + 'variant_qc.txt', 'w')
        print(uniqueID + "\nVF\t\tPercent", file=out)
        for i in ranges:
            print("{0:.2f} to {1:.2f}:\t{2:.2f}%".format(i[0], i[1], RateInRange(sorted(data[uniqueID]), i[0], i[1])*100 ), file=out)
        out.close()

def main():
	parser = argparse.ArgumentParser()
	parser.add_argument('-vcf', action='store', dest='vcf', required=True, help='sample vcf')
	parser.add_argument('-bam', action='store', dest='bam', required=True, help='sample bam')
	parser.add_argument('-name', action='store', dest='name', required=True, help='name for sample 1')

	args = parser.parse_args()

	sample_vcf = args.vcf
	sample_bam = args.bam
	name = args.name

	samples = [name]
	vcfs = [sample_vcf]
	bams = [sample_bam]
	variant_data = ReadVCF(sample_vcf, name)

	outdata = BamLookup(variant_data, samples, bams)
	MakeHist(outdata)
	MakeTable(outdata)

if __name__ == "__main__":
	main()