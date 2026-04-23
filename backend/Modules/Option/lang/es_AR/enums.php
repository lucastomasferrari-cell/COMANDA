<?php
return [
    "option_types" => [
        // Labels orientados al cajero/dueño, no al dev. El vendor usaba
        // traducción literal ("Casilla de verificación") que obligaba al
        // user a pensar qué significa. Ahora el prefijo indica la acción
        // ("Elegir varios"/"Elegir uno"/"Texto libre") y entre paréntesis
        // queda el término técnico como referencia cruzada.
        'text' => 'Texto libre',
        'textarea' => 'Texto libre (largo)',
        'select' => 'Elegir uno (dropdown)',
        'multiple_select' => 'Elegir varios (dropdown)',
        'checkbox' => 'Elegir varios (checkbox)',
        'radio' => 'Elegir uno (botones)',
        'date' => 'Fecha',
        'time' => 'Hora',
    ]
];
