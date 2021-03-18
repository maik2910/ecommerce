<?php
use \Hcode\Page;
Use \Hcode\Model\Product;
//use \Hcode\PageAdmin;

$app->get('/', function() {

	$products = Product::listAll();
    
    $page = new Page();

    $page->setTpl("index",[
    	'products'=>Product::checkList($products)
    ]);

});



?>