<?php

return [
    'database' => 'Database',
    'tabs' => [
        'backup' => 'Backup',
        'restore' => 'Restore',
    ],
    'backup_note_title' => 'Backup Note',
    'backup_note_details' => 'Creating a backup exports the current database into an SQL file in storage. Keep files in a secure location and generate backups before major updates.',
    'restore_warning_title' => 'Restore Warning',
    'restore_warning_details' => 'Restoring a database will overwrite current data. Always take a fresh backup before restore and make sure users are informed.',
    'backup_file' => 'Backup File',
    'latest_backups' => 'Latest Backups',
    'no_backups' => 'No backups found.',
    'columns' => [
        'name' => 'Name',
        'size' => 'Size',
        'created_at' => 'Created At',
    ],
    'confirmations' => [
        'backup_title' => 'Create Backup',
        'backup_message' => 'Create a new database backup now?',
        'restore_title' => 'Restore Database',
        'restore_upload_message' => 'Restore the database from the selected backup file? Current data will be overwritten.',
        'restore_saved_message' => 'Restore the database from this saved backup? Current data will be overwritten.',
    ],

    'messages' => [
        'backup_created' => 'Database backup created successfully.',
        'restore_completed' => 'Database restored successfully.',
        'download_started' => 'Backup download started successfully.',
        'backup_not_found' => 'Backup file was not found.',
        'invalid_backup_file' => 'Invalid backup file name.',
    ],
];
