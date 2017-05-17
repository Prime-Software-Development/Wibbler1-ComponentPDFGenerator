<?php
namespace TrunkSoftware\Component\PDFGenerator;

use Trunk\PreferencesLibrary\Modules\Preferences;
use Trunk\Wibbler\WibblerDependencyContainer;
use Propel\Runtime\ActiveQuery\Criteria as Criteria;

class PDFGenerator extends \Trunk\Wibbler\Modules\base {

	/**
	 * @var Preferences
	 */
	private $preferences;

	/**
	 * @var \Trunk\Wibbler\Modules\twig
	 */
	private $twig;

	/**
	 * @var string
	 */
	private $namespace;

	/**
	 * @var string
	 */
	private $query_class;

	/**
	 * @var string
	 */
	private $base_template = "system/preferences/templates/pdf_template.twig";

	public function __construct( $additional_config = null ) {
		parent::__construct( $additional_config );

		$this->twig = $this->dependencies->getService( "twig" );
		$this->preferences = $this->dependencies->getService( "preferences" );

		$this->namespace = $additional_config['namespace'];
		$this->query_class = $this->namespace . "\\DocumentQuery";
		$this->base_template = $additional_config[ 'base_template' ];
	}

	/**
	 * 	$data = array(
	 *   	'header_title' => 'PDF Title',
	 *   	'content_template' => "path_to_template",
	 * 	    'content_html' => "<p>Hello World.</p>",
	 *   	'images' => $image_array,
	 *   	'hide_header' => false,
	 * 	    'hide_footer' => false,
	 *   	'header_template' => "path_to_template",
	 * 	    'footer_template' => "path_to_template"
	 * 	);

	 * @param [] $data
	 * @param string $dest
	 * @param string $base_template
	 * @param string $filename
	 * @param [] $document_ids
	 */
	public function generate( $data, $dest = 'I', $base_template = null, $filename = null, $document_ids = null ) {

		// Get the base template for pdfs
		if ( $base_template == null ) {
			$base_template = $this->base_template;
		}

		// Set the filename
		$filename = $filename ? $filename : 'pdf_file';

		// Get Image Documents for the Header
		if ( $document_ids == null ) {
			$document_ids = $this->preferences->get( 'PDF.IMAGE.IDS' );
		}

		$query_class = $this->query_class;

		if ( $document_ids !== null ) {
			$document_ids = $document_ids ? explode( ',', $document_ids ) : [ ];
			$images = $query_class::create()
				->filterById( $document_ids, Criteria::IN )
				->find();
		}
		else {
			$images = [];
		}

		$default_data = [
			'images' => $images,
			'default_css' => $this->preferences->get('PDF.DEFAULT.CSS')
		];
		// We merge the arrays so we could override the default values if necessary
		$data = array_merge( $default_data, $data );
		$html = $this->twig->render( $base_template, $data );

		$this->fromHtml($html, $filename, $dest);
	}

	/**
	 * @param string $html
	 * @param string $filename
	 * @param string $destination
	 */
	public function fromHtml($html, $filename = 'pdf_file', $destination = 'I')
	{
		$margin_left    = $this->preferences->get('PDF.MLEFT');#10;
		$margin_right   = $this->preferences->get('PDF.MRIGHT');#10;
		$margin_top     = $this->preferences->get('PDF.MTOP');#30;
		$margin_bottom  = $this->preferences->get('PDF.MBOTTOM');#15;
		$margin_header  = $this->preferences->get('PDF.MHEADER');#3;
		$margin_footer  = $this->preferences->get('PDF.MFOOTER');#3;

		$mpdf = new \mPDF(
			'',
			'A4',
			$font_size      = '',#font-size in points(pt)
			$font_family    = '', #font-family
			$margin_left    ? $margin_left      : 10,
			$margin_right   ? $margin_right     : 10,
			$margin_top     ? $margin_top       : 30,
			$margin_bottom  ? $margin_bottom    : 15,
			$margin_header  ? $margin_header    : 3,
			$margin_footer  ? $margin_footer    : 3
		);

		#$mpdf->debug = true;
		$mpdf->collapseBlockMargins = false;
		$mpdf->allow_output_buffering = true;

		try {
			$mpdf->WriteHTML( $html );
			$mpdf->Output( $filename, $destination );
		}
		catch( \Exception $ex ) {
			echo $ex->getMessage();
		}
	}
}