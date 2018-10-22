<form action="#" method="post" enctype="multipart/form-data">
    <input type="file" name="html_file"><br/>
    <input type="submit" value="Upload" name="submit">
</form>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'DonjonConverter.php';
require_once 'tmp.php';
$map = '';
$info = '';
$rooms = '';
if (isset($_POST["submit"])) {
    $target_file = '/var/www/moridrin.com/tmp/' . basename($_FILES["html_file"]["name"]);
    move_uploaded_file($_FILES["html_file"]["tmp_name"], $target_file);
    $converted = DonjonConverter::Convert('http://moridrin.com/tmp/' . $_FILES["html_file"]["name"]);
    $map = $converted['map'];
    $info = $converted['info'];
    $rooms = $converted['rooms'];
}
?>
<textarea><?= $map ?></textarea>
<textarea><?= $info ?></textarea>
<textarea><?= $rooms ?></textarea>
