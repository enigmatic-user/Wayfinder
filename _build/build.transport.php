<?php
/**
 * Build script for wayfinder
 *
 * @package wayfinder
 * @subpackage build
 */
$tstart = microtime(true);
/* get rid of time limit */
set_time_limit(0);
$root = dirname(dirname(__FILE__)).'/';
$sources= array (
    'root' => $root,
    'build' => $root . '_build/',
    'data' => $root . '_build/data/',
    'docs' => $root . 'core/components/wayfinder/docs/',
    'source_core' => $root . 'core/components/wayfinder',
);

/* override with your own defines here (see build.config.sample.php) */
require_once dirname(__FILE__) . '/build.config.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx= new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');


$modx->loadClass('transport.modPackageBuilder','',false, true);
$builder = new modPackageBuilder($modx);
$builder->createPackage('wayfinder','2.4.0','beta');
$builder->registerNamespace('wayfinder',false,true,'{core_path}components/wayfinder/');

/* get the source from the actual snippet in your database
 * [alternative] you could also manually create the object, grabbing the source
 * from a file
 */
$c= $modx->newObject('modSnippet');
$c->set('id',1);
$c->set('name', 'Wayfinder');
$c->set('description', 'Wayfinder for MODx Revolution.');
$c->set('snippet', file_get_contents($sources['source_core'] . '/wayfinder.snippet.php'));
$c->set('category', 0);
$properties = include $sources['data'].'properties.inc.php';
$c->setProperties($properties, true);

$attributes= array(
    xPDOTransport::UNIQUE_KEY => 'name',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
);
$vehicle = $builder->createVehicle($c, $attributes);

$vehicle->resolve('file',array(
    'source' => $sources['source_core'],
    'target' => "return MODX_CORE_PATH . 'components/';",
));
$builder->putVehicle($vehicle);

/* now pack in the license file, readme and setup options */
$builder->setPackageAttributes(array(
    'license' => file_get_contents($sources['docs'] . 'license.txt'),
    'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
    'changelog' => file_get_contents($sources['docs'] . 'changelog.txt'),
));

$builder->pack();

$totalTime= (microtime(true) - $tstart);
$totalTime= sprintf("%2.4f s", $totalTime);

$modx->log(modX::LOG_LEVEL_INFO, "Wayfinder package built in {$totalTime}\n");

exit ();
