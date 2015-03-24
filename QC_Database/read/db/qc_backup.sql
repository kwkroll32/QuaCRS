-- MySQL dump 10.13  Distrib 5.1.71, for redhat-linux-gnu (x86_64)
--
-- Host: xio29    Database: variants
-- ------------------------------------------------------
-- Server version	5.0.95-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Not dumping tablespaces as no INFORMATION_SCHEMA.FILES table on this server
--

--
-- Table structure for table `qc`
--

DROP TABLE IF EXISTS `qc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qc` (
  `qcID` int(11) NOT NULL auto_increment,
  `Sample` varchar(45) NOT NULL,
  `Aligned` int(11) default NULL,
  `Duplicates` int(11) default NULL,
  `Duplication_Rate` decimal(10,8) default NULL,
  `End_1_%_Sense` decimal(10,8) default NULL,
  `End_1_Antisense` int(11) default NULL,
  `End_1_Sense` int(11) default NULL,
  `End_2_%_Sense` decimal(10,8) default NULL,
  `End_2_Antisense` int(11) default NULL,
  `End_2_Sense` int(11) default NULL,
  `Estimated_Library_Size` int(11) default NULL,
  `Exonic_Rate` decimal(10,8) default NULL,
  `Expressed_Transcripts` int(11) default NULL,
  `Expression_Profiling_Efficiency` decimal(12,10) default NULL,
  `Intergenic_Rate` decimal(10,8) default NULL,
  `Intragenic_Rate` decimal(10,8) default NULL,
  `Intronic_Rate` decimal(10,8) default NULL,
  `RQS` decimal(3,2) default NULL,
  `Total_PF_Reads` int(11) default NULL,
  `Unique` int(11) default NULL,
  `Uniquely_Mapped_Reads_%` decimal(10,8) default NULL,
  `rRNA_&_mitoRNA_%` decimal(10,8) default NULL,
  `R2_Overrepresented_Sequences` enum('pass','fail','warn') default NULL,
  `R2_Per_Sequence_Quality_Scores` enum('pass','fail','warn') default NULL,
  `R1_Sequence_Duplication_Levels` enum('pass','fail','warn') default NULL,
  `R2_Per_Sequence_GC_Content` enum('pass','fail','warn') default NULL,
  `1-10_sequence_dups` int(11) default NULL,
  `R1_Per_Base_N_Content` enum('pass','fail','warn') default NULL,
  `GC_Std_Dev` decimal(14,10) default NULL,
  `R1_Per_Sequence_Quality_Scores` enum('pass','fail','warn') default NULL,
  `100-1000_mapping_dups` int(11) default NULL,
  `R2_Per_Base_N_Content` enum('pass','fail','warn') default NULL,
  `R2_Kmer_Content` enum('pass','fail','warn') default NULL,
  `11-100_sequence_dups` int(11) default NULL,
  `R2_Per_Base_Sequence_Content` enum('pass','fail','warn') default NULL,
  `11-100_mapping_dups` int(11) default NULL,
  `>_1000_mapping_dups` int(11) default NULL,
  `100-1000_sequence_dups` int(11) default NULL,
  `R1_Per_Base_Sequence_Content` enum('pass','fail','warn') default NULL,
  `GC_Skew` decimal(14,10) default NULL,
  `R1_Per_Base_Sequence_Quality` enum('pass','fail','warn') default NULL,
  `R1_Per_Sequence_GC_Content` enum('pass','fail','warn') default NULL,
  `R1_Sequence_Length_Distribution` enum('pass','fail','warn') default NULL,
  `R2_Per_Base_GC_Content` enum('pass','fail','warn') default NULL,
  `R2_Sequence_Length_Distribution` enum('pass','fail','warn') default NULL,
  `R2_Sequence_Duplication_Levels` enum('pass','fail','warn') default NULL,
  `R2_Per_Base_Sequence_Quality` enum('pass','fail','warn') default NULL,
  `>_1000_sequence_dups` int(11) default NULL,
  `R1_Per_Base_GC_Content` enum('pass','fail','warn') default NULL,
  `1-10_mapping_dups` int(11) default NULL,
  `GC_Avg` decimal(14,10) default NULL,
  `R1_Overrepresented_Sequences` enum('pass','fail','warn') default NULL,
  `R1_Kmer_Content` enum('pass','fail','warn') default NULL,
  `cur_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`qcID`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qc`
--

LOCK TABLES `qc` WRITE;
/*!40000 ALTER TABLE `qc` DISABLE KEYS */;
INSERT INTO `qc` VALUES (7,'THH119N',48809548,14485210,'0.29677000','2.67498230',13806927,379484,'97.32414000',379560,13805046,32500140,'0.33200333',74916,'0.2334746900','0.08804325','0.91110504','0.57910174','7.30',25465519,34324338,'83.85000000','19.54000000','warn','pass','fail','fail',29795747,'pass','338.7472147920','pass',15602,'pass','warn',201000,'fail',194166,1522,14500,'fail','8.9336764125','pass','fail','warn','fail','warn','fail','pass',1242,'fail',27674284,'52.9331713779','warn','warn','2014-04-15 21:08:10'),(8,'THH353N',57407496,14509940,'0.25275340','2.86471940',17346264,511577,'97.13644400',511397,17347367,46745531,'0.26908180',69300,'0.2010704400','0.08278733','0.91674550','0.64766365','6.30',29712693,42897556,'89.63000000','17.34000000','warn','pass','fail','warn',29795747,'pass','338.7472147920','pass',15602,'pass','warn',201000,'fail',194166,1522,14500,'fail','8.9336764125','pass','warn','warn','fail','warn','fail','pass',1242,'fail',27674284,'52.9331713779','warn','warn','2014-04-15 21:08:10'),(9,'THH119T',66761234,11368974,'0.17029305','1.91043630',23015080,448252,'98.09329000',447422,23018300,86534248,'0.28747430',71691,'0.2385194500','0.07535026','0.92414110','0.63666683','8.30',34350092,55392260,'90.79000000','7.08000000','warn','pass','warn','warn',45762417,'pass','295.0273039280','pass',14038,'pass','warn',232846,'fail',241503,1198,12564,'fail','6.7934341581','pass','warn','warn','fail','warn','warn','pass',936,'fail',43530097,'48.9493778103','warn','warn','2014-04-15 21:08:10');
/*!40000 ALTER TABLE `qc` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-04-15 21:57:59
