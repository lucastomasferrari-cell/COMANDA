<?php

return [
    'database' => 'Base de datos',
    'tabs' => [
        'backup' => 'Copia de seguridad',
        'restore' => 'Restaurar',
    ],
    'backup_note_title' => 'Nota sobre la copia de seguridad',
    'backup_note_details' => 'Crear una copia de seguridad exporta la base de datos actual a un archivo SQL en el almacenamiento. Mantené los archivos en un lugar seguro y generá copias de seguridad antes de actualizaciones importantes.',
    'restore_warning_title' => 'Advertencia de restauración',
    'restore_warning_details' => 'Restaurar una base de datos sobrescribirá los datos actuales. Siempre hacé una nueva copia de seguridad antes de restaurar y asegurate de informar a los usuarios.',
    'backup_file' => 'Archivo de copia de seguridad',
    'latest_backups' => 'Últimas copias de seguridad',
    'no_backups' => 'No se encontraron copias de seguridad.',
    'columns' => [
        'name' => 'Nombre',
        'size' => 'Tamaño',
        'created_at' => 'Creado el',
    ],
    'confirmations' => [
        'backup_title' => 'Crear copia de seguridad',
        'backup_message' => '¿Crear una nueva copia de seguridad de la base de datos ahora?',
        'restore_title' => 'Restaurar base de datos',
        'restore_upload_message' => '¿Restaurar la base de datos desde el archivo de copia seleccionado? Los datos actuales serán sobrescritos.',
        'restore_saved_message' => '¿Restaurar la base de datos desde esta copia guardada? Los datos actuales serán sobrescritos.',
    ],

    'messages' => [
        'backup_created' => 'La copia de seguridad de la base de datos se creó correctamente.',
        'restore_completed' => 'La base de datos se restauró correctamente.',
        'download_started' => 'La descarga de la copia de seguridad comenzó correctamente.',
        'backup_not_found' => 'No se encontró el archivo de copia de seguridad.',
        'invalid_backup_file' => 'Nombre de archivo de copia de seguridad no válido.',
    ],
];
