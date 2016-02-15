import os
import pdb

def ReadFeatureQC(uniqueID, feature_type):
    fn = 'ExpressionQC/' + uniqueID + '/expression_qc.txt'
    if os.path.exists(fn):
        for line in open(fn, 'r'):
            col = line.strip().split('\t')
            if col[0] == feature_type:
                return "\t".join(col[1:])
    return "\t"*2

def ReadHousekeepingQC(uniqueID):
    try:
        exp = {}
        ordered = []
        for line in open('ExpressionQC/' + uniqueID + '/housekeeping_expression.txt','r'):
            col = line.strip().split('\t')
            exp[col[0]] = col[1]
        for gene in ["C1orf43", "CHMP2A", "EMC7", "GPI", "PSMB2", "PSMB4", "RAB7A", "REEP5", "SNRPD3", "VCP", "VPS29"]:
            ordered.append(str(exp[gene]))
        return '\t'.join(ordered)
    except:
        return '\t'*10

def GetExpressionPlots(uniqueID, feature_type):
    if os.path.exists('ExpressionQC' + '/' + uniqueID + '/' + uniqueID + '_' + feature_type + '.png'):
        return str('ExpressionQC' + '/' + uniqueID + '/' + uniqueID + '_' + feature_type + '.png')
    return ""

def Parse(uniqueID):
    out_data = []
    out_locs = []
    for feature_type in ["Global", "Coding RNA", "lincRNA"]:
        out_data.append( ReadFeatureQC(uniqueID, feature_type) )
        out_locs.append( GetExpressionPlots(uniqueID, feature_type) )
    out_data.append(ReadHousekeepingQC(uniqueID))
    return "\t".join( out_data + out_locs )