<?php


try {
    $soluce = new \PDO('mysql:host=localhost;dbname=soluces', 'root', 'toto');
    $soluce->query('INSERT INTO tokens (user_id, user_name, token, `datetime`)
                   VALUES(' . $user->Id . ',
                         ' . $soluce->quote($user->UserName) . ',
                         ' . $soluce->quote($token['oauth_token']) . ',
                         NOW())
                   ON DUPLICATE KEY UPDATE user_name = ' . $soluce->quote($user->UserName) . ',
                                           token = ' . $soluce->quote($token['oauth_token']) . ',
                                           invalid = 0,
                                           datetime = NOW();');
} catch (Exception $e) {
    die($e->getMessage());
}

//include dirname(dirname(dirname(__DIR__))) . '/tokens.php';