<?php header("HTTP/1.1 403 Forbidden"); ?>
<html>
<head>
<title>403 Forbidden</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
<style type="text/css">

body {
background-color:	#fff;
margin:				40px;
font-family:		Lucida Grande, Verdana;
font-size:			10pt;
color:				#000;
}

#content  {
border:				1px solid navy;
background-color:	#fff;
padding:			20px 20px 12px 20px;
}

h1 {
font-weight:		bold;
font-size:			14pt;
color:				darkred;
margin: 			0 0 4px 0;
}
</style>
</head>
<body>
	<div id="content">
		<h1><?=$level;?></h1>
		<h4>Something has gone wrong...</h4>
		<?=$message;?>
	</div>
</body>
</html>