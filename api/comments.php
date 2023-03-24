<?php

// ERROR REPORTING
//ini_set('display_errors', 1);
//error_reporting(E_ALL);

$url = $_SERVER['REQUEST_URI'];
if(strpos($url,"/") !== 0){
    $url = "/$url";
}
$urlArr = explode("/", $url);

$dbInstance = new DB();
$dbConn = $dbInstance->connect($db);


header("Content-Type:application/json");

if($url == '/blog/api/index.php/comments' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $comments = getAllComments($dbConn);
    echo json_encode($comments);
}

if($url == '/blog/api/index.php/comments' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = $_POST;
    $commentId = addComment($input, $dbConn);
    if($commentId){
        $input['id'] = $commentId;
        $input['link'] = "/comments/$commentId";
    }

    echo json_encode($input);

}

// RegEx alternatives - sample code regex didn't work
function old($url) { return preg_match("/blog/api/index.php/comments\/([0-9])+/", $url, $matches); }
function num_is_in_url($url) { return (is_numeric(explode('?', str_replace("/blog/api/index.php/comments/", '', $url))[0])); }
function get_num_from_url($url) { return explode('?', str_replace("/blog/api/index.php/comments/", '', $url))[0]; }


if(num_is_in_url($url) && $_SERVER['REQUEST_METHOD'] == 'PATCH'){
    $input = $_GET;
    $commentId = get_num_from_url($url);
    updateComment($input, $dbConn, $commentId);

    $comment = getComment($dbConn, $commentId);
    echo json_encode($comment);
}

if(num_is_in_url($url) && $_SERVER['REQUEST_METHOD'] == 'GET'){
    $commentId = get_num_from_url($url);
    $comment = getComment($dbConn, $commentId);

    echo json_encode($comment);
}

if(num_is_in_url($url) && $_SERVER['REQUEST_METHOD'] == 'DELETE'){
    $commentId = get_num_from_url($url);
    deleteComment($dbConn, $commentId);

    echo json_encode([
        'id'=> $commentId,
        'deleted'=> 'true'
    ]);
}

/**
 * Get Comment based on ID
 *
 * @param $db
 * @param $id
 *
 * @return Associative Array
 */
function getComment($db, $id) {
    $statement = $db->prepare("SELECT * FROM comments where id=:id");
    $statement->bindValue(':id', $id);
    $statement->execute();

    return $statement->fetch(PDO::FETCH_ASSOC);
}

/**
 * Delete Comment record based on ID
 *
 * @param $db
 * @param $id
 */
function deleteComment($db, $id) {
    $statement = $db->prepare("DELETE FROM comments where id=:id");
    $statement->bindValue(':id', $id);
    $statement->execute();
}

/**
 * Get all comments
 *
 * @param $db
 * @return mixed
 */
function getAllComments($db) {
    $statement = $db->prepare("SELECT * FROM comments");
    $statement->execute();
    $statement->setFetchMode(PDO::FETCH_ASSOC);

    return $statement->fetchAll();
}

/**
 * Add comment
 *
 * @param $input
 * @param $db
 * @return integer
 */
function addComment($input, $db){

    $sql = "INSERT INTO comments
          (comment, post_id, user_id) 
          VALUES 
          (:comment, :post_id, :user_id)";

    $statement = $db->prepare($sql);

    bindAllValues($statement, $input);

    $statement->execute();

    return $db->lastInsertId();
}

/**
 * @param $statement
 * @param $params
 * @return PDOStatement
 */
function bindAllValues($statement, $params){
    $allowedFields = ['commentId', 'comment', 'post_id', 'user_id'];

    foreach($params as $param => $value){
        if(in_array($param, $allowedFields)){
            $statement->bindValue(':'.$param, $value);
        }
    }

    return $statement;
}

/**
 * Get fields as parameters to set in record
 *
 * @param $input
 * @return string
 */
function getParams($input) {
    $allowedFields = ['comment', 'post_id', 'user_id'];

    $filterParams = [];
    foreach($input as $param => $value){
        if(in_array($param, $allowedFields)){
            $filterParams[] = "$param=:$param";
        }
    }

    return implode(", ", $filterParams);
}


/**
 * Update Comment
 *
 * @param $input
 * @param $db
 * @param $commentId
 * @return integer
 */
function updateComment($input, $db, $commentId){

    $fields = getParams($input);
    $input['commentId'] = $commentId;

    $sql = "
          UPDATE comments
          SET $fields 
          WHERE id=:commentId
           ";

    $statement = $db->prepare($sql);

    bindAllValues($statement, $input);

    $statement->execute();

    return $commentId;

}