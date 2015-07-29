<?php

	$ch = curl_init();
	$url = "http://myspider/home/Urlspider/run";
	//设置参数
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);//设置返回数据
	curl_setopt($ch,CURLOPT_HEADER,0);//设置头部不执行
	$output = curl_exec($ch);
	curl_close($ch);
	var_dump($output);
?>  