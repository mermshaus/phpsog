<?php

namespace org\ermshaus\phpsog;

use \Exception;
use \Kaloa\Loader;

require_once './library/Kaloa/library/Kaloa/Loader.php';
$loader = new Loader('org\\ermshaus', './library');
$loader->register();
$loader = new Loader('Kaloa', './library/Kaloa/library');
$loader->register();

function e($s)
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$params = array(
    'config' => ''
);

if (!isset($_GET['config'])) {
    throw new Exception('Config not set');
} else {
    $params['config'] = (string) $_GET['config'];
}

if (is_dir($params['config'])) {
    $params['config'] .= '/phpsog.ini';
}

$phpsog = new PhpSog();

$phpsog->loadConfig($params['config']);

$phpsog->sanitizeEnvironment();

$phpsog->processFiles();
$phpsog->processResources();





exit;


// Virtual pages

include_once './blog-content.php';

$blogOverviewPage = '<h1>Blog</h1>';

foreach ($blog as $data) {
    $content = $phpsog->addVirtualPage($config, '<h1>' . e($data['title']) . '</h1>' . $data['content'], $data['title'], null, '../');

    $exportPath = $config['project.dir'] . '/' . $config['export.dir'] . '/blog/' . $data['id'] . '.html';

    if (!file_exists(dirname($exportPath))) {
        mkdir(dirname($exportPath));
        echo 'Created dir...' . PHP_EOL;
        echo '  ' . dirname($exportPath) . PHP_EOL;
    }

    file_put_contents($exportPath, $content);

    $blogOverviewPage .= '<p><a href="blog/' . $data['id'] . '.html">' . e($data['title']) . '</a></p>';
}

$content = $phpsog->addVirtualPage($config, $blogOverviewPage, 'Blog overview');

file_put_contents($config['project.dir'] . '/' . $config['export.dir'] . '/blog.html', $content);
