<?php 
$file_dir   =   "static/page/stationcourse/"; 
$file_name   =   "fid.xls"; 

$file   =   fopen($file_dir   .   $file_name, "r ");   //   打开文件   
//   输入文件标签 
Header( "Content-type:   application/octet-stream "); 
Header( "Accept-Ranges:   bytes "); 
Header( "Accept-Length:   ".filesize($file_dir   .   $file_name)); 
Header( "Content-Disposition:   attachment;   filename= "   .   $file_name); 
//   输出文件内容 
echo   fread($file,filesize($file_dir   .   $file_name)); 
fclose($file); 
exit; 
?> 