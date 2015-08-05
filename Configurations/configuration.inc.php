<?php

ini_set('date.timezone', 'Europe/Moscow');
set_time_limit(0);


error_reporting(E_ALL | E_STRICT);
setlocale(LC_CTYPE, "ru_RU.UTF8");
setlocale(LC_TIME, "ru_RU.UTF8");

define('DEFAULT_ENCODING', 'UTF-8');
mb_internal_encoding(DEFAULT_ENCODING);
mb_regex_encoding(DEFAULT_ENCODING);

define('__LOCAL_DEBUG__', true);

define('RELEASE_VERSION', 'v0v1');


if (!defined('PATH_SOURCE_DIR')) {
    define('PATH_SOURCE_DIR', '');
}
define('EXT_CLASS', '.class.php');
define('EXT_TPL', '.php');
define('EXT_MOD', '.inc.php');
define('EXT_HTML', '.html');
define('EXT_UNIT', '.unit.php');
if (!defined('ONPHP_TEMP_PATH')) {
    $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'onPHP' . DIRECTORY_SEPARATOR;
    if (!file_exists($tmpDir)) mkdir($tmpDir);
    define('ONPHP_TEMP_PATH', $tmpDir);
}

if (!defined('ONPHP_CLASS_CACHE'))
    define('ONPHP_CLASS_CACHE', ONPHP_TEMP_PATH);

if (!defined('ONPHP_CLASS_CACHE_TYPE'))
    define('ONPHP_CLASS_CACHE_TYPE', 'classPathCache');

define('ONPHP_VERSION', '1.1.master');


define('PATH_BASE', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);

define('PATH_BIN', PATH_BASE . 'Bin' . DIRECTORY_SEPARATOR);
define('PATH_COMPONENTS', PATH_BASE . 'Components' . DIRECTORY_SEPARATOR);
define('PATH_CONFIGURATIONS', PATH_BASE . 'Configurations' . DIRECTORY_SEPARATOR);
define('PATH_CONTENT', PATH_BASE . 'Content' . DIRECTORY_SEPARATOR);
define('PATH_CONTROLLERS', PATH_BASE . 'Controllers' . DIRECTORY_SEPARATOR);
define('PATH_HELPERS', PATH_BASE . 'Helpers' . DIRECTORY_SEPARATOR);
define('PATH_MODELS', PATH_BASE . 'Models' . DIRECTORY_SEPARATOR);
define('PATH_MODULES', PATH_BASE . 'Modules' . DIRECTORY_SEPARATOR);
define('PATH_REPOSITORIES', PATH_BASE . 'Repositories' . DIRECTORY_SEPARATOR);
define('PATH_SERVICES', PATH_BASE . 'Services' . DIRECTORY_SEPARATOR);
define('PATH_VIEWS', PATH_BASE . 'Views' . DIRECTORY_SEPARATOR);
define('PATH_VENDORS', PATH_BASE . 'Vendors' . DIRECTORY_SEPARATOR);
define('PATH_CLASSES', PATH_MODELS);


define('PATH_ONPHP', PATH_VENDORS . 'onphp-framework' . DIRECTORY_SEPARATOR);
define('PATH_ONPHP_CORE', PATH_ONPHP);
define('PATH_ONPHPUTILS', PATH_VENDORS . 'onPHPUtils' . DIRECTORY_SEPARATOR);


define('PATH_WEB', 'http://acomru.test/');
define('COOKIE_HOST_NAME', 'acomru.test');

define('PATH_WEB_URL', PATH_WEB . 'index.php?');
define('PATH_WEB_CSS', PATH_WEB . 'Styles/');
define('PATH_WEB_IMG', PATH_WEB . 'Images/');
define('PATH_WEB_JS', PATH_WEB . 'Scripts/');

require_once(PATH_VENDORS . 'swiftmailer-5.x' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'swift_required.php');

require_once(PATH_VENDORS . 'gelf-php' . DIRECTORY_SEPARATOR . 'GELFMessage.php');
require_once(PATH_VENDORS . 'gelf-php' . DIRECTORY_SEPARATOR . 'GELFMessagePublisher.php');


require_once PATH_ONPHPUTILS . 'src' . DIRECTORY_SEPARATOR . 'Autoloader' . DIRECTORY_SEPARATOR . 'require.inc.php';

AutoloaderClassPathCache::create()
    ->setNamespaceResolver(NamespaceResolverPSR0::create())
    ->addPaths(array(
        PATH_ONPHP . 'core' . DIRECTORY_SEPARATOR . 'Base',
        PATH_ONPHP . 'core' . DIRECTORY_SEPARATOR . 'Cache',
        PATH_ONPHP . 'core' . DIRECTORY_SEPARATOR . 'DB',
        PATH_ONPHP . 'core' . DIRECTORY_SEPARATOR . 'DB' . DIRECTORY_SEPARATOR . 'Transaction',
        PATH_ONPHP . 'core' . DIRECTORY_SEPARATOR . 'Exceptions',
        PATH_ONPHP . 'core' . DIRECTORY_SEPARATOR . 'Form',
        PATH_ONPHP . 'core' . DIRECTORY_SEPARATOR . 'Form' . DIRECTORY_SEPARATOR . 'Filters',
        PATH_ONPHP . 'core' . DIRECTORY_SEPARATOR . 'Form' . DIRECTORY_SEPARATOR . 'Primitives',
        PATH_ONPHP . 'core' . DIRECTORY_SEPARATOR . 'Logic',
        PATH_ONPHP . 'core' . DIRECTORY_SEPARATOR . 'OSQL',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Application',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Base',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Charts',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Charts' . DIRECTORY_SEPARATOR . 'Google',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Criteria',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Criteria' . DIRECTORY_SEPARATOR . 'Projections',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Crypto',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'DAOs',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'DAOs' . DIRECTORY_SEPARATOR . 'Handlers',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'DAOs' . DIRECTORY_SEPARATOR . 'Uncachers',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'DAOs' . DIRECTORY_SEPARATOR . 'Workers',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'EntityProto',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'EntityProto' . DIRECTORY_SEPARATOR . 'Accessors',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'EntityProto' . DIRECTORY_SEPARATOR . 'Builders',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Flow',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Markup',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Markup' . DIRECTORY_SEPARATOR . 'Feed',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Markup' . DIRECTORY_SEPARATOR . 'Html',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Math',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Messages',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Messages' . DIRECTORY_SEPARATOR . 'Interface',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Monitoring',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Monitoring' . DIRECTORY_SEPARATOR . 'Interface',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Net',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Net' . DIRECTORY_SEPARATOR . 'Http',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Net' . DIRECTORY_SEPARATOR . 'Ip',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Net' . DIRECTORY_SEPARATOR . 'Mail',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Net' . DIRECTORY_SEPARATOR . 'Soap',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'OpenId',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'OQL',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'OQL' . DIRECTORY_SEPARATOR . 'Expressions',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'OQL' . DIRECTORY_SEPARATOR . 'Parsers',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'OQL' . DIRECTORY_SEPARATOR . 'Statements',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'SPL',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'UI',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'UI' . DIRECTORY_SEPARATOR . 'View',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'UnifiedContainer',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Utils',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Utils' . DIRECTORY_SEPARATOR . 'AMQP',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Utils' . DIRECTORY_SEPARATOR . 'AMQP' . DIRECTORY_SEPARATOR . 'Exceptions',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Utils' . DIRECTORY_SEPARATOR . 'AMQP' . DIRECTORY_SEPARATOR . 'Pecl',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Utils' . DIRECTORY_SEPARATOR . 'Archivers',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Utils' . DIRECTORY_SEPARATOR . 'CommandLine',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Utils' . DIRECTORY_SEPARATOR . 'IO',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Utils' . DIRECTORY_SEPARATOR . 'Logging',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Utils' . DIRECTORY_SEPARATOR . 'Mobile',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Utils' . DIRECTORY_SEPARATOR . 'Routers',
        PATH_ONPHP . 'main' . DIRECTORY_SEPARATOR . 'Utils' . DIRECTORY_SEPARATOR . 'TuringTest',

        PATH_ONPHP . 'meta',
        PATH_ONPHP . 'meta' . DIRECTORY_SEPARATOR . 'bin',
        PATH_ONPHP . 'meta' . DIRECTORY_SEPARATOR . 'builders',
        PATH_ONPHP . 'meta' . DIRECTORY_SEPARATOR . 'classes',
        PATH_ONPHP . 'meta' . DIRECTORY_SEPARATOR . 'dtd',
        PATH_ONPHP . 'meta' . DIRECTORY_SEPARATOR . 'patterns',
        PATH_ONPHP . 'meta' . DIRECTORY_SEPARATOR . 'types',

        PATH_ONPHP . 'src' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Auto',
        PATH_ONPHP . 'src' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Auto' . DIRECTORY_SEPARATOR . 'Business',
        PATH_ONPHP . 'src' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Auto' . DIRECTORY_SEPARATOR . 'DAOs',
        PATH_ONPHP . 'src' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Auto' . DIRECTORY_SEPARATOR . 'Proto',
        PATH_ONPHP . 'src' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Business',
        PATH_ONPHP . 'src' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'DAOs',
        PATH_ONPHP . 'src' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Proto',

        PATH_ONPHPUTILS . 'src' . DIRECTORY_SEPARATOR . 'Access',
        PATH_ONPHPUTILS . 'src' . DIRECTORY_SEPARATOR . 'Application',
        PATH_ONPHPUTILS . 'src' . DIRECTORY_SEPARATOR . 'EntityProto',
        PATH_ONPHPUTILS . 'src' . DIRECTORY_SEPARATOR . 'ListMakerHelper',
        PATH_ONPHPUTILS . 'src' . DIRECTORY_SEPARATOR . 'ServiceLocator',
        PATH_ONPHPUTILS . 'src' . DIRECTORY_SEPARATOR . 'ToolkitFlow',
        PATH_ONPHPUTILS . 'src' . DIRECTORY_SEPARATOR . 'Translator',
        PATH_ONPHPUTILS . 'src' . DIRECTORY_SEPARATOR . 'Utils',

        PATH_BASE,

        PATH_BIN,
        PATH_COMPONENTS,
        PATH_COMPONENTS . 'Utils',
        PATH_COMPONENTS . 'Flow',
        PATH_CONFIGURATIONS,
        PATH_CONTENT,
        PATH_CONTROLLERS,
        PATH_HELPERS,
        PATH_MODELS,
        PATH_MODELS . 'Auto',
        PATH_MODELS . 'Auto' . DIRECTORY_SEPARATOR . 'Business',
        PATH_MODELS . 'Auto' . DIRECTORY_SEPARATOR . 'DAOs',
        PATH_MODELS . 'Auto' . DIRECTORY_SEPARATOR . 'Proto',
        PATH_MODELS . 'Business',
        PATH_MODELS . 'DAOs',
        PATH_MODELS . 'Proto',
        PATH_MODELS . 'Meta',
        PATH_MODULES,
        PATH_REPOSITORIES,
        PATH_SERVICES,
        PATH_VIEWS,
        PATH_VENDORS,
    ))
    ->addPath(PATH_VENDORS . 'Whoops', '\\Whoops')
    ->register();