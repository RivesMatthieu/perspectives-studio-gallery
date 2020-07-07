<?php
class Wf_Woocommerce_Packing_List_Barcode_generator{
	public function __construct()
	{

	}
	public static function generate($invoice_number,$type='png',$width_factor=2)
	{
		$path=plugin_dir_path(__FILE__).'vendor/picqer/';
		include_once($path.'BarcodeGenerator.php');
		include_once($path.'BarcodeGeneratorPNG.php');
		include_once($path.'BarcodeGeneratorSVG.php');
		include_once($path.'BarcodeGeneratorJPG.php');
		include_once($path.'BarcodeGeneratorHTML.php');
		if($type=='jpg')
		{
			$generator = new BarcodeGeneratorJPG();
		}elseif($type=='html')
		{
			$generator = new BarcodeGeneratorHTML();
		}
		else
		{
			$generator = new BarcodeGeneratorPNG();
		}
		$barcode_data=$generator->getBarcode($invoice_number,$generator::TYPE_CODE_128,$width_factor);
		if($barcode_data)
		{
			if($type=='jpg' || $type=='png')
			{
				return 'data:image/'.$type.';base64,' . base64_encode($barcode_data);
			}else
			{
				return $barcode_data;
			}
		}
		return false;
	}
	
}