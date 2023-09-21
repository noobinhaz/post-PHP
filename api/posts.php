<?php

$url = $_SERVER['REQUEST_URI'];
// checking if slash is first character in route otherwise add it
if (strpos($url, "/") !== 0) {
    $url = "/$url";
}

$dbInstance = new DB();
$dbConn = $dbInstance->connect($db);

//List all posts
if ($url == '/posts' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $posts = getAllPosts($dbConn);
    echo json_encode($posts);
}

if ($url == '/posts' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = $_POST;
    $postId = addPost($input, $dbConn);
    if ($postId) {
        $input['id'] = $postId;
        $input['link'] = "/posts/$postId";
    }
    echo json_encode($input);
}

if (
    preg_match("/posts\/([0-9])+/", $url, $matches) && $_SERVER['REQUEST_METHOD']
    == 'GET'
) {
    $postId = $matches[1];
    $post = getPost($dbConn, $postId);
    echo json_encode($post);
}

if (
    preg_match("/posts\/([0-9])+/", $url, $matches) && $_SERVER['REQUEST_METHOD']
    == 'PATCH'
) {
    // $input = file_get_contents("php://input");

    // // Parse the JSON data into an associative array
    // $inputData = json_decode($input, true);

    // echo json_encode($inputData);

    $inputData = $_GET;

    $postId = $matches[1];
    updatePost($inputData, $dbConn, $postId);
    $post = getPost($dbConn, $postId);
    echo json_encode($post);
}

if (
    preg_match("/posts\/([0-9])+/", $url, $matches) && $_SERVER['REQUEST_METHOD']
    == 'DELETE'
) {
    $postId = $matches[1];
    deletePost($dbConn, $postId);
    echo json_encode([
        'id' => $postId,
        'deleted' => 'true'
    ]);
}


function getAllPosts($db)
{
    $statement = "SELECT * FROM posts";
    $result = $db->query($statement);

    if ($result && $result->num_rows > 0) {
        $posts = [];
        while ($result_row = $result->fetch_assoc()) {
            $post = [
                'id' => $result_row['id'],
                'title' => $result_row['title'],
                'status' => $result_row['status'],
                'content' => $result_row['content'],
                'user_id' => $result_row['user_id']
            ];
            $posts[] = $post;
        }
    }
    return $posts;
}

function addPost($input, $db)
{
    $title = $input['title'];
    $status = $input['status'];
    $content = $input['content'];
    $users_id = $input['user_id'];
    $statement = "INSERT INTO posts (title, status, content, user_id)
VALUES ('$title', '$status', '$content', $users_id)";

    $db->query($statement);
    return $db->insert_id;
}

function getPost($db, $id)
{
    $statement = "SELECT * FROM posts where id = " . $id;
    $result = $db->query($statement);
    $result_row = $result->fetch_assoc();
    return $result_row;
}

function updatePost($input, $db, $postId)
{
    $fields = getParams($input);
    $statement = "UPDATE posts SET $fields WHERE id = " . $postId;
    $db->query($statement);
    return $postId;
}

function getParams($input)
{
    $allowedFields = ['title', 'status', 'content', 'user_id'];
    $filterParams = [];
    foreach ($input as $param => $value) {
        if (in_array($param, $allowedFields)) {
            $filterParams[] = "$param='$value'";
        }
    }
    print_r($filterParams);
    return implode(", ", $filterParams);
}

function deletePost($db, $id)
{
    $statement = "DELETE FROM posts where id = " . $id;
    $db->query($statement);
}
