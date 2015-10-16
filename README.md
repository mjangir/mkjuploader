# mkjuploader
A simple library to easily handle file uploads, downloads and move with any PHP framework.

<p>Basically the library employs adapter concept to upload the files whether they are being uploaded in local directory or any third party storage. Currently I have only local adapter injected by default but I'm coming with other third party adapters like AmazonS3 etc.</p>

<h3>How To Install Mkjuploader</h3>

```shell
$ composer require "mjangir/mkjuploader":"dev-master"
```

<h3>How To Use</h3>

The package can be used for various purposes I'm describing in the following.

<h4>1. Upload A File</h4>
A file can be uploaded following the below steps:
<ol>
<li>Create a suitable adapter having upload path and public path</li>
<li>Create the main Upload object and pass the adapter in it</li>
<li>Crate the File object for posted input</li>
<li>Use the upload function of Uploader object</li>
<li>It will return the actual uploaded file key</li>
</ol>

Refer the below code to work with immediately

<b>Create a database first</b>
```sql
CREATE DATABASE /*!32312 IF NOT EXISTS*/`mkjuploader` /*!40100 DEFAULT CHARACTER SET latin1 */;

/*Table structure for table `files` */

DROP TABLE IF EXISTS `files`;

CREATE TABLE `files` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
```
<b>Write the PHP code</b>
<small>index.php</small>
```php
<?php

//Upload A New File Through $_FILE Input

require "vendor/autoload.php";

use MkjUploader\File\Input;
use MkjUploader\Upload;
use MkjUploader\Adapter\Local;



if(isset($_POST['submit'])) {

    //Create upload directory if not exist
    if(!is_dir('uploads')) {
    	mkdir('uploads', 0777, true);
    }
    
    //Create a local adapter. uploads folder must be there and writeable
    $adapter = new Local('uploads','uploads');

    //Create main Upload object and pass the adapter
    $uploadObj = new Upload($adapter);
	
    //Create uploading file object
    $fileObject = new Input($_FILES['profile']);
    
    //Upload the actual file. It will give the uploaded key like below
    // 8/8/b/88bd070a290dba83f0594f791da5b8de8326c833/bootstrap-3.3.5-dist.zip
    $key = $uploadObj->upload($fileObject);
    
    
    //Insert the file information in database
    $dbhost     = "DATABASE_HOST";
    $dbname     = "DATABASE_NAME";
    $dbuser     = "DATABASE_USER";
    $dbpass     = "DATABASE_PASS";

    $conn = new PDO("mysql:host=$dbhost;dbname=$dbname",$dbuser,$dbpass);

    $sql = "INSERT INTO `files` (`key`,`created`) VALUES (:key,:created)";
    $q = $conn->prepare($sql);
    $q->execute(array(':key'      =>  $key,
                      ':created'  =>  date('Y-m-d H:i:s')
                     ));
}

?>
```
```html
<!-- Upload File Form -->
<form method="post" action="index.php" enctype="multipart/form-data">
    <input type="file" name="profile"/>
    <input type="submit" name="submit" value="submit" />
</form>
```

<h4>Get A File Info</h4>
You can get the complete file info on the basis of stored key in database. In the following example I'm using a static key for one of my stored files.

<small>get.php</small>

```php
<?php
require "vendor/autoload.php";

use MkjUploader\Upload;
use MkjUploader\Adapter\Local;

//Get the key from the database. I'm using a static here
$key = '6/4/3/643c4b13e88cb02e6e4a9fa6369666bbb83c978e/jdbc.zip';

//Create a local adapter. uploads folder must be there and writeable
$adapter = new Local('uploads','uploads');

//Create main Upload object and pass the adapter
$uploadObj = new Upload($adapter);

//Get file info
$info   = $uploadObj->get($key);

//Iterate over the object
echo "Public Path       :: ". $info->getPublicPath() ."<br/>"; //uploads/6/4/3/643c4b13e88cb02e6e4a9fa6369666bbb83c978e/jdbc.zip
echo "Base Name         :: ". $info->getBasename() ."<br/>"; //jdbc.zip
echo "File Extension    :: ". $info->getExtension() ."<br/>"; //zip
echo "File Size         :: ". $info->getContentLength() ."<br/>"; //2921919
echo "Mime Type         :: ". $info->getContentType() ."<br/>"; //application/zip
echo "Last Modified     :: ". $info->getLastModified()->format('d M, Y') ."<br/>"; //16 Oct, 2015

```

<h4>Download A File Info</h4>
You can download a file on the basis of stored key in database. In the following example I'm using a static key for one of my stored files.

<small>download.php</small>

```php
<?php
require "vendor/autoload.php";

use MkjUploader\Upload;
use MkjUploader\Adapter\Local;

//Get the key from the database. I'm using a static here
$key = '6/4/3/643c4b13e88cb02e6e4a9fa6369666bbb83c978e/jdbc.zip';

//Create a local adapter. uploads folder must be there and writeable
$adapter = new Local('uploads','uploads');

//Create main Upload object and pass the adapter
$uploadObj = new Upload($adapter);

//Get file info
$info   = $uploadObj->get($key);

$fileName = $info->getBasename();

//If file name doesn't contain extension
if (!pathinfo($fileName, PATHINFO_EXTENSION) && $info->getExtension()) {
    $fileName .= '.'. $info->getExtension();
}

//Set the http response headers to download the file
$mimeType = $info->getContentType() ?: 'application/octet-stream';

header('Content-Type: "'.$mimeType.'"');
header('Content-Disposition: attachment; filename="'. str_replace('"', '\\"', $fileName) .'"');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header("Content-Transfer-Encoding: binary");
header('Pragma: public');
header("Content-Length: ".$info->getContentLength());

//Flush the content
echo $info->getContent();
