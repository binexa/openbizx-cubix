<?php

use Openbiz\Easy\EasyForm;

class CustomHeaderForm extends EasyForm
{

    public function UpdateLogo()
    {
        $recArr = $this->readInputRecord();
        $imgfile = $recArr['custom_header'];
        $imgfile = OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . $imgfile;

        $logofile = OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'cubi_top_header.png';
        @copy($imgfile, $logofile);

        $this->processPostAction();
    }

    public function Restore()
    {
        $logofile = OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'cubi_top_header.png';
        @unlink($logofile);
        $this->processPostAction();
    }

}
