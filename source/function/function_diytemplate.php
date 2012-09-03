<?php

if(false==defined('DIR_COMPILED')) define('DIR_COMPILED',DISCUZ_ROOT.'/data/template');

function diy_template_replace_parse($tFile,$cFile) {

    $fileContent = false;
    
    if(!($fileContent = file_get_contents($tFile))){
        return false;
    }
	$fileContent = preg_replace( '/^(\xef\xbb\xbf)/', '', $fileContent ); //EFBBBF
    $fileContent = preg_replace("/\<\!\-\-\s*\\\$\{(.+?)\}\s*\-\-\>/ies", "diy_template_replace('<?php \\1; ?>')", $fileContent);
    $fileContent = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\\\ \-\'\,\%\*\/\.\(\)\>\'\"\$\x7f-\xff]+)\}/s", "<?php echo \\1; ?>", $fileContent);
    $fileContent = preg_replace("/\\\$\{(.+?)\}/ies", "diy_template_replace('<?php echo \\1; ?>')", $fileContent);
    $fileContent = preg_replace("/\<\!\-\-\s*\{else\s*if\s+(.+?)\}\s*\-\-\>/ies", "diy_template_replace('<?php } else if(\\1) { ?>')", $fileContent);
    $fileContent = preg_replace("/\<\!\-\-\s*\{elif\s+(.+?)\}\s*\-\-\>/ies", "diy_template_replace('<?php } else if(\\1) { ?>')", $fileContent);
    $fileContent = preg_replace("/\<\!\-\-\s*\{else\}\s*\-\-\>/is", "<?php } else { ?>", $fileContent);

    for($i = 0; $i < 5; ++$i) {
        $fileContent = preg_replace("/\<\!\-\-\s*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\s*\}\s*\-\-\>(.+?)\<\!\-\-\s*\{\/loop\}\s*\-\-\>/ies", "diy_template_replace('<?php if(is_array(\\1)){foreach(\\1 AS \\2=>\\3) { ?>\\4<?php }}?>')", $fileContent);
        $fileContent = preg_replace("/\<\!\-\-\s*\{loop\s+(\S+)\s+(\S+)\s*\}\s*\-\-\>(.+?)\<\!\-\-\s*\{\/loop\}\s*\-\-\>/ies", "diy_template_replace('<?php if(is_array(\\1)){foreach(\\1 AS \\2) { ?>\\3<?php }}?>')", $fileContent);
        $fileContent = preg_replace("/\<\!\-\-\s*\{if\s+(.+?)\}\s*\-\-\>(.+?)\<\!\-\-\s*\{\/if\}\s*\-\-\>/ies", "diy_template_replace('<?php if(\\1){?>\\2<?php }?>')", $fileContent);
    }

    //Add for call <!--{include othertpl}-->
    $fileContent = preg_replace("#<!--\s*{\s*include\s+([^\{\}]+)\s*\}\s*-->#i", '<?php include template("\\1");?>', $fileContent);

    //Add value namespace
    if(!file_put_contents($cFile,$fileContent))
        return false;

    return true;
}

function diy_template_replace($string) {
    return str_replace('\"', '"', $string);
}

function diy_template_template($root, $compfile, $tFile) {

    $tFileN = preg_replace( '/\.htm$/', '', $tFile);
    $tFile = $root . '/' . $tFileN . '.htm';
    $cFile = DIR_COMPILED . '/' . $compfile . '.php';

    if(false===file_exists($tFile)){
        echo("Templace File [$tFile] Not Found!");
        return false;
    }

    if(false===file_exists($cFile)
            || @filemtime($tFile) > @filemtime($cFile)) {
        diy_template_replace_parse($tFile,$cFile);
    }

    return $cFile;
}

function diy_template_render($root, $template, $compfile, $data){
    ob_start();
    foreach($GLOBALS AS $_k=>$_v) {
        ${$_k} = $_v;
    }
	foreach($data AS $_k=>$_v) {
		${$_k} = $_v;
	}
	include diy_template_template($root,$compfile, $template);
    $string = ob_get_contents();
     ob_get_clean();
    return $string;
}
?>
