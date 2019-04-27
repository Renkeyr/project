<?php

class dbf_class {
var $dbf_num_rec;          
var $dbf_num_field;         
var $dbf_names = array();   
var $_raw;               
var $_rowsize;           
var $_hdrsize;           
var $_memos;         
   function dbf_class($filename) {
   if ( !file_exists($filename)) {
   echo 'Not a valid DBF file !!!'; exit;
   }
   $tail=substr($filename,-4);
   if (strcasecmp($tail, '.dbf')!=0) {
   echo 'Not a valid DBF file !!!'; exit;
   }		
   $handle = fopen($filename, "r");
   if (!$handle) { echo "Cannot read DBF file"; exit; }
   $filesize = filesize($filename);
   $this->_raw = fread ($handle, $filesize);
   fclose ($handle);
   if(!(ord($this->_raw[0]) == 3 || ord($this->_raw[0]) == 131) && ord($this->_raw[$filesize]) != 26) {
   echo 'Not a valid DBF file !!!'; exit;
   }
   $arrHeaderHex = array();
   for($i=0; $i<32; $i++){
   $arrHeaderHex[$i] = str_pad(dechex(ord($this->_raw[$i]) ), 2, "0", STR_PAD_LEFT);
   }
   $line = 32;
   $this->dbf_num_rec=  hexdec($arrHeaderHex[7].$arrHeaderHex[6].$arrHeaderHex[5].$arrHeaderHex[4]);
   $this->_hdrsize= hexdec($arrHeaderHex[9].$arrHeaderHex[8]);
   $this->_rowsize = hexdec($arrHeaderHex[11].$arrHeaderHex[10]);
   $this->dbf_num_field = floor(($this->_hdrsize - $line ) / $line ) ;	
   for($j=0; $j<$this->dbf_num_field; $j++){
   $name = '';
   $beg = $j*$line+$line;
   for($k=$beg; $k<$beg+11; $k++){
   if(ord($this->_raw[$k])!=0){
   $name .= $this->_raw[$k];
   }
   }
   $this->dbf_names[$j]['name']= $name;
   $this->dbf_names[$j]['len']= ord($this->_raw[$beg+16]);
   $this->dbf_names[$j]['type']= $this->_raw[$beg+11];
   }
   if (ord($this->_raw[0])==131) { 
   $tail=substr($tail,-1,1);   
   if ($tail=='F'){            
   $tail='T';              
   } else {
   $tail='t';
   }
   $memoname = substr($filename,0,strlen($filename)-1).$tail;
   $handle = fopen($memoname, "r");
   if (!$handle) { echo "Cannot read DBT file"; exit; }
   $filesize = filesize($memoname);
   $this->_memos = fread ($handle, $filesize);
   fclose ($handle);
   }
   }
   function getRow($recnum) {
   $memoeot = chr(26).chr(26);
   $rawrow = substr($this->_raw,$recnum*$this->_rowsize+$this->_hdrsize,$this->_rowsize);
   $rowrecs = array();
   $beg=1;
   if (ord($rawrow[0])==42) {
   return false;   
   }
   for ($i=0; $i<$this->dbf_num_field; $i++) {
   $col=trim(substr($rawrow,$beg,$this->dbf_names[$i]['len']));
   if ($this->dbf_names[$i]['type']!='M') {
   $rowrecs[]=$col;
    } else {
   $memobeg=$col*512;  
   $memoend=strpos($this->_memos,$memoeot,$memobeg);  
   $rowrecs[]=substr($this->_memos,$memobeg,$memoend-$memobeg);
   }
   $beg+=$this->dbf_names[$i]['len'];
   }
   return $rowrecs;
   }
   function getRowAssoc($recnum) {
   $rawrow = substr($this->_raw,$recnum*$this->_rowsize+$this->_hdrsize,$this->_rowsize);
   $rowrecs = array();
   $beg=1;
   if (ord($rawrow[0])==42) {
   return false;   
   }
   for ($i=0; $i<$this->dbf_num_field; $i++) {
   $col=trim(substr($rawrow,$beg,$this->dbf_names[$i]['len']));
   if ($this->dbf_names[$i]['type']!='M') {
   $rowrecs[$this->dbf_names[$i]['name']]=$col;
   } else {
   $memobeg=$col*512;  
   $memoend=strpos($this->_memos,$memoeot,$memobeg);   
   $rowrecs[$this->dbf_names[$i]['name']]=substr($this->_memos,$memobeg,$memoend-$memobeg);
   }
   $beg+=$this->dbf_names[$i]['len'];
   }
   return $rowrecs;
   }
}
?>
