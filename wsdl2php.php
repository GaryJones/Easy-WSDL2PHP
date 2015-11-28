<?php
include 'EasyWsdl2PHP.php';
if (isset($_POST['url'])) {
    $url   = $_POST['url'];
    $sname = $_POST['sname'];
} else {
    $url   = '';
    $sname = '';
}
?>
<!DOCTYPE>
<html>
<head>
    <meta charset="utf-8" />
    <title>WSDSL2PHP</title>
</head>
<body>
    <h1>Easy WSDL2PHP Generator</h1>
    <form action="wsdl2php.php" method="post">
        <div><label for="url">Url:<br /><input type="url" name="url" id="url" size="60" placeholder="e.g. http://www.webservicex.com/CurrencyConvertor.asmx?wsdl" value="<?php echo $url ? $url : ''; ?>" /></label><br /></div>

        <div><br /><label for="sname">Service Class Name:<br /><input type="text" name="sname" id="sname" size="20" placeholder="Service" value="<?php echo $sname ? $sname : ''; ?>" /></label><br /></div>

        <br /><button type="submit" name="generatebtn">Generate Code</button>
    </form>

    <?php
    if (isset($_POST['generatebtn'])) {
    ?>
    <label for="code">Code:</label><br />
    <textarea rows="20" cols="120" name="code" id="code">
    <?php
    echo EasyWsdl2PHP::generate(trim($url), $sname);
    ?>
    </textarea>
    <?php
    }
    ?>
</body>
</html>
