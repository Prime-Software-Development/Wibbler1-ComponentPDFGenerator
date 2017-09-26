<?php
namespace TrunkSoftware\Component\PDFGenerator;
/**
 * Created by PhpStorm.
 * User: trunk
 * Date: 26/09/17
 * Time: 16:05
 */

abstract class AbstractTransformer {
	public $type;

	public function __construct($type) {
		$this->type = $type;
	}

	public function transform($data) {

		return $data;
	}

	public function matchType($type) {
		return $this->type === $type;
	}
}