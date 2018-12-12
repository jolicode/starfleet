<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Enum;

class TagEnum
{
    const TAGS = [
        'Front' => [
            'CSS',
            'HTML',
            'Javascript',
        ],
        'Back' => [
            'Ruby',
            'PHP',
            'Go',
            'Elixir',
            'NodeJS',
        ],
        'Multi' => [
            'C++',
            'Rust',
            'Scala',
            'Python',
            'Dotnet',
            'Java',
            'Dart',
        ],
        'Ops' => [
            'DevOps',
        ],
        'Mobile' => [
            'Android',
            'iOS',
            'React Native',
            'Flutter',
        ],
        'Query' => [
            'GraphQL',
        ],
        'Data' => [
            'Data',
        ],
        'Security' => [
            'Security',
        ],
        'Design' => [
            'UX',
        ],
        'General' => [
            'General',
            'TechComm',
        ],
        'Companies' => [
            'Google',
            'Apple',
            'Facebook',
            'Microsoft',
        ],
    ];
}
