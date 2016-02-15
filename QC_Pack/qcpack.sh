#!/bin/bash

SCRIPTS=$(dirname $0)
CWD=`pwd -P`
while read line; do if [ "$line" != "" ]; then export $line; fi ; done < ${SCRIPTS}/tools.cfg
[ ! -e "$FASTQC_EXEC" ] && echo "FastQC directory $FASTQC_EXEC not found" && exit 1
[ ! -e "$PICARD_JAR" ] && echo "Picard tools directory $PICARD_JAR not found" && exit 1
[ ! -e "$RNASEQC_JAR" ] && echo "RNA-SeQC jar file $RNASEQC_JAR not found" && exit 1
[ ! -e "$SAMTOOLS_EXEC" ] && echo "Samtools directory $SAMTOOLS_EXEC not found" && exit 1
[ ! -d "$RSEQC_DIR" ] && echo "RSeQC directory $RSEQC_DIR not found" && exit 1
[ ! -e "$WGF" ] && echo "Whole genome fasta file not found" && exit 1
[ ! -e "$ANNOT" ] && echo "Genome annotation gtf file not found" && exit 1
[ ! -e "$ANNOT_BED" ] && echo "Genome annotation bed file not found" && exit 1

if [ "$2" == "force" ]; then
	keep_temp=no
else
	keep_temp=yes
fi
input=$1
#### How are your variables and requirements?
[ ! -f "$input" ] && echo "input configuration required" && exit 1 

while read line; do if [ "$line" != "" ]; then export $line; fi ; done < $input
[ ! -e "$BAM_FILE" ] || [ ${BAM_FILE##*.} != "bam" ] && echo "bam file not found" && exit 1
count=0
for fastq in `echo ${FASTQ_FILE//,/ }`;
do
        count=`expr $count + 1`
	[ ! -f "$fastq" ] && echo "fastq file not found" && exit 1
done
[ $count == '0' ] && echo "No Fastq Files given" && exit 1
[ $count == '1' ] && PE=no
[ $count == '2' ] && PE=yes
[ $count -gt 2 ] && echo "Too many fastq files provided."  && exit 1

[ ${#FCN} -eq 0 ] && FCN="None"
[ ${#LN} -eq 0 ] && LN="None"
[ ${#INDEX} -eq 0 ] && INDEX="None"
[ ${#UNIQUE_ID} -eq 0 ] && echo "UNIQUE_ID required in input.cfg!" && exit 1
[ ${#threads} -eq 0 ] && threads=1
#[ ${#DATE} -gt 0 ] && UNIQUE_ID=${DATE}_L00${LN}_${SID}_${STUDY}
#[ ${#DATE} -le 0 ] && UNIQUE_ID=${SID}_${STUDY}
echo $UNIQUE_ID

################################################################### 
#### FastQC
################################################################### 
echo 
echo " ==== FastQC ==== "
echo
for fastq in `echo ${FASTQ_FILE//,/ }`;
do
	processed_fastq=${fastq##*/}
	IFS='_' read -a name_parts <<< "${processed_fastq}"
	for i in "${name_parts[@]}"; do [ ${i%%.*} == R1 ] && end=${i%%.*} && export $end ; [ ${i%%.*} == R2 ] && end=${i%%.*} && export $end ; done
	[ ${#end} -eq 0 ] && echo "no end found. rename fastq file with R1.fastq.gz and/or R2.fastq.gz" && echo $end && exit 
	[ ! -d "FastQC" ] && mkdir FastQC

	if [ -f "FastQC/${UNIQUE_ID}_${end}/fastqc_report.html" ] && [ $keep_temp == "yes" ]; then
		echo "FastQC output found for sample "${UNIQUE_ID}" "${end}
		echo "    Skipping this step ... "
	else
		if [ ${fastq##*.} == "gz" ]; then
			
			zcat $fastq > ${processed_fastq%.gz*}
			${FASTQC_EXEC} -o FastQC -f fastq ${processed_fastq%.gz*}
			rm ${processed_fastq%.gz*}
		elif [ ${fastq##*.} == "fastq" ]; then
			${FASTQC_EXEC} -o FastQC -f fastq ${fastq}
		fi
		[ ! -d "FastQC/${processed_fastq%.fastq*}_fastqc" ] && unzip "FastQC/${processed_fastq%.fastq*}_fastqc.zip" -d FastQC/
		mv "FastQC/${processed_fastq%.fastq*}_fastqc" "FastQC/${UNIQUE_ID}_${end}"
		for image in FastQC/${UNIQUE_ID}_${end}/Images/*.png; do
			img_path=${image%/*}
			mv $image ${img_path}/${end}_${image##*/}
		done
	fi
done


################################################################### 
#### RNA-SeQC
################################################################### 
echo 
echo " ==== RNA-SeQC ==== "
echo
[ ! -d RNASeQC ] && mkdir RNASeQC 
[ ! -d RNASeQC/${UNIQUE_ID} ] && mkdir RNASeQC/${UNIQUE_ID}
#cd RNASeQC
#[ ! -d ${UNIQUE_ID} ] && mkdir  ${UNIQUE_ID} 
#cd ${UNIQUE_ID}
#[ -d RNASeQC/${UNIQUE_ID} ] && cd RNASeQC/${UNIQUE_ID}
out_dir=`pwd -P`/RNASeQC/${UNIQUE_ID}
pwd
if [ -f $out_dir/${SID}/${SID}.metrics.txt ] && [ $keep_temp == "yes" ]; then 
	echo "RNASeQC has already been run for "$BAM_FILE
	echo "     Skipping this step ..."
else
	BAM=${BAM_FILE##*/}
	base_BAM=${BAM%%.bam*}
	out_BAM="${base_BAM}_grpd.bam"

	BAMNAME=${BAM##*/}
	#creating arguments variable to easily pass to the picard tools
	args="INPUT=${BAM_FILE} OUTPUT=${out_BAM} RGID=FLOWCELL${FCN}.LANE${LN} RGLB=library_$SID RGPL=Illumina RGPU=${INDEX} RGSM=$SID  VALIDATION_STRINGENCY=LENIENT"
	if [ -f $out_BAM ] || [ -f temp/$UNIQUE_ID/RNASeQC/$out_BAM ] && [ $keep_temp == "yes" ]; then
		echo "Found Add/Replace Read Groups output"
		echo "    Skipping this step ... "
	elif [ ! -f $out_BAM ] && [ ! -f temp/$UNIQUE_ID/RNASeQC/$out_BAM ] ||  [ $keep_temp == "no" ]; then	
		echo " running Picard.AddOrReplaceReadGroups.jar" 

		echo " arguments= $args"
		java   -Xmx6g  -jar ${PICARD_JAR} AddOrReplaceReadGroups $args
		echo " finished Picard.AddOrReplaceReadGroups.jar"
	fi
	if [ ! -f $out_BAM ] && [ ! -f temp/$UNIQUE_ID/RNASeQC/$out_BAM ]; then
		echo "Add/Replace Read Groups may have failed"
		echo "    exiting  "
		exit 1
	elif [ ! -f $out_BAM ] && [ -f temp/$UNIQUE_ID/RNASeQC/$out_BAM ]; then
		BAM="temp/$UNIQUE_ID/RNASeQC/$out_BAM"
	elif [ -f "$out_BAM" ]; then
		BAM=$out_BAM
	fi
	#----------------------
	#This step reorders the bam file passed to this script
	#  in the form of 'picard.SamReorder_single.job.sh bamfile.bam'.
	#The bam file is given to the ReorderSam.jar function 
	#  from the picard tools. 

	base_BAM=${BAM%%.bam*}
	out_BAM="${base_BAM##*/}_reorded"

	if [ -f "$out_BAM".bam ] || [ -f temp/$UNIQUE_ID/RNASeQC/${out_BAM}.bam ] && [ $keep_temp == "yes" ]; then
		echo "Found SamReorder output"
		echo "    Skipping this step ... "
	elif [ ! -f "$out_BAM".bam ] && [ ! -f temp/$UNIQUE_ID/RNASeQC/${out_BAM}.bam ] || [ $keep_temp == "no" ]; then
		echo "running samtools sort" 
		${SAMTOOLS_EXEC} sort $BAM $out_BAM
		echo "finished samtools sort"
	fi
	if [ ! -f "$out_BAM".bam ] && [ ! -f temp/$UNIQUE_ID/RNASeQC/${out_BAM}.bam ]; then
		echo "SamReorder may have failed"
		echo "    exiting  "
		exit 1
	elif [ ! -f "$out_BAM".bam ] && [ -f temp/$UNIQUE_ID/RNASeQC/${out_BAM}.bam ]; then
		BAM="temp/$UNIQUE_ID/RNASeQC/$out_BAM".bam
	elif [ -f "$out_BAM".bam ]; then
		BAM=$out_BAM.bam
	fi
	#----------------------
	#picard.MarkDuplicates.job.sh
	base_BAM=${BAM%%.bam*}
	out_BAM=${base_BAM##*/}_marked.bam
	out_metrics=$out_dir/${base_BAM}_metrics.txt
	args="INPUT=${BAM} OUTPUT=${out_BAM} ASSUME_SORTED=True OPTICAL_DUPLICATE_PIXEL_DISTANCE=100 MAX_FILE_HANDLES_FOR_READ_ENDS_MAP=8000 SORTING_COLLECTION_SIZE_RATIO=0.25 METRICS_FILE=$out_metrics VALIDATION_STRINGENCY=LENIENT  CREATE_INDEX=true"

	if [ -f "$out_BAM" ] || [ -f "temp/$UNIQUE_ID/RNASeQC/$out_BAM" ] && [ $keep_temp == "yes" ]; then
		echo "Found Mark Duplicates output"
		echo "    Skipping this step ... "
	elif [ ! -f "$out_BAM" ] && [ ! -f "temp/$UNIQUE_ID/RNASeQC/$out_BAM" ] || [ $keep_temp == "no" ]; then
		echo " running Picard.MarkDuplicates"
		echo " arguments= $args"
		java -Xmx6g -jar ${PICARD_JAR} MarkDuplicates $args
		echo " finished Picard.MarkDuplicates"
	fi
	if [ ! -f "$out_BAM" ] && [ ! -f "temp/$UNIQUE_ID/RNASeQC/$out_BAM" ]; then
		echo "Mark Duplicates may have failed"
		echo "    exiting  "
		exit 1
	elif [ ! -f "$out_BAM" ] && [ -f "temp/$UNIQUE_ID/RNASeQC/$out_BAM" ]; then
		BAM="temp/$UNIQUE_ID/RNASeQC/$out_BAM"
	elif [ -f "$out_BAM" ]; then
		BAM=$out_BAM
	fi

	#Creates an index for the bam file given as an argument
	# in the form of 'indexbam.job.sh bamfile.bam'.
	#Passes the bam file straight to the samtools index function.

	if [ -f "$BAM".bai ] || [ -f "temp/$UNIQUE_ID/RNASeQC/$out_BAM".bai ] && [ $keep_temp == "yes" ]; then
		echo "Found BAM Index "
		echo "    Skipping this step ... "
	elif [ ! -f "$BAM".bai ] && [ ! -f "temp/$UNIQUE_ID/RNASeQC/$out_BAM".bai ] || [ $keep_temp == "no" ]; then
		echo " starting samtools index"

		${SAMTOOLS_EXEC} index ${BAM}
		echo " finished samtools index"
	fi
	if [ ! -f "$BAM".bai ] && [ ! -f "temp/$UNIQUE_ID/RNASeQC/$out_BAM".bai ]; then
		echo "Samtools index may have failed"
		echo "    exiting  "
		exit 1
	fi

	#----------------------
	#QC_StudyFiles.job.sh
	#This is the RNA-SeQc function. The executable creates a QC directory in 
	#  the working directory, makes a text file for input to the RNA-SeQc, 
	#  and performs the QC. 
	#It takes in a properly formatted, reordered bam file with an appropriate index 
	#  as an argument in the form of 'QC_StudyFiles.job.sh bamfile.bam'.
	#Note, the bam file is the input. The appropriate index must simply be in the 
	#  same directory as the input bam file. 

	if [ -f $out_dir/${SID}/${SID}.metrics.txt ] && [ $keep_temp == "yes" ]; then
		echo "Found RNASeQC output"
		echo "    Skipping this step ... "
	elif [ ! -f $out_dir"/meanCoverage_high.png" ] || [ $keep_temp == "no" ]; then
		echo -e "Sample ID\tBam File\tNotes" > rnaSeQC_samples_list.txt
		echo -e "$SID\t$BAM\tNo Note" >>rnaSeQC_samples_list.txt
		
		args='-s ./rnaSeQC_samples_list.txt -t '$ANNOT' -r '$WGF' -e 50 -n 1000 -o '$out_dir/


		echo " Running	RNA-SeQC" 
		echo " arguments: $args"
		java -Xmx6g -jar ${RNASEQC_JAR} $args
		if [ -f rnaSeQC_samples_list.txt ]; then
			\rm rnaSeQC_samples_list.txt
		fi
		echo " finished RNA-SeQC"
	fi
	if [ ! -f $out_dir"/meanCoverage_high.png" ]; then
		echo "RNA-SeQC may have failed"
		echo "    Continuing to RSeQC ... "
	fi
fi
#This line will interrogate the RNA-SeQC output to determine the strandedness of the data 
#  for use with Subread featureCounts. 0 is non-stranded, 1 is read1 stranded, 2 is reverse stranded
featureCountsStrand=$(tail -1 $out_dir/${SID}/${SID}.metrics.txt | awk '{if ($(NF-1)>75) print 1; else if ($NF>75) print 2; else print 0}')
cd $CWD
################################################################### 
#### RSeQC
################################################################### 
echo
echo " ==== RSeQC ==== "
echo

[ ! -d "RSeQC" ] && mkdir RSeQC
[ ! -d "RSeQC/"$UNIQUE_ID ] && mkdir RSeQC/$UNIQUE_ID

output=RSeQC/$UNIQUE_ID
if [ -f $output/${SID}.bam.stat.txt ] && [ $keep_temp == "yes" ]; then
	echo "BAM stats already present for "$UNIQUE_ID
	echo "    Skipping this step ... "
else
	echo "Running BAM stats for "$UNIQUE_ID
	python $RSEQC_DIR/bam_stat.py -i $BAM_FILE &> $output/${SID}.bam.stat.txt
fi
if [ -f $output/${SID}.geneBodyCoverage.curves.pdf ] && [ $keep_temp == "yes" ]; then
	echo "Gene body coverage already present for "$UNIQUE_ID
	echo "    Skipping this step ... "
else
	echo "Running gene body coverage for "$UNIQUE_ID
	[ ! -f ${BAM_FILE}.bai ] && ${SAMTOOLS_EXEC} index $BAM_FILE
	python $RSEQC_DIR/geneBody_coverage.py -r $ANNOT_BED -i $BAM_FILE -o $output/${SID}
fi

#if [ -f $output/${SID}.read.distribution.txt ] && [ $keep_temp == "yes" ]; then
#	echo "Read distribution already present for "$UNIQUE_ID
#	echo "    Skipping this step ... "
#else
#	echo "Running read distribution for "$UNIQUE_ID
#	python $RSEQC_DIR/read_distribution.py -r $ANNOT_BED -i $BAM_FILE &> $output/${SID}.read.distribution.txt
#fi

if [ -f $output/${SID}.DupRate_plot.pdf ] && [ $keep_temp == "yes" ]; then
	echo "Read duplication already present for "$UNIQUE_ID
	echo "    Skipping this step ... "
else
	echo "Running read duplication for "$UNIQUE_ID
	python $RSEQC_DIR/read_duplication.py -i $BAM_FILE -o $output/${SID}
fi
if [ -f $output/${SID}.GC_plot.pdf ] && [ $keep_temp == "yes" ]; then
	echo "Read GC already present for "$UNIQUE_ID
	echo "    Skipping this step ... "
else
	echo "Running read GC for "$UNIQUE_ID
	python $RSEQC_DIR/read_GC.py -i $BAM_FILE -o $output/${SID}
fi
if [ -f $output/${SID}.NVC_plot.pdf ] && [ $keep_temp == "yes" ]; then
	echo "Read NVC already present for "$UNIQUE_ID
	echo "    Skipping this step ... "
else
	echo "Running read NVC for "$UNIQUE_ID
	python $RSEQC_DIR/read_NVC.py -i $BAM_FILE -o $output/${SID}
fi
if [ -f $output/${SID}.qual.boxplot.pdf ] && [ -f $output/${SID}.qual.heatmap.pdf ] && [ $keep_temp == "yes" ]; then
	echo "Read quality already present for "$UNIQUE_ID
	echo "    Skipping this step ... "
else
	echo "Running read quality for "$UNIQUE_ID
	python $RSEQC_DIR/read_quality.py -i $BAM_FILE -o $output/${SID}
fi
if [ -f $output/${SID}.splice_events.pdf ] && [ $keep_temp == "yes" ]; then
	echo "Junction annotation already present for "$UNIQUE_ID
	echo "    Skipping this step ... "
else
	echo "Running junction annotation for "$UNIQUE_ID
	python $RSEQC_DIR/junction_annotation.py -i $BAM_FILE -r $ANNOT_BED -o $output/${SID} &> $output/${SID}.junction_annotation.txt
fi

for i in RSeQC/${UNIQUE_ID}/*.pdf 
do
    if [ ! -f ${i%.pdf*}.png ]; then
	echo "Converting $i to png"
	convert $i ${i%.pdf*}.png
    elif [ -f ${i%.pdf*}.png ]; then
	echo "Found ${i%.pdf*}.png"
    else 
	echo "terrible error"
    fi
done

################################################################### 
#### ExpressionQC
################################################################### 
echo
echo " ==== Expression QC ==== "
echo

[ ! -d "ExpressionQC" ] && mkdir ExpressionQC
[ ! -d "ExpressionQC/"$UNIQUE_ID ] && mkdir ExpressionQC/$UNIQUE_ID

output=ExpressionQC/$UNIQUE_ID
if [ -f ExpressionQC/$UNIQUE_ID/$UNIQUE_ID.subCounts.txt ] && [ -f ExpressionQC/$UNIQUE_ID/expression_qc.txt ] && [ $keep_temp == "yes" ]; then
	echo "Expression analysis already present for "$UNIQUE_ID
	echo "    Skipping this step ... "
else
	if [ ! -f ExpressionQC/$UNIQUE_ID/$UNIQUE_ID.subCounts.txt ] ; then
		echo "Running expression analysis for "$UNIQUE_ID
		$SUBREAD/featureCounts -s $featureCountsStrand -g gene_name -T $threads -R $( [ $PE '==' "yes" ] && echo "-p") -a $ANNOT -o ExpressionQC/$UNIQUE_ID/$UNIQUE_ID.subCounts.txt $BAM_FILE
		for trash in ExpressionQC/$UNIQUE_ID/expression_qc.txt ExpressionQC/$UNIQUE_ID/housekeeping_expression.txt; do [ -f $trash ] && rm $trash ; done
		[ -f ${BAM_FILE}.featureCounts ] && mv ${BAM_FILE}.featureCounts ExpressionQC/$UNIQUE_ID/
	fi
	if [ -f ExpressionQC/$UNIQUE_ID/$UNIQUE_ID.subCounts.txt ] && [ ! -f ExpressionQC/$UNIQUE_ID/expression_qc.txt ]; then
		python $SCRIPTS/RawCounts_to_FPKM.py -name $UNIQUE_ID -raw ExpressionQC/$UNIQUE_ID/$UNIQUE_ID.subCounts.txt $( [ ${#lncRNA_genes} -gt 0 ] && echo " -lnc "$lncRNA_genes ) $( [ ${#lincRNA_genes} -gt 0 ] && echo " -linc "$lincRNA_genes ) $( [ ${#coding_genes} -gt 0 ] && echo " -coding "$coding_genes ) $( [ ${#other_genes} -gt 0 ] && echo " -other "$other_genes )
	fi
fi
if [ ! -f ExpressionQC/$UNIQUE_ID/$UNIQUE_ID.subCounts.txt ]; then
	echo "Expression analysis may have failed"
	echo "    Continuing anyway ... "
fi

################################################################### 
#### VariantQC
################################################################### 
echo
echo " ==== Variant QC ==== "
echo
if [ ${#VCF} -eq 0 ]; then
	echo "VCF file not found. skipping ..." 
else
	if [ -f VariantQC/$UNIQUE_ID/${UNIQUE_ID}_AF_dist.png ] && [ -f VariantQC/$UNIQUE_ID/variant_qc.txt ]; then
		echo "Variant QC already present for "$UNIQUE_ID
		echo "    Skipping this step ... "
	else
		python $SCRIPTS/VAF_QC_from_VCF.py -vcf $VCF -bam $BAM_FILE -name $UNIQUE_ID
	fi
	if [ ! -f VariantQC/$UNIQUE_ID/${UNIQUE_ID}_AF_dist.png ] || [ ! -f VariantQC/$UNIQUE_ID/variant_qc.txt ]; then
		echo "Variant QC may have failed"
		echo "    Continuing anyway ... "
	fi
fi

################################################################### 
#### Temporary Files
################################################################### 

if [ "$keep_temp" == "yes" ]; then 
	echo
	echo "Keeping intermediate files"
	[ ! -d temp ] && mkdir temp
	[ ! -d temp/$UNIQUE_ID ] && mkdir temp/$UNIQUE_ID
	[ ! -d temp/$UNIQUE_ID/RNASeQC ] && mkdir temp/$UNIQUE_ID/RNASeQC
	#[ ! -d temp/$UNIQUE_ID/FastQC ] && mkdir temp/$UNIQUE_ID/FastQC
	[ `\ls | grep ${BAM_FILE%.bam*}_grpd | grep bam | wc -l` -gt 0 ] && mv ${BAM_FILE%.bam*}*_grpd*.ba* temp/$UNIQUE_ID/RNASeQC/
	
elif [ "$keep_temp" == "no" ]; then
	[ `\ls | grep ${BAM_FILE%.bam*}_grpd | grep bam | wc -l` -gt 0 ] && rm ${BAM_FILE%.bam*}*_grpd*.bam*
	[ `\ls | grep fastq | grep "$UNIQUE_ID" | wc -l` -gt 0 ] && rm *$UNIQUE_ID*.fastq
	[ `\ls RSeQC/${SID}/*.pdf | wc -l` -gt 0 ] && rm RSeQC/${SID}/*.pdf
fi

################################################################### 
#### Table
################################################################### 

echo
echo " ==== TABLE ==== "
echo

args=$UNIQUE_ID' '$STUDY' '$SID' '$PE

[ ${#SEQUENCING_TYPE} -gt 0 ] && args+=' -st '$SEQUENCING_TYPE
[ ${#RQS} -gt 0 ] && args+=' -rqs '$RQS
[ ${#RUN_DESCRIPTION} -gt 0 ] && args+=' -rd '${RUN_DESCRIPTION} 
[ ${#CONTAMINATION} -gt 0 ] && args+=' -cr '${CONTAMINATION}
[ ${#DATE} -gt 0 ] && args+=' -sd '${DATE}

python $SCRIPTS/QC_table.py $args



