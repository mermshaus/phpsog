<?php

namespace org\ermshaus\phpsog;

require_once './library/org/ermshaus/phpsog/PhpSog.php';

function e($s)
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$params = array(
    'config' => ''
);

if (!isset($_GET['config'])) {
    throw new \Exception('Config not set');
} else {
    $params['config'] = (string) $_GET['config'];
}

if (is_dir($params['config'])) {
    $params['config'] .= '/phpsog.ini';
}

$phpsog = new PhpSog();

$config = $phpsog->loadConfig($params['config']);

$exportDir = $config['project.dir'] . '/' . $config['export.dir'];

if (!is_writable($exportDir)) {
    throw new \Exception('export directory ' . $exportDir . ' is not writable');
}

// Process content pages

$files = glob($config['project.dir'] . '/' . $config['pages.dir'] . '/*.phtml');

foreach ($files as $file) {
    $content = $phpsog->processFile($config, $file);

    $exportPath = $config['project.dir'] . '/' . $config['export.dir'] . '/'
            . pathinfo($file, PATHINFO_FILENAME)
            . '.' . $config['export.fileExtension'];

    echo 'Exporting... ' . "\n";
    echo '  from: ' . $file . "\n";
    echo '  to:   ' . $exportPath . "\n";

    file_put_contents($exportPath, $content);
}

// Copy resources

$files = glob($config['project.dir'] . '/' . $config['resources.dir'] . '/*.*');

foreach ($files as $file) {
    $exportPath = $config['project.dir'] . '/' . $config['export.dir'] . '/'
            . basename($file);

    echo 'Moving resource...' . "\n";
    echo '  from: ' . $file . "\n";
    echo '  to:   ' . $exportPath . "\n";

    copy($file, $exportPath);
}
