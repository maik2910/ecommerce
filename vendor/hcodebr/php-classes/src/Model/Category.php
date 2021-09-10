<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;


Class Category extends Model {

	
	public static function listAll()
	{

		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
	}

	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_categories_save(:idcategory,:descategory)", array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
		));
		
	$this->setData($results[0]);

	Category::updateFile();

	}

	public function get($idcategory)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_categories where idcategory=:idcategory",[':idcategory'=>$idcategory
			
		]);
	
		$this->setData($results[0]);

	}

	public function delete()
	{

		$sql = new Sql();

		$sql->query("DELETE FROM tb_categories where idcategory=:idcategory",[':idcategory'=>$this->getidcategory()
			
		]);

		Category::updateFile();
	}

	public static function updateFile()
	{

		$categories = Category::listAll();

		$html = [];

		foreach ($categories as $row) {
			array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
		}

		file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories_menu.html", implode('', $html));

	}

	public function getProducts($related = true)
	{

		$sql = new Sql();

		if ($related === true) {

			return $sql->select("
				SELECT * FROM tb_products WHERE idproduct IN(
					SELECT a.idproduct
					FROM tb_products a
					INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
					WHERE b.idcategory = :idcategory
				);
			", [
				':idcategory'=>$this->getidcategory()
			]);

		} else {

			return $sql->select("
				SELECT * FROM tb_products WHERE idproduct NOT IN(
					SELECT a.idproduct
					FROM tb_products a
					INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
					WHERE b.idcategory = :idcategory
				);
			", [
				':idcategory'=>$this->getidcategory()
			]);

		}

	}

	public function getProductsPage($page = 1, $itensPerPage = 3)
	{
		$start = ($page-1) * $itensPerPage;

		$sql = new Sql();
	
		$results = $sql -> select("
		SELECT /*FUNCAO PARA CONTAR OS PRODUTOS*/ SQL_CALC_FOUND_ROWS * 
		FROM tb_products a
		inner join tb_productscategories b on a.idproduct = b.idproduct
		inner join tb_categories c on c.idcategory = b.idcategory
		where c.idcategory = :idcategory
		LIMIT $start,$itensPerPage;
		",[
			':idcategory'=>$this->getidcategory()
		]);
		
		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrTotal;");
	
			return [
				'data'=>Product::checkList($results),
				'total'=>(int)$resultTotal[0]["nrTotal"],
				'page'=>ceil($resultTotal[0]["nrTotal"] / $itensPerPage )
			];
	}

	public function addProduct(Product $product)
	{
		$sql = new Sql();

		$sql->query("INSERT INTO tb_productscategories (idcategory,idproduct) VALUES (:idcategory,:idproduct)",[
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()
		]);
	}
	public function removeProduct(Product $product)
	{
		$sql = new Sql();

		$sql->query("DELETE FROM tb_productscategories where idcategory = :idcategory and idproduct =:idproduct",[
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()
		]);
	}

	public static function getPage($page = 1, $itemsPerPage = 10)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_categories a 
			ORDER BY descategory
			LIMIT $start, $itemsPerPage;
		");

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];

	}

	public static function getPageSearch($search, $page = 1, $itemsPerPage = 10)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_categories a 
			WHERE a.descategory LIKE :search
			ORDER BY a.descategory
			LIMIT $start, $itemsPerPage;
		", [
			':search'=>'%'.$search.'%'
		]);

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];

	} 
}