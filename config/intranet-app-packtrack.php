<?php

// config for Hwkdo/IntranetAppPacktrack
return [
'roles' => [
        'admin' => [
            'name' => 'App-Packtrack-Admin',
            'permissions' => [
                'see-app-packtrack',
                'manage-app-packtrack',
            ]
        ],
        'user' => [
            'name' => 'App-Packtrack-Benutzer',
            'permissions' => [
                'see-app-packtrack',                
            ]
        ],
]
];
