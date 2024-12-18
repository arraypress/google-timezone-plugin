<?php return array(
    'root' => array(
        'name' => 'arraypress/google-timezone-plugin',
        'pretty_version' => '1.0.0+no-version-set',
        'version' => '1.0.0.0',
        'reference' => null,
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'arraypress/google-timezone' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => 'accce3e5363ccf3b46dd769b4b983824b9631648',
            'type' => 'library',
            'install_path' => __DIR__ . '/../arraypress/google-timezone',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
        'arraypress/google-timezone-plugin' => array(
            'pretty_version' => '1.0.0+no-version-set',
            'version' => '1.0.0.0',
            'reference' => null,
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
