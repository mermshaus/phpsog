<?php

namespace org\ermshaus\phpsog;

require_once './library/org/ermshaus/spl/SplClassLoader.php';
$loader = new \org\ermshaus\spl\SplClassLoader(null, './library');
$loader->register();

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


$dirIter = new \RecursiveDirectoryIterator($config['project.dir'] . '/' . $config['pages.dir']);
$recursiveIterator = new \RecursiveIteratorIterator($dirIter,
    \RecursiveIteratorIterator::SELF_FIRST,
    \RecursiveIteratorIterator::CATCH_GET_CHILD);

$regexIterator = new \RegexIterator($recursiveIterator, '/\.phtml$/i');

foreach ($regexIterator as $file => $unused) {
    $content = $phpsog->processFile($config, $file);

    $relativePath = substr($file, strlen($config['project.dir'] . '/' . $config['pages.dir'] . '/'));

    $exportPath = $config['project.dir'] . '/' . $config['export.dir'] . '/'
            . pathinfo($relativePath, PATHINFO_DIRNAME) . '/' . pathinfo($relativePath, PATHINFO_FILENAME)
            . '.' . $config['export.fileExtension'];

    if (!file_exists(dirname($exportPath))) {
        mkdir(dirname($exportPath));
        echo 'Created dir...' . PHP_EOL;
        echo '  ' . dirname($exportPath) . PHP_EOL;
    }

    echo 'Exporting... ' . "\n";
    echo '  from: ' . $file . "\n";
    echo '  to:   ' . $exportPath . "\n";

    file_put_contents($exportPath, $content);
}

// Copy resources

$dirIter = new \RecursiveDirectoryIterator($config['project.dir'] . '/' . $config['resources.dir']);
$recursiveIterator = new \RecursiveIteratorIterator($dirIter,
    \RecursiveIteratorIterator::SELF_FIRST,
    \RecursiveIteratorIterator::CATCH_GET_CHILD);

foreach ($recursiveIterator as $file => $unused) {

    if (!is_file($file)) {
        continue;
    }

    $relativePath = substr($file, strlen($config['project.dir'] . '/' . $config['resources.dir'] . '/'));

    $exportPath = $config['project.dir'] . '/' . $config['export.dir'] . '/'
            . pathinfo($relativePath, PATHINFO_DIRNAME) . '/' . basename($relativePath);

    if (!file_exists(dirname($exportPath))) {
        mkdir(dirname($exportPath));
        echo 'Created dir...' . PHP_EOL;
        echo '  ' . dirname($exportPath) . PHP_EOL;
    }

    echo 'Moving resource...' . "\n";
    echo '  from: ' . $file . "\n";
    echo '  to:   ' . $exportPath . "\n";

    copy($file, $exportPath);
}
