<?php
		$con = mysql_connect('10.168.11.22', 'root', 'fangpinhui_db');	
		if (!$con)
		{
			die('Could not connect: '.mysql_error());//这里数据库连接不上也应该输出xml文本  方便在微信端知道哪里出错
		}
		mysql_query("set names utf8");
       mysql_select_db('fangpinhui', $con);
?>