<?php error_reporting(E_ALL);

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/Database.php';
include_once '../../models/Url.php';
require './../../vendor/autoload.php';

use Elasticsearch\ClientBuilder;


//Create index with mappings

$client = ClientBuilder::create()->build();
// Source enabled/disabled
$params = [
    'index' => 'urls_url',
    'body' => [
        'settings' => [
            'number_of_shards' => 8,
            'number_of_replicas' => 0
        ],
        'mappings' => [
            '_url' => [
                '_source' => [
                    'enabled' => false
                ],
                'properties' => [
                    'id_url' => [
                        'type' => 'integer'
                    ],
                    'url_url' => [
                        'type' => 'keyword'
                    ],
                    'first_submitted_url' => [
                        'type' => 'date'
                    ],
                    'last_submitted_url' => [
                        'type' => 'date'
                    ],
                    'idusr_url' => [
                        'type' => 'integer'
                    ],
                    'count_url' => [
                        'type' => 'integer'
                    ],
                    'classification_url' => [
                        'type' => 'keyword'
                    ],
                    'whitelisted_url' => [
                        'type' => 'integer'
                    ],
                    'idmfm_url' => [
                        'type' => 'integer'
                    ],
                    'status_url' => [
                        'type' => 'keyword'
                    ],
                    'idcat_url' => [
                        'type' => 'integer'
                    ]
                ]
            ]
        ]
    ]
];
// Source enabled/disabled
$params1 = [
    'index' => 'botnet_bot',
    'body' => [
        'settings' => [
            'number_of_shards' => 8,
            'number_of_replicas' => 0
        ],
        'mappings' => [
            '_bot' => [
                '_source' => [
                    'enabled' => false
                ],
                'properties' => [
                    'id_bot' => [
                        'type' => 'integer'
                    ],
                    'xmlcontent_bot' => [
                        'type' => 'text'
                    ],
                    'id_usr' => [
                        'type' => 'integer'
                    ],
                    'idcmp_bot' => [
                        'type' => 'integer'
                    ],
                    'postdate_bot' => [
                        'type' => 'date'
                    ]
                ]
            ]
        ]
    ]
];

// Create the index with mappings and settings now
$response = $client->indices()->create($params);
$response1 = $client->indices()->create($params1);
var_dump($response, $response1);
exit('stop');



$offset = 0;
for ($j = 0; $j < 30; $j++) {
    $client = ClientBuilder::create()->build();
// Instantiate DB & connect
    $database = new Database();
    $db = $database->connect();

//Instantiate URL object
    $url = new Url($db, $offset);
    $result = $url->read();

// Check if any urls
        if ($result) {
            $rows = $result->fetchAll(PDO::FETCH_ASSOC);
            $params = ['body' => []];
            $i = 0;
            foreach ($rows as $key => $value) {
                $i++;
                $value['url_url'] = filter_var($value['url_url'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
                unset($value['md5_url']);

                $value['id_url'] = (int)$value['id_url'];
                $value['first_submitted_url'] = str_replace(' ', 'T', $value['first_submitted_url']);
                $value['last_submitted_url'] = str_replace(' ', 'T', $value['last_submitted_url']);
                $value['count_url'] = (int)$value['count_url'];
                $value['idcat_url'] = (int)$value['idcat_url'];
                $value['idmfm_url'] = (int)$value['idmfm_url'];
                $value['idusr_url'] = (int)$value['idusr_url'];
                $value['whitelisted_url'] = (int)$value['whitelisted_url'];

                $params['body'][] = [
                    'index' => [
                        '_index' => 'urls_url',
                        '_type' => '_url',
                        '_id' => (int)$value['id_url']
                    ]
                ];

                $params['body'][] = $value;

                // Every 1000 documents stop and send the bulk request
                if ($i % 10000 == 0) {
                    $responses = $client->bulk($params);
                    print_r($responses);
                    print_r("\n");
                    // erase the old bulk request
                    $params = ['body' => []];

                    // unset the bulk response when you are done to save memory
                    unset($responses);
                }
            }
        }
    $offset += 5000000;
}
