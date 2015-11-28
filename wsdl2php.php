<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>WSDSL2PHP</title>
</head>
<body>
<?
if (isset($_POST['url']))
{
 $url = $_POST['url'];
 $sname = $_POST['sname'];
}
else
{
 $url='';
 $sname = '';
}
?>
<h2>Easy WSDL2PHP Generator</h2>
<p> ej. http://soap.amazon.com/schemas2/AmazonWebServices.wsdl</p>

<form action="wsdl2php.php" method="post">
<p>Url: <input type="text" name="url" size="60" value="<?=$url?>" /></p>
<p>Class Name: <input type="text" name="sname" size="60" value="<?=$sname?>" /></p>

<input type="submit" name="generatebtn" value="Generate Code" />
</form>

<?if (isset($_POST['generatebtn'])){?>
<form method="post" action="http://thephppro.com/tools/beautify.php">
<textarea rows="10" cols="80" name="code">

<?
include 'EasyWsdl2PHP.php';
echo EasyWsdl2PHP::generate(trim($url),$sname);
?>
</textarea>
</form>
Beutify at <a target="_blank" href="http://thephppro.com/tools/beautify.php">http://thephppro.com/tools/beautify.php</a>

<?}?>
</body>
</html>