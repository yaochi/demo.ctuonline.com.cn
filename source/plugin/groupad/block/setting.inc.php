<?php

$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
$blockclass['noticeinfo']["subs"][$plugin_id] = array(
                "name" => '广告',
		"script" => array(
				$plugin_id . "_text" => array ( name => "文字广告",
                                    style => array(
                                        array(
                                            name => "广告标题",
                                            key => "groupad_text"
                                        )
                                    )
                                ),                                
                                $plugin_id . "_images" => array(name => "图片广告",
                                    style => array(
                                        array(
                                            name => "仅图片",
                                            key => "groupad_only_pic"
                                        ),                                        
                                        array(
                                            name => "图文混排",
                                            key => "groupad_pic_text"
                                        ),
                                        array(
                                            name => "图片幻灯",
                                            key => "groupad_pics"
                                        ),
                                        array(
                                            name => "从左至右滚动",
                                            key => "groupad_pics_roll"
                                        ),
                                        array(
                                            name => "互动橱窗",
                                            key => "groupad_pics_showwindows"
                                        )
                                    )
                               ),

				$plugin_id . "_flash" => array(name => "Flash广告",
                                    style => array(
                                        array(
                                            name => "独立样式",
                                            key => "groupad_flash"
                                        )
                                    )
                               )
		),
);
?>
