<?php

namespace Biko\Models;

/**
 * @Source("biko_products")
 */
class Products extends ModelBase
{

	/**
	 * @Primary
	 * @Column(type="integer", nullable=false, column="pro_id")
	 * @Identity
	 */
	public $id;

	/**
	 * @Column(type="integer", nullable=false, column="pro_cat_id")
	 */
	public $categoriesId;

	/**
	 * @Column(type="string", nullable=false, column="pro_name")
	 */
	public $name;

	/**
	 * @Column(type="string", nullable=false, column="pro_description")
	 */
	public $description;

	/**
	 * @Column(type="decimal", nullable=false, column="pro_price")
	 */
	public $price;

}