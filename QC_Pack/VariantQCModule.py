import os
import pdb # pdb.set_trace()

def ReadVFRates(uniqueID):
    saved = {}
    ordered = []
    fn = 'VariantQC/' + uniqueID + '/variant_qc.txt'
    if os.path.exists(fn):
        for line in open(fn, 'r'):
            try:
                col = line.strip().split('\t')
                bin = col[0].strip(':').replace(' ','_')
                percent = str(float(col[1].strip('%')))
                saved[bin] = percent
            except:
                continue
    for key in sorted(saved.keys()):
        ordered.append(saved[key])
    if ordered:
        return "\t".join(ordered)
    return '\t'*4

def GetVFPlot(uniqueID):
    if os.path.exists('VariantQC/' + uniqueID + '/' + uniqueID + '_AF_dist.png'):
        return str('VariantQC/' + uniqueID + '/' + uniqueID + '_AF_dist.png')
    return ""

def Parse(uniqueID):
    return "\t".join([ReadVFRates(uniqueID), GetVFPlot(uniqueID)])
