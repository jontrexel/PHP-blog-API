<?php

//echo "Posts will display here";

$url = $_SERVER['REQUEST_URI'];
//echo $url;

// checking if slash is first character in route otherwise add it
if(strpos($url,"/") !== 0){
    $url = "/$url";
}

$dbInstance = new DB();
$dbConn = $dbInstance->connect($db);

// GET ALL POSTS ENDPOINT
//	- URI: /blog/api/index.php/posts
//	- Method: GET

if($url == '/blog/api/index.php/posts' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $posts = getAllPosts($dbConn);
	//$posts = getAllPostsTest();
    echo json_encode($posts);
}

function getAllPosts($db) {
 $statement = $db->prepare("SELECT * FROM posts");
 $statement->execute();
 $result = $statement->setFetchMode(PDO::FETCH_ASSOC);
 return $statement->fetchAll();
}


function getAllPostsTest() {
    return [
        [
            'id' => 1,
            'title' => 'First Post',
            'content' => 'It is all about PHP'
        ],
        [
            'id' => 2,
            'title' => 'Second Post',
            'content' => 'RESTful web services'
        ],
    ];
}


// GET SINGLE POST ENDPOINT:
//	- URI: /blog/api/index.php/posts/{id}
//	- Method: GET
//			uses str_replace($search, $replace, $subject)
if((is_numeric(str_replace("/blog/api/index.php/posts/", '', $url)))
	&& $_SERVER['REQUEST_METHOD'] == 'GET'){
    $postId = str_replace("/blog/api/index.php/posts/", '', $url);
    $post = getPost($dbConn, $postId);

    echo json_encode($post);
}


function getPost($db, $id) {
    $statement = $db->prepare("SELECT * FROM posts where id=:id");
    $statement->bindValue(':id', $id);
    $statement->execute();

    return $statement->fetch(PDO::FETCH_ASSOC);
}

// SUBMIT NEW POST ENDPOINT
//	- URI: /blog/api/index.php/posts
//	- Method: POST
//	- (Body) Parameters: title, status, content, user_id

if($url == '/blog/api/index.php/posts' && $_SERVER['REQUEST_METHOD'] == 'POST') {
 $input = $_POST;
 
 
 $postId = addPost($input, $dbConn);
 if($postId){
     $input['id'] = $postId;
     $input['link'] = "/posts/$postId";
 }
 

 echo json_encode($input);
}

function addPost($input, $db){
 $sql = "INSERT INTO posts 
 (title, status, content, user_id) 
 VALUES 
 (:title, :status, :content, :user_id)";

 $statement = $db->prepare($sql);

 bindAllValues($statement, $input);

 $statement->execute();

 return $db->lastInsertId();
}

function bindAllValues($statement, $params){
    $allowedFields = ['title', 'status', 'content', 'user_id'];

    foreach($params as $param => $value){
        if(in_array($param, $allowedFields)){
            $statement->bindValue(':'.$param, $value);
        }
    }

    return $statement;
}


// UPDATE POST ENDPOINT
//	- URI: /blog/api/index.php/posts/{id}
//	- Method: PATCH
//	- (Query String) Parameters: title, status, content, user_id
//Code to update post, if /posts/{id} and method is PATCH

if(preg_match("/posts\/([0-9])+/", $url, $matches) && $_SERVER['REQUEST_METHOD'] == 'PATCH'){
    $input = $_GET;
    $postId = $matches[1];
    updatePost($input, $dbConn, $postId);

    $post = getPost($dbConn, $postId);
    echo json_encode($post);
}

/**
 * Get fields as parameters to set in record
 *
 * @param $input
 * @return string
 */
function getParams($input) {
    $allowedFields = ['title', 'status', 'content', 'user_id'];

    $filterParams = [];
    foreach($input as $param => $value){
        if(in_array($param, $allowedFields)){
            $filterParams[] = "$param=:$param";
        }
    }

    return implode(", ", $filterParams);
}


/**
 * Update Post
 *
 * @param $input
 * @param $db
 * @param $postId
 * @return integer
 */
function updatePost($input, $db, $postId){

    $fields = getParams($input);

    $sql = "
          UPDATE posts 
          SET $fields 
          WHERE id=':postId'
           ";

    $statement = $db->prepare($sql);
    $statement->bindValue(':id', $id);
    bindAllValues($statement, $input);

    $statement->execute();

    return $postId;
}

?>