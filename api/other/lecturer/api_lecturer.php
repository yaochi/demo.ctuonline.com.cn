<?php
	require dirname(dirname(dirname(dirname(__FILE__)))).'/source/class/class_core.php';
	$discuz = & discuz_core::instance();
	$discuz->init1();
	global $_G;
	$action=$_G['gp_ac'];
	if($action){
		$action();
	}
        
        function getLecturers(){
            $key=$_GET['key'];
            $date=date("Ymd",time());
            $mykey=md5("api_lecturer_"."$date");
            
            if($mykey!=$key)
            {
                    $arraydata=array("message"=>"Key error");           
            }
            else
            {
                    $page=$_GET['page'];
                    $per=$_GET['per'];
                    $lecturers=getLecturerInfo($page,$per);                    
                    $length=getLength();                    
                    $arraydata=array("message"=>"SUCCESS","length"=>$length,"lecturers"=>$lecturers); 
            }
            $jsondata = json_encode ($arraydata);
            echo $jsondata;
            exit();
        }
        function getLength(){
            $sql="SELECT count(*) as length FROM pre_lecturer";
            $info = DB::query($sql);
            $value = DB::fetch($info);    
            return $value[length];
        }
	function getLecturerInfo($pagenumber,$pernumber){
            $sql="SELECT * FROM pre_lecturer WHERE 1 ORDER BY id LIMIT ".($pagenumber-1)*$pernumber.",".$pernumber;
            $info = DB::query($sql);
            if($info==False)
            {
                return  0;                
            }
            else
            {
                    while ($value = DB::fetch($info)){
                        $obj[] = $value;                        
                        }
                        return $obj;                        
            }
        }
	
	
?>