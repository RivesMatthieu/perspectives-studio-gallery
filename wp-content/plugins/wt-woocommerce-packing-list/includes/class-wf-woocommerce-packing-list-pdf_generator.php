<?php
class Wf_Woocommerce_Packing_List_Pdf_generator{
    public function __construct()
    {

    }
    public static function generate_pdf($html,$basedir,$name,$action='')
    {
        $path=plugin_dir_path(__FILE__).'vendor/dompdf/';
        include_once($path.'autoload.inc.php');
        
        // initiate dompdf class
        $dompdf = new Dompdf\Dompdf();

        $upload_loc=Wf_Woocommerce_Packing_List::get_temp_dir();
        $upload_dir=$upload_loc['path'];
        $upload_url=$upload_loc['url'];

        if(!is_dir($upload_dir))
        {
            @mkdir($upload_dir, 0700);
        }

        //document type specific subfolder
        $upload_dir=$upload_dir.'/'.$basedir;
        $upload_url=$upload_url.'/'.$basedir;
        if(!is_dir($upload_dir))
        {
            @mkdir($upload_dir, 0700);
        }

        //if directory successfully created
        if(is_dir($upload_dir))
        {
            $file_path=$upload_dir . '/'.$name.'.pdf';
            $file_url=$upload_url . '/'.$name.'.pdf';
            $dompdf->tempDir = $upload_dir;
            $dompdf->set_option('isHtml5ParserEnabled', true);
            $dompdf->set_option('enableCssFloat', true);
            $dompdf->set_option('isRemoteEnabled', true);
            
            $dompdf->set_option('defaultFont', 'dejavu sans');
            $dompdf->loadHtml($html);
            // (Optional) Setup the paper size and orientation
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->set_option('enable_font_subsetting', true);
            // Render the HTML as PDF
            $dompdf->render();

            if($action=='download' || $action=='preview')
            {  

                $dompdf_is_attachment=(($action=='preview' || isset($_GET['debug'])) ? false : true );
                $dompdf->stream($file_path, array("Attachment" =>$dompdf_is_attachment));              
                exit;
            }
            @file_put_contents($file_path, $dompdf->output());
            if($action=='preview_url')
            {
                return $file_url;
            }else
            {
                return $file_path;
            }
        }
    }   
}
