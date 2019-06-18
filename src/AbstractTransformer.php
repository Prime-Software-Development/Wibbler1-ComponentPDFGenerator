<?php
namespace Trunk\Component\PDFGenerator;

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
