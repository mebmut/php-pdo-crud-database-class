<?php
    require('CrudClass.php');
    $crud = new CrudClass;
    $posts = $crud->findOne('posts',[
        'conditions' => [
        'title'=>'first updated post'
        ]
    ]);
    echo "<pre>";
    var_dump($posts);
    echo "</pre>";
?>

