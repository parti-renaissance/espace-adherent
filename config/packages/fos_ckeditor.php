<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('fos_ck_editor', [
        'default_config' => 'default',
        'configs' => [
            'default' => [
                'toolbar' => [
                    [
                        'Bold',
                        'Italic',
                        'Underline',
                        '-',
                        'Cut',
                        'Copy',
                        'Paste',
                        'PasteText',
                        'PasteFromWord',
                        '-',
                        'Undo',
                        'Redo',
                        '-',
                        'NumberedList',
                        'BulletedList',
                        '-',
                        'Outdent',
                        'Indent',
                        '-',
                        'Blockquote',
                        '-',
                        'Image',
                        'Link',
                        'Unlink',
                        'Table',
                    ],
                    [
                        'Format',
                        'Maximize',
                        'Source',
                    ],
                ],
            ],
        ],
    ]);
};
