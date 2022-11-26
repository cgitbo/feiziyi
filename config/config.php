<?php return array (
  'logs' => 
  array (
    'path' => 'backup/logs',
    'type' => 'file',
  ),
  'DB' => 
  array (
    'type' => 'mysqli',
    'tablePre' => 'pre_',
    'read' => 
    array (
      0 => 
      array (
        'host' => 'localhost:3306',
        'user' => 'root',
        'passwd' => 'root',
        'name' => 'feiziyi',
      ),
    ),
    'write' => 
    array (
      'host' => 'localhost:3306',
      'user' => 'root',
      'passwd' => 'root',
      'name' => 'feiziyi',
    ),
  ),
  'interceptor' => 
  array (
    0 => 'themeroute@onCreateController',
    1 => 'layoutroute@onCreateView',
    2 => 'plugin',
  ),
  'langPath' => 'language',
  'viewPath' => 'views',
  'skinPath' => 'skin',
  'classes' => 'classes.*',
  'rewriteRule' => 'pathinfo',
  'theme' => 
  array (
    'pc' => 
    array (
      'sysdm' => 'default',
      'sysiseller' => 'default',
      'mobile_fun' => 'default',
    ),
    'mobile' => 
    array (
      'sysdm' => 'default',
      'sysiseller' => 'default',
      'mobile_fun' => 'default',
    ),
  ),
  'timezone' => 'Etc/GMT-8',
  'upload' => 'upload',
  'dbbackup' => 'backup/database',
  'safe' => 'session',
  'lang' => 'zh_sc',
  'debug' => '1',
  'configExt' => 
  array (
    'site_config' => 'config/site_config.php',
    'plugin_config' => 'config/plugin_config.php',
  ),
  'encryptKey' => '284a4923cd90e05747f857745b5430e6',
  'authorizeCode' => '201609302314',
  'uploadSize' => '10',
  'low_withdraw' => '1',
  'low_bill' => '0',
  'api_account' => '',
  'api_key' => '',
)?>