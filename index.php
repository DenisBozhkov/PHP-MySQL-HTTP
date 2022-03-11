<?php
	const host='localhost';
	const user='root';
	const pass='';

	function config_db_connection()
	{
		$link=mysqli_connect(host,user,pass);
		
		try 
		{ 
			mysqli_select_db($link,'exam'); 
		}
		catch(Exception)
		{
			mysqli_query($link,'CREATE DATABASE exam');
			mysqli_select_db($link,'exam');
		}
		
		try 
		{ 
			mysqli_query($link,'SELECT * FROM weather LIMIT 1'); 
		}
		catch(Exception)
		{
			$query='CREATE TABLE weather(
				id INT AUTO_INCREMENT PRIMARY KEY,
				temperature DOUBLE NOT NULL,
				humidity DOUBLE NOT NULL,
				pressure DOUBLE NOT NULL,
				datetime DATETIME)';
			mysqli_query($link,$query);
		}
		
		//sets the value of $link as a global variable
		$GLOBALS['link']=$link;
	}

	function show_data_table()
	{
		//uses the global variable $link
		global $link;
		
		$page=$_GET['page']??0;
		$N=15;
		
		$result=mysqli_query($link,'SELECT COUNT(*) FROM weather');
		$records_count=mysqli_fetch_row($result)[0];
		
		$html='<table border="1">';
		$html.='<tr><th>Date</th><th>Temperature</th><th>Humidity</th><th>Pressure</th></tr>';
		
		$result=mysqli_query($link,'SELECT * FROM weather ORDER BY datetime DESC LIMIT '.($N*$page).",$N");
		
		while($row=mysqli_fetch_array($result))
		{
			$html.='<tr>';
			$html.="<td>{$row['datetime']}</td>";
			$html.="<td>{$row['temperature']}</td>";
			$html.="<td>{$row['humidity']}</td>";
			$html.="<td>{$row['pressure']}</td>";
			$html.='</tr>';
		}
		
		$html.='</table>';
		$html.='<p>';
		
		if($page>0)
			$html.="<a href=\"{$_SERVER['PHP_SELF']}?page=".($page-1)."\">Previous</a>";
		if($page>0&&($page+1)*$N<$records_count)
			$html.=" | ";
		if(($page+1)*$N<$records_count)
			$html.="<a href=\"{$_SERVER['PHP_SELF']}?page=".($page+1)."\">Next</a>";
		
		return $html.'</p>';
	}
	
	try
	{
		config_db_connection();
		
		if(!isset($_GET['temp'])||!isset($_GET['humidity'])||!isset($_GET['pressure']))
			die(show_data_table());
		
		echo "<p>Temperature: {$_GET['temp']}</p>";
		echo "<p>Humidity: {$_GET['humidity']}</p>";
		echo "<p>Pressure: {$_GET['pressure']}</p>";
		
		mysqli_query($link,"INSERT INTO weather VALUES(0,{$_GET['temp']},{$_GET['humidity']},{$_GET['pressure']},'".date('Y-m-d H:i:s')."')");
	}
	catch(Exception $err)
	{
		echo "<p>{$err->getMessage()}</p>";
	}
	finally
	{
		if($link)
			mysqli_close($link);
	}
?>