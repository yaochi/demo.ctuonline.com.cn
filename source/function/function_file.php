<?php
/**
 * 文件上传
 * error 错误详情
 * errorid 错误id（出现错误才有值）
 * type 文件扩展名（扩展名前带“.”）
 * filename 文件名(不带后缀)
 * upname 上传的文件名
 * time 上传的时间（从 Unix 纪元（1970-01-01 00:00）开始至今的秒数）
 */
function file_func_upload($file, $path, $type) {
    $state = array();
    $state['error'] = true;
    $alltype = ""; // 所有可以上传的类型，用"/"连接起来
    $path = trim($path);
    //为$path末尾加上"/"
    if (strlen(strrchr($path, '/')) <= 1) {
        $path .= "/";
    }
    //为类型加上开头“.”
    //将所有类型合成字符串，用"/"连接起来
    foreach ($type as $key => $typeone) {
        $type[$key] = $typeone = trim($typeone);
        if (strlen(strchr($typeone, ".")) != strlen($typeone)) {
            $type[$key] = "." . $typeone;
        }
        $alltype .= $typeone . '/';
    }
    $alltype = substr($alltype, 0, strlen($alltype) - 1); //去掉最后的“/”
    if (empty($file['name'])) {
        $state['error'] = "没有上传{$alltype}类型文件！";
        $state['errorid'] = 8;
        return $state;
    } else {
        if (!$file['error']) {
            $state['name'] = $file['name'];
            $state['type'] = strrchr($state['name'], '.');
            if (in_array($state['type'], $type)) {
                $time = date("U");
                $state['filename'] = $time . rand(1000, 9999);
                $state['upname'] = $state['filename'] . $state['type']; //文件命名
                if (copy($file['tmp_name'], $path . $state['upname'])) {
                    $state['time'] = date("U"); //上传的时间
                    $state['error'] = false;
                    return $state;
                } else {
                    switch ($file('error')) {
                        case 1: $state['error'] = $state['name'] . "上传失败，文件大小超出了服务器的空间大小！";
                            $state['errorid'] = 1;
                            return $state;
                        case 2: $state['error'] = $state['name'] . "上传失败，要上传的文件大小超出浏览器限制！";
                            $state['errorid'] = 2;
                            return $state;
                        case 3: $state['error'] = $state['name'] . "上传失败，文件仅部分被上传！";
                            $state['errorid'] = 3;
                            return $state;
                        case 4: $state['error'] = $state['name'] . "上传失败，没有找到要上传的文件！";
                            $state['errorid'] = 4;
                            return $state;
                        case 5: $state['error'] = $state['name'] . "上传失败，服务器临时文件夹丢失！";
                            $state['errorid'] = 5;
                            return $state;
                        case 6: $state['error'] = $state['name'] . "上传失败，文件写入到临时文件夹出错！";
                            $state['errorid'] = 6;
                            return $state;
                        default: $state['error'] = $state['name'] . "上传失败，位置错误！";
                            $state['errorid'] = 10;
                            return $state;
                    }
                }
            } else {
                $state['error'] = $state['name'] . "上传失败！不符合所要上传的文件类型！({$alltype})";
                $state['errorid'] = 10;
                return $state;
            }
        }
    }
}

/*新建目录,以递归的方式新建一个完整的目录结构*/
function file_func_mkdirp($path) {
    if (!file_exists($path)) {
        file_func_mkdirp(dirname($path));
        @mkdir($path, 0777);
    }
}
?>
