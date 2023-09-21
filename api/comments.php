<?php

$url = $_SERVER['REQUEST_URI'];
// checking if slash is first character in route otherwise add it
if (strpos($url, "/") !== 0) {
    $url = "/$url";
}

$dbInstance = new DB();
$dbConn = $dbInstance->connect($db);

//List all comments
if ($url == '/comments' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $comments = getAllcomments($dbConn);
    echo json_encode([
        'isSuccess' => true,
        'message'   => !empty($comments) ? '' : 'No Comments Available',
        'data'  => $comments
    ]);
}

if ($url == '/comments' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = $_POST;
    if ($input === null) {
        http_response_code(400);
        echo json_encode([
            'isSuccess' => false,
            'message'   => 'Invalid JSON data',
            'data' => []
        ]);
        return;
    }

    $commentId = addComment($input, $dbConn);

    if ($commentId !== null) {
        $input['id'] = $commentId;
        $input['link'] = "/comments/$commentId";
        http_response_code(201);
    } else {
        http_response_code(500);
    }

    echo json_encode([
        'isSuccess' => $commentId !== null,
        'message'   => $commentId !== null ? '' : 'Could not add comment',
        'data'      => $input
    ]);
}

if (
    preg_match("/comments\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD']
    == 'GET'
) {
    $commentId = $matches[1];
    $comment = getcomment($dbConn, $commentId);
    echo json_encode([
        'isSuccess' => !empty($comment) ? true : false,
        'message'   => !empty($comment) ? '' : 'Could not add comment',
        'data'      => $comment
    ]);
}

if (
    preg_match("/comments\/(\d+)/", $url, $matches) && $_SERVER['REQUEST_METHOD']
    == 'PATCH'
) {
    $input = json_decode(file_get_contents("php://input"), true);

    if ($input === null) {
        http_response_code(400); // Bad Request
        echo json_encode([
            'isSuccess' => false,
            'message'   => 'Invalid JSON data',
            'data' => []
        ]);
        return;
    }

    $commentId = $matches[1];

    $update = updatecomment($input, $dbConn, $commentId);
    $comment = getcomment($dbConn, $commentId);

    if ($comment == null) {
        http_response_code(404); // Bad Request
        echo json_encode([
            'isSuccess' => false,
            'message'   => 'No Comment Found',
            'data' => []
        ]);
        return;
    }

    echo json_encode([
        'isSuccess' => $update ? true : false,
        'message'   => $update ? '' : 'Could not update',
        'data'      => $comment
    ]);
}

if (
    preg_match("/comments\/([0-9])+/", $url, $matches) && $_SERVER['REQUEST_METHOD']
    == 'DELETE'
) {
    $commentId = $matches[1];
    $deleteStatus = deletecomment($dbConn, $commentId);
    echo json_encode([
        'isSuccess' => $deleteStatus,
        'message'   => 'Deleted ' . $deleteStatus ? 'Success' : 'Failed',
        'data'      => [

            'id' => $commentId,
        ]
    ]);
}


function getAllcomments($db)
{
    $statement = "SELECT * FROM comments";
    $result = $db->query($statement);

    if ($result && $result->num_rows > 0) {
        $comments = [];
        while ($result_row = $result->fetch_assoc()) {
            $comment = [
                'id' => $result_row['id'],
                'comment' => $result_row['comment'],
                'post_id' => $result_row['post_id'],
                'user_id' => $result_row['user_id']
            ];
            $comments[] = $comment;
        }
    }
    return $comments;
}

function addcomment($input, $db)
{
    $comment = $input['comment'];
    $post_id = $input['post_id'];
    $users_id = $input['user_id'];
    $statement = "INSERT INTO comments (comment, post_id, user_id)
VALUES ('$comment', '$post_id',  $users_id)";

    $create = $db->query($statement);
    if ($create) {

        return $db->insert_id;
    }
    return null;
}

function getcomment($db, $id)
{
    $statement = "SELECT * FROM comments where id = " . $id;
    $result = $db->query($statement);
    $result_row = $result->fetch_assoc();
    return $result_row;
}

function updatecomment($input, $db, $commentId)
{
    $fields = getParams($input);
    $statement = "UPDATE comments SET $fields WHERE id = " . $commentId;
    $update = $db->query($statement);
    return $update;
}

function getParams($input)
{
    $allowedFields = ['comment', 'post_id', 'user_id'];
    $filterParams = [];
    foreach ($input as $param => $value) {
        if (in_array($param, $allowedFields)) {
            $filterParams[] = "$param='$value'";
        }
    }
    return implode(", ", $filterParams);
}

function deletecomment($db, $id)
{
    $statement = "DELETE FROM comments where id = " . $id;
    return $db->query($statement);
}
