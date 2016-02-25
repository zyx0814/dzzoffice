<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
$exts=array(
'aca','acf','acm','aif','aif','aifc','aiff','ani','ans','arc','arj','asf','asp','aspx','asx','au','avi','bak','bas','bat','bbs','bfc','bin','bmp','c','cab','cal','cdf','cdr','cdx','cfg','chm','clp','cmd','cmf','cnf','cnt','col','com','cpl','cpp','crd','crt','cur','css','dat','dat','dbf','dcx','ddi','dev','dib','dir','dll','doc','docx','dos','dot','drv','dwg','dxb','dxf','der','dic','emf','eps','err','exe','exp','exc','flc','fnd','fon','for','fot','fp','fpt','frt','frx','fxp','gif','grh','grp','goc','gra','h','hlp','hqx','ht','htm','html','icm','ico','idf','idx','iff','image','ime','img','inc','inf','ini','jar','jpeg','jpg','lnk','log','lzh','mac','mag','mdb','men','mid','mif','mov','movie','mp3','mpg','mpt','msg','obj','ovl','pcd','pcs','pcx','pdf','psd','ppt','pptx','pwl','qt','qtm','rec','reg','rle','rm','rmi','rtf','sav','scp','scr','sct','scx','set','shb','snd','sql','svg','svx','swf','swg','sys','tbk','tga','tiff','tmp','txt','url','vcd','ver','voc','vxd','wab','wav','win','wmf','wpc','wps','wri','xab','xbm','xls','xlsx','zip','png','mp4','dzzdoc','php','js');
$exts_all=array(
//----A
'A','AAM','AAS','ABF','ABK','ABS','ACE','ACL','ACM','ACP','ACR','ACT','ACV','AD','ADA','ADB','ADD','ADF','ADI','ADM','ADP','ADR','ADS','AFM','AF2',
'AF3','AI','AIF','AIFF','AIFC','AIM','AIS','AKW','ALAW','ALB','ALL','AMS','ANC','ANI','ANS','ANT','API','APR','APS','ARC','ARI','ARJ','ART','ASA',
'ASC','ASD','ASE','ASF','ASM','ASO','ASP','AST','ASV','ASX','ATT','ATW','AU','AVB','AVI','AVR','AVS','AWD','AWR','Axx','A3L','A4L','A5L','A3M','A4M','A4P','A3W','A4W','A5W',
//----B
'BAK','BAS','BAT','BDF','BFC','BG','BGL','BI','BIF','BIFF','BIN','BKS','BMK','BMP','BMI','BOOK','BOX','BPL','BQY','BRX','BSC','BSP','BS1','BS_','BTM','BUD','BUN','BW','BWV','BYU','B4',
//------C
'C','C0l','CAB','CAD','CAL','CAM','CAP','CAS','CAT','CB','CBI','CC','CCA','CCB','CCF','CCH','CCM','CCO','CCT','CDA','CDF','CDI','CDM','CDR','CDT','CDX','CEL','CER','CFB','CFG','CFM','CGI','CGM','CH','CHK','CHM','CHR','CHP','CHT','CIF','CIL','CIM','CIN','CK1','CK2','CK3','CK4','CK5','CK6','CLASS','CLL','CLP','CLS','CMD','CMF','CMG','CMP','CMV','CMX','CNF','CNM','CNQ','CNT','COB','COD','COM','CPD','CPD','CPE','CPI','CPL','CPO','CPP','CPR','CPT','CPX','CRD','CRP','CRT','CSC','CSP','CSS','CST','CSV','CT','CTL','CUE','CUR','CUT','CV','CWK','CWS','CXT','CXX',
////-------'D',
'DAT','DB','DBC','DBF','DBX','DCM','DCR','DCS','DCT','DCU','DCX','DC5','DDF','DDIF','DEF','DEFI','DEM','DER','DEWF','DGN','DIB','DIC','DIF','DIG','DIR','DIZ','DLG','DLL','DLS','DMD','DMF','DOC','DOT','DPL','DPR','DRAW','DRV','DRW','DSF','DSG','DSM','DSP','DSQ','DST','DSW','DTA','DTD','DTED','DTF','DTM','DUN','DV','DWD','DWG','DXF','DXR','D64',
////-------'E',
'EDA','EDD','EDE','EDK','EDQ','EDS','EDV','EFA','EFE','EFK','EFQ','EFS','EFV','EMD','EMF','EML','ENC','ENFF','EPHTML','EPS','EPSF','ERI','ERR','EPX','ESPS','EUI','EVY','EWL','EXC','EXE',
////-------'F',
'F','F2R','F3R','F77','F90','FAR','FAV','FAX','FBK','FCD','FDB','FDF','FEM','FFA','FFL','FFO','FFK','FFF','FFT','FH3','FIF','FIG','FITS','FLA','FLC','FLF','FLI','FLT','FM','FMB','FML','FMT','FMX','FND','FNG','FNK','FOG','FON','FOR','FOT','FP','FP1','FP3','FPT','FPX','FRM','FRT','FRX','FSF','FSL','FSM','FT','FTG','FTS','FW2','FW3','FW4','FXP','FZB','FZF','FZV',
////-------'G',
'G721','G723','GAL','GCD','GCP','GDB','GDM','GED','GEM','GEN','GetRight','GFC','GFI','GFX','GHO','GID','GIF','GIM','GIX','GKH','GKS','GL','GNA','GNT','GNX','GRA','GRD','GRF','GRP','GSM','GTK','GT2','GWX','GWZ','GZ',
////-------'H',
'H','HCM','HCOM','HCR','HDF','HED','HEL','HEX','HGL','HH','HLP','HOG','HPJ','HPP','HQX','HST','HT','HTM','HTML','HTT','HTX','HXM',
////-------'I',
'ICA','ICB','ICC','ICL','ICM','ICO','IDB','IDD','IDF','IDQ','IDX','IFF','IGES','IGF','IIF','ILBM','IMA','IMG','IMZ','INC','INF','INI','INP','INRS','INS','INT','IOF','IQY','ISO','ISP','IST','ISU','IT','ITI','ITS','IV','IVD','IVP','IVT','IVX','IW','IWC',
////-------'J',
'J62','JAR','JAVA','JBF','JFF','JFIF','JIF','JMP','JN1','JPE','JPEG','JPG','JS','JSP','JTF',
////-------'K',
'K25','KAR','KDC','KEY','KFX','KIZ','KKW','KMP','KQP','KR1','KRZ','KSF','KYE',
//-------'L',
'LAB','LBM','LBT','LBX','LDB','LDL','LEG','LES','LFT','LGO','LHA','LIB','LIN','LIS','LLX','LNK','LOG','LPD','LRC','LSL','LSP','LST','LU','LVL','LWLO','LWOB','LWP','LWSC','LYR','LZH','LZS',
//-------'M'
'M1V','M3D','M3U','MAC','MAD','MAF','MAG','MAGIC','MAK','MAM','MAN','MAP','MAQ','MAR','MAS','MAT','MAUD','MAX','MAZ','MB1','MBOX','MBX','MCC','MCP','MCR','MCW','MDA','MDB','MDE','MDL','MDN','MDW','MDZ','MED','MER','MET','MFG','MGF','MHTM','MHTML','MI','MIC','MID','MIF','MIFF','MIM','MIME','MME','MLI','MMF','MMG','MMM','MMP','MN2','MND','MNI','MNG','MNT','MNX','MNU','MOD','MOV','MP2','MP3','MP4','MPA','MPE','MPEG','MPG','MPP','MPR','MRI','MSA','MSDL','MSG','MSI','MSN','MSP','MST','MTM','MUL','MUS','MUS10','MVB','MWP',
//-------'N',
'NAN','NAP','NCB','NCD','NCF','NDO','netCDF','NFF','NFT','NIL','NIST','NLB','NLM','NLS','NLU','NOD','NSF','NSO','NST','NS2','NTF','NTX','NWC','NWS',
//-------'O',
'O01','OBD','OBJ','OBZ','OCX','ODS','OFF','OFN','OFT','OKT','OLB','OLE','OOGL','OPL','OPO','OPT','OPX','ORA','ORC','ORG','OR2','OR3','OSS','OST','OTL','OUT',
//-------'P',
'P3','P10','P65','P7C','PAB','PAC','PAK','PAL','PART','PAS','PAT','PBD','PBF','PBK','PBL','PBM','PBR','PCD','PCE','PCL','PCM','PCP','PCS','PCT','PCX','PDB','PDD','PDF','PDP','PDQ','PDS','PF','PFA','PFB','PFC','PFM','PGD','PGL','PGM','PGP','PH','PHP','PHP3','PHTML','PIC','PICT','PIF','PIG','PIN','PIX','PJ','PJX','PJT','PKG','PKR','PL','PLG','PLI','PLM','PLS','PLT','PM5','PM6','PNG','PNT','PNTG','POG','POL','POP','POT','POV','PP4','PPA','PPF','PPM','PPP','PPS','PPT','PQI','PRC','PRE','PRF','PRG','PRJ','PRN','PRP','PRS','PRT','PRV','PRZ','PS','PSB','PSD','PSI','PSM','PSP','PST','PTD','PTM','PUB','PWD','PWL','PWP','PWZ','PXL','PY','PYC',
//-------'Q',
'QAD','QBW','QDT','QD3D','QFL','QIC','QIF','QLB','QM','QRY','QST','QT','QTM','QTI','QTIF','QTP','QTS','QTX','QW','QXD',
//-------'R',
'RA','RAM','RAR','RAS','RAW','RBH','RDF','RDL','REC','REG','REP','RES','RFT','RGB','SGI','RLE','RL2','RM','RMD','RMF','RMI','ROM','ROV','RPM', 
'RPT','RRS','RSL','RSM','RTF','RTK','RTM','RTS','RUL','RVP','Rxx',
//-------'S',
'S','S3I','S3M','SAM','SAV','SB','SBK','SBL','SC2','SC3','SCC','SCD','SCF','SCH','SCI','SCN','SCP','SCR','SCT','SCT01','SCV','SCX','SD','SD2','SDF','SDK','SDL','SDR','SDS','SDT','SDV','SDW','SDX','SEA','SEP','SES','SF','SF2','SFD','SFI','SFR','SFW','SFX','SGML','SHB','SHG','SHP','SHS','SHTML','SHW','SIG','SIT','SIZ','SKA','SKL','SL','SLB','SLD','SLK','SM3','SMP','SND','SNDR','SNDT','SOU','SPD','SPL','SPPACK','SPRITE','SQC','SQL','SQR','SSDO1','SSD','SSF','ST','STL','STM','STR','STY','SVX','SW','SWA','SWF','SWP','SYS','SYW',
//-------'T',
'T64','TAB','TAR','TAZ','TBK','TCL','TDB','TDDD','TEX','TGA','TGZ','THEME','THN','TIF','TIFF','TIG','TLB','TLE','TMP','TOC','TOL','TOS','TPL','TPP','TRK','TRM','TRN','TTF','TTK','TWF','TWW','TX8','TXB','TXT','TXW','TZ','T2T',
//-------'U',
'UB','UDF','UDW','ULAW','ULT','UNI','URL','USE','UU','UUE','UW','UWF',
//-------'V',
'V8','VAP','VBA','VBP','VBW','VBX','VCE','VCF','VCT','VCX','VDA','VI','VIFF','VIR','VIV','VIZ','VLB','VMF','VOC','VOX','VP','VQE','VQL','VQF','VRF','VRML','VSD','VSL','VSN','VSS','VST','VSW','VXD',
//-------'W',
'W3L','WAB','WAD','WAL','WAV','WB1','WB2','WBK','WBL','WBR','WBT','WCM','WDB','WDG','WEB','WFB','WFD','WFM','WFN','WFP','WGP','WID','WIL','WIZ','WK1','WK3','WK4','WKS','WLD','WLF','WLL','WMF','WOW','WP','WP4','WP5','WP6','WPD','WPF','WPG','WPS','WPT','WPW','WQ1','WQ2','WR1','WRG','WR1','WRK','WRL','WRZ','WS1','WS2','WS3','WS4','WS5','WS6','WS7','WSD','WVL','WWL',
//-------'X',
'X','XAR','XBM','XI','XIF','XLA','XLB','XLC','XLD','XLK','XLL','XLM','XLS','XLT','XLV','XLW','XM','XNK','XPM','XR1','XTP','XWD','XWF','XY3','XY4','XYP','XYW','X16','X32',
//-------'Y',
'YAL','YBK',
//-------'Z',
'Z','ZAP','ZIP','ZOO',
//-------其他 -------
'999','12M','123','2D','2GR','3GR','3D','3DM','3DS','386','4GE','4GL','669',
 
 );
?>
